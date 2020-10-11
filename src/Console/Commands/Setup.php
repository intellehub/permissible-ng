<?php

namespace Shahnewaz\Permissible\Console\Commands;

use Artisan;
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
    public function handle()
    {
        $this->modifyUserModel();
        $this->call('migrate');
        $this->call('permissible:seed');
        $this->call('jwt:secret', [
            '--no-interaction' => true,
        ]);
        $this->info('Permissible setup complete.');
    }


    /**
     * Write permissible trait into the Authenticable Model
     * */
    public function modifyUserModel () {
        $files = new Filesystem;

        $model = 'User';
        $modelFilePath = app_path($model.'.php');

        $modelFile = file($modelFilePath);

        // Is model already using the trait?
        $modelUsingPermissible = count(preg_grep("~Permissible~", $modelFile));
        
        if ($modelUsingPermissible === 0) {

            // Use statement
            $firstUseStatement = min(array_keys(preg_grep("~use~", $modelFile)));
            $currentUseStatement = $modelFile[$firstUseStatement];
            $modelFile[$firstUseStatement] = $currentUseStatement.PHP_EOL.'use Shahnewaz\Permissible\Permissible;'.PHP_EOL;

            // Class definition
            $classStatement = min(array_keys(preg_grep("~class~", $modelFile)));
            $modelFile[$classStatement] = 'class User extends Permissible'.PHP_EOL;

            $files->put($modelFilePath, $modelFile);
            $this->info('Permissible integrated into system.');
        } else {
            $this->info('Model file already using Permissible! Skipping...');
        }

    }

}
