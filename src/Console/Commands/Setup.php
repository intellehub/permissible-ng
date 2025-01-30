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
    protected $description = 'Setup permissible with seeds and modify User model.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $this->call('migrate');
        $this->call('permissible:seed');
        $this->call('jwt:secret', [
            '--no-interaction' => true,
        ]);
        
        if ($this->confirm('Do you want to modify the User model to use traits instead of inheritance?', true)) {
            $this->modifyUserModel();
        }
        
        $this->info('PermissibleAuth setup complete.');
    }

    protected function modifyUserModel(): void
    {
        $modelPath = app_path('Models/User.php');
        
        if (!$this->files->exists($modelPath)) {
            $this->error('User model not found at: ' . $modelPath);
            return;
        }

        // Backup original file
        $this->files->copy($modelPath, $modelPath . '.backup');

        try {
            $content = $this->files->get($modelPath);
            
            // Only make necessary changes
            $content = $this->updateModelContent($content);
            
            $this->files->put($modelPath, $content);
            $this->info('User model updated successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to update User model: ' . $e->getMessage());
            // Restore backup
            if ($this->files->exists($modelPath . '.backup')) {
                $this->files->move($modelPath . '.backup', $modelPath);
            }
        }
    }

    protected function updateModelContent(string $content): string
    {
        // Add required use statements if they don't exist
        $requiredUses = [
            'use Illuminate\Foundation\Auth\User as Authenticatable;',
            'use Tymon\JWTAuth\Contracts\JWTSubject;',
            'use Shahnewaz\PermissibleNg\Traits\Permissible;',
            'use Shahnewaz\PermissibleNg\Traits\JWTAuthentication;'
        ];

        foreach ($requiredUses as $use) {
            if (!str_contains($content, $use)) {
                // Find the last use statement or namespace
                preg_match_all('/^use .+;$/m', $content, $matches, PREG_OFFSET_CAPTURE);
                if (!empty($matches[0])) {
                    $lastUse = end($matches[0]);
                    $position = $lastUse[1] + strlen($lastUse[0]);
                    $content = substr_replace($content, "\n" . $use, $position, 0);
                } else {
                    // Add after namespace
                    $content = preg_replace(
                        '/(namespace .+;)/',
                        "$1\n\n" . $use,
                        $content
                    );
                }
            }
        }

        // Change extends Permissible to extends Authenticatable implements JWTSubject
        $content = preg_replace(
            '/extends Permissible/',
            'extends Authenticatable implements JWTSubject',
            $content
        );

        // Add traits if they don't exist
        if (!str_contains($content, 'use Permissible;') && !str_contains($content, 'use Permissible,')) {
            $content = preg_replace(
                '/(class User.+{)/',
                "$1\n    use Permissible, JWTAuthentication;",
                $content
            );
        }

        return $content;
    }
}
