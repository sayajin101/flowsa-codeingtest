<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SetupPermissions extends Command
{

    public $roles = [
        'admin' => [
            'users_view_all',
            'lists_view_all',
        ],
        'user' => [
            'lists_view',
            'lists_index',
            'lists_read',
            'lists_create',
            'lists_update',
            'lists_delete',
            'lists_add_todo',
            'todo_view',
            'todo_index',
            'todo_create',
            'todo_read',
            'todo_update',
            'todo_delete',
        ]
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach ($this->roles as $role => $permissions) {
            $roleObject = Role::firstOrCreate(['name' => $role]);

            foreach ($permissions as $permission) {
                $permissionObject = Permission::firstOrCreate(['name' => $permission]);

                $permissionObject->assignRole($roleObject);
            }

        }

        return Command::SUCCESS;
    }
}
