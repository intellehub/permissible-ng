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
        // Create roles table
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('code');
                $table->integer('weight');
                $table->softDeletes();
                $table->index('weight');
            });
        }

        // Create permissions table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function(Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->string('name');
                $table->softDeletes();
                $table->index(['type', 'name']);
            });
        }

        // Create role_user pivot table
        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function(Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->integer('role_id')->unsigned();
                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
                $table->primary(['user_id', 'role_id']);
            });
        }

        // Create role_permission pivot table
        if (!Schema::hasTable('role_permission')) {
            Schema::create('role_permission', function(Blueprint $table) {
                $table->integer('role_id')->unsigned();
                $table->integer('permission_id')->unsigned();
                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');
                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');
                $table->primary(['role_id', 'permission_id']);
            });
        }

        // Modify users table if configured
        if (config('permissible.first_last_name_migration', false)) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'name')) {
                    $table->dropColumn('name');
                }

                if (!Schema::hasColumn('users', 'first_name')) {
                    $table->string('first_name')->after('id');
                }

                if (!Schema::hasColumn('users', 'last_name')) {
                    $table->string('last_name')->after('first_name');
                }

                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }

                // Check for existing index before creating
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
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the tables in reverse order to handle foreign key constraints
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        if (config('permissible.first_last_name_migration', false)) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'first_name')) {
                    $table->dropColumn('first_name');
                }
                if (Schema::hasColumn('users', 'last_name')) {
                    $table->dropColumn('last_name');
                }
                if (!Schema::hasColumn('users', 'name')) {
                    $table->string('name')->after('id');
                }
                if (Schema::hasColumn('users', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
                
                // Drop index if exists
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
            });
        }
    }
}; 