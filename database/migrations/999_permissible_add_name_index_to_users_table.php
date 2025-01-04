<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pgSqlStmt = "CREATE INDEX fullNameIndex ON users USING gin(to_tsvector('english', first_name || ' ' || last_name))";
        $mySqlStmt = "ALTER TABLE users ADD FULLTEXT fullNameIndex (first_name,last_name);";

        if (config('permissible.first_last_name_migration', false) === true) {
            
            if (env('DB_CONNECTION') === 'pgsql') {
                // Postgresql
                DB::statement($pgSqlStmt);
            } elseif (env('DB_CONNECTION') === 'mysql') {
                // MySQL
                DB::statement($mySqlStmt);
            } else {
                throw new Exception("DB type not supported yet.");
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
        Schema::table('users', function (Blueprint $table) {
            if (config('permissible.first_last_name_migration', false) === true) {
                if (env('DB_CONNECTION') === 'mysql') {
                    $table->dropIndex('fullNameIndex');
                }
            }
        });
    }
};
