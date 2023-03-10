<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Todo;
use App\Models\User;
use App\Models\TodoList;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Factories\Sequence;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $admin = User::factory()->create(
            [
                'name' => 'Test User',
                'email' => 'admin@example.com',
            ]
        );

        $admin->assignRole(Role::where('name', 'admin')->first());


        $users = User::factory(2)
            ->state(
                new Sequence(
                    ['email' => 'user@example.com'],
                    ['email' => 'user2@example.com'],
                )
            )
            ->create();

        $this->seedUsers($users);

        $users = User::factory(10)->create();
        $this->seedUsers($users);
    }

    protected function seedUsers($users)
    {
        // This could be better but want varied numbers between them.
        foreach ($users as $user) {
            $user->assignRole(Role::where('name', 'user')->first());
            $user->todoLists()->saveMany(TodoList::factory(random_int(0, 10))->make());
            foreach ($user->todoLists as $list) {
                $list->todos()->saveMany(Todo::factory(random_int(0, 10))->make());
            }
        }
    }
}
