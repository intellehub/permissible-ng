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
            
            // Parse the file into sections
            preg_match('/^(namespace[^;]+;)\s*(.*?)(\s*class\s+User\s+.*?)(\s*{[\s\S]*$)/m', $content, $matches);
            
            if (count($matches) !== 5) {
                throw new \Exception('Unable to parse User model structure');
            }
            
            $namespace = $matches[1];
            $useStatements = $matches[2];
            $classDefinition = $matches[3];
            $classBody = $matches[4];
            
            // Process each section
            $useStatements = $this->processUseStatements($useStatements);
            $classDefinition = $this->processClassDefinition($classDefinition);
            $classBody = $this->processClassBody($classBody);
            
            // Rebuild the file with proper formatting
            $content = "<?php\n\n" .
                      $namespace . "\n\n" .
                      $useStatements . "\n" .
                      $classDefinition . "\n" .
                      $classBody;
            
            $this->files->put($modelPath, $content);
            $this->info('User model updated successfully.');

            // Ask about backup file
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

    protected function processUseStatements(string $useStatements): string
    {
        // Get all existing use statements
        preg_match_all('/^use [^;]+;/m', $useStatements, $matches);
        $existing = $matches[0] ?? [];
        
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
        // Replace extends Permissible with extends Authenticatable
        $classDefinition = preg_replace('/extends Permissible/', 'extends Authenticatable', $classDefinition);
        
        // Update implements clause
        if (preg_match('/implements\s+([^{]+)/', $classDefinition, $matches)) {
            $implements = array_map('trim', explode(',', $matches[1]));
            if (!in_array('JWTSubject', $implements)) {
                $implements[] = 'JWTSubject';
            }
            $classDefinition = preg_replace(
                '/implements\s+[^{]+/',
                'implements ' . implode(', ', array_unique($implements)),
                $classDefinition
            );
        } else {
            $classDefinition = trim($classDefinition) . ' implements JWTSubject, Auditable';
        }
        
        return $classDefinition;
    }

    protected function processClassBody(string $classBody): string
    {
        // Find the position after the opening brace
        $pos = strpos($classBody, '{') + 1;
        
        // Add our traits if they don't exist
        if (!str_contains($classBody, 'use Permissible;') && !str_contains($classBody, 'use Permissible,')) {
            $traitDefinition = "\n    use Permissible, JWTAuthentication;\n";
            $classBody = substr_replace($classBody, $traitDefinition, $pos, 0);
        }
        
        return $classBody;
    }
}
