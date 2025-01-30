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
        
        if ($this->confirm('Do you want to modify the User model to use traits automatically?', true)) {
            $this->modifyUserModel();
        }
        
        $this->info('PermissibleAuth setup complete.');
    }

    protected function modifyUserModel(): void
    {
        $modelPath = app_path('Models/User.php');
        $backupPath = $modelPath . '.backup';
        
        if (!$this->files->exists($modelPath)) {
            $this->error('User model not found at: ' . $modelPath);
            return;
        }

        // Backup original file
        $this->files->copy($modelPath, $backupPath);

        try {
            $content = $this->files->get($modelPath);
            
            // Split content into major sections using more reliable markers
            $parts = $this->parseFileContent($content);
            
            // Process each section
            $useStatements = $this->processUseStatements($parts['uses']);
            $classDefinition = $this->processClassDefinition($parts['class_definition']);
            $classBody = $this->processClassBody($parts['class_body']);
            
            // Rebuild the file with proper formatting
            $content = "<?php\n\n" .
                      $parts['namespace'] . "\n\n" .
                      $useStatements . "\n\n" .
                      $classDefinition . "\n" .
                      $classBody;
            
            $this->files->put($modelPath, $content);
            $this->info('User model updated successfully.');

            if ($this->confirm('Do you want to delete the backup file?', true)) {
                $this->files->delete($backupPath);
                $this->info('Backup file deleted.');
            } else {
                $this->info('Backup file kept at: ' . $backupPath);
            }

        } catch (\Exception $e) {
            $this->error('Failed to update User model: ' . $e->getMessage());
            if ($this->files->exists($backupPath)) {
                $this->files->move($backupPath, $modelPath);
                $this->info('Original file restored from backup.');
            }
        }
    }

    protected function parseFileContent(string $content): array
    {
        // Get namespace
        preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch);
        $namespace = $namespaceMatch[0] ?? '';

        // Get use statements - only those between namespace and class
        $classPos = strpos($content, 'class User');
        $beforeClass = substr($content, 0, $classPos);
        preg_match_all('/^\s*use\s+[^;]+;$/m', $beforeClass, $useMatches);
        $uses = $useMatches[0] ?? [];

        // Get class definition
        preg_match('/class\s+User\s+extends\s+Permissible\s+implements\s+Auditable/', $content, $classMatch);
        $classDefinition = $classMatch[0] ?? '';

        // Get class body (everything between the first { and the last })
        $startBrace = strpos($content, '{');
        $endBrace = strrpos($content, '}');
        $classBody = substr($content, $startBrace, $endBrace - $startBrace + 1);

        if (empty($namespace) || empty($classDefinition) || empty($classBody)) {
            throw new \Exception('Failed to parse one or more required sections of the User model');
        }

        return [
            'namespace' => $namespace,
            'uses' => implode("\n", array_filter($uses)), // Filter out empty lines
            'class_definition' => $classDefinition,
            'class_body' => $classBody
        ];
    }

    protected function processUseStatements(string $useStatements): string
    {
        // Get all existing use statements
        preg_match_all('/^\s*use\s+[^;]+;/m', $useStatements, $matches);
        $existing = $matches[0] ?? [];
        
        // Remove the old Permissible use statement
        $existing = array_filter($existing, function($use) {
            return !str_contains($use, 'Shahnewaz\PermissibleNg\Permissible;');
        });
        
        // Add required use statements
        $required = [
            'use Illuminate\Foundation\Auth\User as Authenticatable;',
            'use Tymon\JWTAuth\Contracts\JWTSubject;',
            'use Shahnewaz\PermissibleNg\Traits\Permissible;',
            'use Shahnewaz\PermissibleNg\Traits\JWTAuthentication;'
        ];
        
        // Merge and remove duplicates
        $allUses = array_unique(array_merge($existing, $required));
        sort($allUses);
        
        return implode("\n", $allUses);
    }

    protected function processClassDefinition(string $classDefinition): string
    {
        return 'class User extends Authenticatable implements JWTSubject, Auditable';
    }

    protected function processClassBody(string $classBody): string
    {
        // Find the position after the opening brace
        $pos = strpos($classBody, '{') + 1;
        
        // Add our traits if they don't exist
        if (!str_contains($classBody, 'use Permissible;') && !str_contains($classBody, 'use Permissible,')) {
            $traitDefinition = "\n    use Permissible, JWTAuthentication;";
            $classBody = substr_replace($classBody, $traitDefinition, $pos, 0);
        }
        
        return $classBody;
    }
}
