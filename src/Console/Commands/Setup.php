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
            
            // Remove duplicate use statements
            $content = $this->removeDuplicateUses($content);
            
            // Add required use statements
            $content = $this->addRequiredUses($content);
            
            // Update class definition
            $content = $this->updateClassDefinition($content);
            
            // Add traits if needed
            $content = $this->addTraits($content);
            
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

    protected function removeDuplicateUses(string $content): string
    {
        preg_match_all('/^use .+;$/m', $content, $matches);
        if (!empty($matches[0])) {
            $uniqueUses = array_unique($matches[0]);
            $content = preg_replace('/^use .+;$/m', '', $content);
            $content = preg_replace('/^(namespace.+?;)/', "$1\n\n" . implode("\n", $uniqueUses), $content);
        }
        return $content;
    }

    protected function addRequiredUses(string $content): string
    {
        $requiredUses = [
            'use Illuminate\Foundation\Auth\User as Authenticatable;',
            'use Tymon\JWTAuth\Contracts\JWTSubject;',
            'use Shahnewaz\PermissibleNg\Traits\Permissible;',
            'use Shahnewaz\PermissibleNg\Traits\JWTAuthentication;'
        ];

        foreach ($requiredUses as $use) {
            if (!str_contains($content, $use)) {
                preg_match('/^(namespace.+?;.*?)(?=class|$)/ms', $content, $matches);
                if (!empty($matches[1])) {
                    $content = str_replace($matches[1], $matches[1] . $use . "\n", $content);
                }
            }
        }
        return $content;
    }

    protected function updateClassDefinition(string $content): string
    {
        // Replace extends Permissible with extends Authenticatable
        $content = preg_replace(
            '/extends Permissible/',
            'extends Authenticatable',
            $content
        );

        // Update or add implements clause
        if (preg_match('/implements\s+([^{]+)/', $content, $matches)) {
            $implements = array_map('trim', explode(',', $matches[1]));
            if (!in_array('JWTSubject', $implements)) {
                $implements[] = 'JWTSubject';
            }
            $content = preg_replace(
                '/implements\s+[^{]+/',
                'implements ' . implode(', ', array_unique($implements)),
                $content
            );
        } else {
            $content = preg_replace(
                '/(extends\s+Authenticatable)/',
                '$1 implements JWTSubject, Auditable',
                $content
            );
        }

        return $content;
    }

    protected function addTraits(string $content): string
    {
        if (!str_contains($content, 'use Permissible;') && !str_contains($content, 'use Permissible,')) {
            $content = preg_replace(
                '/(class\s+User\s+[^{]+{)/',
                "$1\n    use Permissible, JWTAuthentication;",
                $content
            );
        }
        return $content;
    }
}
