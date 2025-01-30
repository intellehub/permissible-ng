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
        
        if ($this->confirm('Do you want to modify the User model?', true)) {
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
        // Required additions
        $newUseStatements = [
            'use Tymon\JWTAuth\Contracts\JWTSubject;',
            'use Shahnewaz\PermissibleNg\Traits\Permissible;',
            'use Shahnewaz\PermissibleNg\Traits\JWTAuthentication;'
        ];
        
        // Parse existing content
        $lines = explode("\n", $content);
        $useStatements = [];
        $otherLines = [];
        $namespace = '';
        $inClass = false;
        $useTraitLine = -1;
        
        foreach ($lines as $i => $line) {
            $trimmedLine = trim($line);
            
            // Collect namespace
            if (preg_match('/^namespace\s+(.+);/', $trimmedLine, $matches)) {
                $namespace = $matches[1];
                continue;
            }
            
            // Collect use statements
            if (preg_match('/^use\s+.+;$/', $trimmedLine)) {
                $useStatements[] = $trimmedLine;
                continue;
            }
            
            // Find class definition
            if (preg_match('/^class\s+User\s+extends\s+Authenticatable/', $trimmedLine)) {
                $inClass = true;
                // Update class definition to implement JWTSubject if not already implementing
                if (!str_contains($trimmedLine, 'implements')) {
                    $line = str_replace('Authenticatable', 'Authenticatable implements JWTSubject', $trimmedLine);
                } elseif (!str_contains($trimmedLine, 'JWTSubject')) {
                    $line = str_replace('implements', 'implements JWTSubject,', $trimmedLine);
                }
            }
            
            // Find use trait statement
            if ($inClass && preg_match('/^\s*use\s+.*?;/', $trimmedLine)) {
                $useTraitLine = $i;
                // Extract existing traits
                preg_match('/use\s+([^;]+)/', $trimmedLine, $matches);
                $existingTraits = array_map('trim', explode(',', $matches[1]));
                // Add our traits if not present
                $newTraits = array_merge($existingTraits, ['Permissible', 'JWTAuthentication']);
                $newTraits = array_unique($newTraits);
                $line = '    use ' . implode(', ', $newTraits) . ';';
            }
            
            $otherLines[] = $line;
        }
        
        // Add use trait statement if not found
        if ($useTraitLine === -1) {
            array_splice($otherLines, array_search('{', array_map('trim', $otherLines)) + 1, 0, 
                '    use Permissible, JWTAuthentication;');
        }
        
        // Merge use statements, remove duplicates
        $useStatements = array_merge($useStatements, $newUseStatements);
        $useStatements = array_unique($useStatements);
        sort($useStatements);
        
        // Rebuild file content
        return "<?php\n\n" . 
               "namespace $namespace;\n\n" .
               implode("\n", $useStatements) . "\n\n" .
               implode("\n", $otherLines);
    }
}
