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
        
        $this->modifyUserModel();
        
        $this->info('PermissibleAuth setup complete.');
    }

    protected function modifyUserModel(): void
    {
        $modelPath = app_path('Models/User.php');
        
        if (!$this->files->exists($modelPath)) {
            $this->error('User model not found at: ' . $modelPath);
            return;
        }

        $content = $this->files->get($modelPath);

        // Backup original file
        $this->files->copy($modelPath, $modelPath . '.backup');

        try {
            $updatedContent = $this->updateModelContent($content);
            $this->files->put($modelPath, $updatedContent);
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
        // Parse the existing content
        if (!preg_match('/namespace\s+(.+?);/', $content, $namespaceMatch)) {
            throw new \Exception('Could not find namespace declaration');
        }

        // Required use statements
        $requiredUses = [
            'use Illuminate\Foundation\Auth\User as Authenticatable;',
            'use Tymon\JWTAuth\Contracts\JWTSubject;',
            'use Shahnewaz\PermissibleNg\Traits\Permissible;',
            'use Shahnewaz\PermissibleNg\Traits\JWTAuthentication;'
        ];

        // Get existing use statements
        preg_match_all('/use\s+(.+?);/', $content, $useMatches);
        $existingUses = $useMatches[0] ?? [];
        
        // Merge existing and required use statements, removing duplicates
        $allUses = array_unique(array_merge($existingUses, $requiredUses));
        sort($allUses);

        // Build use statements block
        $useBlock = implode("\n", $allUses);

        // Update class definition
        $classPattern = '/(class\s+User\s+extends\s+)[^\s{]+/';
        $content = preg_replace($classPattern, '$1Authenticatable implements JWTSubject', $content);

        // Update traits
        $traitsPattern = '/use\s+([^;{]+)(?:\s*{|\s*;)/';
        if (preg_match($traitsPattern, $content, $traitMatch)) {
            $existingTraits = array_map('trim', explode(',', $traitMatch[1]));
            $requiredTraits = ['Notifiable', 'Permissible', 'JWTAuthentication'];
            $allTraits = array_unique(array_merge($existingTraits, $requiredTraits));
            $traitBlock = 'use ' . implode(', ', $allTraits) . ';';
            $content = preg_replace($traitsPattern, $traitBlock, $content);
        } else {
            // If no traits found, add them after class definition
            $content = preg_replace(
                '/(class\s+User\s+extends\s+Authenticatable\s+implements\s+JWTSubject\s*{)/',
                '$1' . PHP_EOL . '    use Notifiable, Permissible, JWTAuthentication;' . PHP_EOL,
                $content
            );
        }

        // Rebuild the file content
        $parts = explode(';', $content, 2);
        return $parts[0] . ";\n\n" . $useBlock . "\n\n" . $parts[1];
    }
}
