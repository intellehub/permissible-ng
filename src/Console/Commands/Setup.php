<?php

namespace Shahnewaz\PermissibleNg\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissible:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup permissible with seeds.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $this->modifyUserModel();
        $this->call('migrate');
        $this->call('permissible:seed');
        $this->call('jwt:secret', [
            '--no-interaction' => true,
        ]);
        $this->info('PermissibleAuth setup complete.');
    }

    public function modifyUserModel()
    {
        $files = new Filesystem;
        $model = 'User';
        $modelFilePath = app_path('Models/' . $model . '.php');

        // Check if the file exists
        if (!$files->exists($modelFilePath)) {
            $this->error('Model file not found: ' . $modelFilePath);
            return;
        }

        // Read the file contents
        try {
            $modelFile = file($modelFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        } catch (\Exception $e) {
            $this->error('Failed to read the model file: ' . $e->getMessage());
            return;
        }

        // Is model already using the trait?
        $modelUsingPermissible = count(preg_grep("~PermissibleAuth~", $modelFile));

        if ($modelUsingPermissible > 0) {
            $this->info('Model file already using PermissibleAuth! Skipping...');
            return;
        }

        // Backup the original file
        $backupFilePath = $modelFilePath . '.backup';
        try {
            $files->copy($modelFilePath, $backupFilePath);
        } catch (\Exception $e) {
            $this->error('Failed to create a backup of the model file: ' . $e->getMessage());
            return;
        }

        try {
            // Add the `use` statement
            $firstUseStatement = min(array_keys(preg_grep("~^use ~", $modelFile)));
            $currentUseStatement = $modelFile[$firstUseStatement];
            $modelFile[$firstUseStatement] = $currentUseStatement . PHP_EOL . 'use Shahnewaz\PermissibleNg\Permissible;';

            // Modify the class definition
            $classStatement = min(array_keys(preg_grep("~^class ~", $modelFile)));
            $modelFile[$classStatement] = 'class User extends PermissibleAuth';

            // Write the modified file
            $files->put($modelFilePath, implode(PHP_EOL, $modelFile));

            $this->info('PermissibleAuth integrated into the system.');
        } catch (\Exception $e) {
            // Restore the backup in case of any failure
            $files->copy($backupFilePath, $modelFilePath);
            $this->error('Operation failed: ' . $e->getMessage());
            return;
        } finally {
            // Clean up the backup file
            if ($files->exists($backupFilePath)) {
                $files->delete($backupFilePath);
            }
        }
    }
}
