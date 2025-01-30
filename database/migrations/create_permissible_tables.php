<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Only drop our package tables, NOT the users table
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        // Ensure users table exists before creating our tables
        if (!Schema::hasTable('users')) {
            throw new \Exception('Users table must exist before running this migration.');
        }

        // Create roles table first
        Schema::create('roles', function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->integer('weight');
            $table->softDeletes();
            $table->index('weight');
        });

        // Create permissions table
        Schema::create('permissions', function(Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->softDeletes();
            $table->index(['type', 'name']);
        });

        // Create role_user pivot table
        Schema::create('role_user', function(Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        // Create role_permission pivot table
        Schema::create('role_permission', function(Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        // Modify users table if configured
        if (config('permissible.first_last_name_migration', false)) {
            // First, add the new columns
            Schema::table('users', function (Blueprint $table) {
                // Add first_name and last_name if they don't exist
                if (!Schema::hasColumn('users', 'first_name')) {
                    $table->string('first_name')->after('id')->nullable();
                }
                if (!Schema::hasColumn('users', 'last_name')) {
                    $table->string('last_name')->after('first_name')->nullable();
                }
                
                // Add soft deletes if it doesn't exist
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Then, if name exists and both first_name and last_name exist, drop the name column
            if (Schema::hasColumn('users', 'name') && 
                Schema::hasColumn('users', 'first_name') && 
                Schema::hasColumn('users', 'last_name')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('name');
                });
            }

            // Finally, add the fulltext index if it doesn't exist
            if (config('database.default') === 'mysql') {
                $indexExists = collect(DB::select("SHOW INDEX FROM users WHERE Key_name = 'fullNameIndex'"))->isNotEmpty();
                if (!$indexExists) {
                    DB::statement('ALTER TABLE users ADD FULLTEXT fullNameIndex (first_name, last_name)');
                }
            } elseif (config('database.default') === 'pgsql') {
                $indexExists = collect(DB::select("SELECT 1 FROM pg_indexes WHERE indexname = 'fullnameindex'"))->isNotEmpty();
                if (!$indexExists) {
                    DB::statement("CREATE INDEX fullNameIndex ON users USING gin(to_tsvector('english', first_name || ' ' || last_name))");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop only our package tables, NOT the users table
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        if (config('permissible.first_last_name_migration', false)) {
            // First drop the fulltext index if it exists
            if (config('database.default') === 'mysql') {
                $indexExists = collect(DB::select("SHOW INDEX FROM users WHERE Key_name = 'fullNameIndex'"))->isNotEmpty();
                if ($indexExists) {
                    DB::statement('ALTER TABLE users DROP INDEX fullNameIndex');
                }
            } elseif (config('database.default') === 'pgsql') {
                $indexExists = collect(DB::select("SELECT 1 FROM pg_indexes WHERE indexname = 'fullnameindex'"))->isNotEmpty();
                if ($indexExists) {
                    DB::statement('DROP INDEX fullNameIndex');
                }
            }

            // Modify users table
            Schema::table('users', function (Blueprint $table) {
                // Drop first_name and last_name if they exist
                if (Schema::hasColumn('users', 'first_name')) {
                    $table->dropColumn('first_name');
                }
                if (Schema::hasColumn('users', 'last_name')) {
                    $table->dropColumn('last_name');
                }
                
                // Add back name column if it doesn't exist
                if (!Schema::hasColumn('users', 'name')) {
                    $table->string('name')->after('id');
                }
                
                // Drop soft deletes if it exists
                if (Schema::hasColumn('users', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
}; 