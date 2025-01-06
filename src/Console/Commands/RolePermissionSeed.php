<?php

namespace Shahnewaz\PermissibleNg\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;

class RolePermissionSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissible:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds roles and permissions. Creates super user.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        Artisan::call('db:seed', [ '--class' => 'Shahnewaz\PermissibleNg\Database\Seeder\RolePermissionSeeder']);
        $this->info('PermissibleAuth roles and permissions seeded!');
    }
}
