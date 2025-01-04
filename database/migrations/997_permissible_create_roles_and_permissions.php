<?php

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
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('code');
                $table->integer('weight');
            });
        } else {
            Schema::table('roles', function(Blueprint $table) {

                $hasIdColumn = Schema::hasColumn('roles', 'id');
                $hasNameColumn = Schema::hasColumn('roles', 'name');
                $hasCodeColumn = Schema::hasColumn('roles', 'code');
                $hasWeightColumn = Schema::hasColumn('roles', 'weight');

                if (!$hasIdColumn) {
                    $table->increments('id');
                }
                if (!$hasNameColumn) {
                    $table->string('name');
                }
                if (!$hasCodeColumn) {
                    $table->string('code');
                }
                if (!$hasWeightColumn) {
                    $table->integer('weight');
                }
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function(Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->string('name');
            });
        } else {

            Schema::table('permissions', function(Blueprint $table) {

                $hasIdColumn = Schema::hasColumn('permissions', 'id');
                $hasNameColumn = Schema::hasColumn('permissions', 'name');
                $hasTypeColumn = Schema::hasColumn('permissions', 'type');
                
                if (!$hasIdColumn) {
                    $table->increments('id');
                }

                if (!$hasTypeColumn) {
                    $table->string('type');
                }

                if (!$hasNameColumn) {
                    $table->string('name');
                }
            });
        }

        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function(Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->integer('role_id')->unsigned();
            });
        } else {
            Schema::table('role_user', function(Blueprint $table) {

                $hasUserIdColumn = Schema::hasColumn('role_user', 'user_id');
                $hasRoleIdColumn = Schema::hasColumn('role_user', 'role_id');

                if (!$hasUserIdColumn) {
                    $table->integer('user_id')->unsigned();
                }
                if (!$hasRoleIdColumn) {
                    $table->integer('role_id')->unsigned();
                }
            });
        }

        if (!Schema::hasTable('role_permission')) {
            Schema::create('role_permission', function(Blueprint $table) {
                $table->integer('role_id')->unsigned();
                $table->integer('permission_id')->unsigned();
            });
        } else {
            Schema::table('role_permission', function(Blueprint $table) {
                $hasRoleIdColumn = Schema::hasColumn('role_permission', 'role_id');
                $hasPermissionIdIdColumn = Schema::hasColumn('role_permission', 'permission_id');
                
                if (!$hasRoleIdColumn) {
                    $table->integer('role_id')->unsigned();
                }
                if (!$hasPermissionIdIdColumn) {
                    $table->integer('permission_id')->unsigned();
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
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('role_permission');
    }
};
