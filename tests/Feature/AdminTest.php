<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\TodoList;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class AdminTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setup();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_user_login()
    {

        $user = User::factory()->create();
        $user->assignRole(Role::where('name', 'admin')->first());


        $response = $this->json('POST',
            'api/auth/login',
            [
                'email' => $user->email,
                'password' => 'password'
            ]
        );

        $response->assertStatus(200)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'authorisation' => [
                    'token',
                    'type'
                ]
            ]);

        $user->delete();
    }

    public function test_user_list()
    {

        $user = User::factory()->create();
        $user->assignRole(Role::where('name', 'admin')->first());
        $token = Auth::login($user);

        $users = User::factory(5)->create();

        $response = $this->json(
            'GET',
            'api/users',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200);

        $user->delete();
        foreach ($users as $user) {
            $user->delete();
        }
    }

    public function test_user_list_not_for_users()
    {

        $users = User::factory(5)->create();
        $token = Auth::login($users->first());

        $response = $this->json(
            'GET',
            'api/users',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(403);

        foreach ($users as $user) {
            $user->delete();
        }
    }

    public function test_admin_list_index()
    {

        $admin = User::factory()->create();
        $admin->assignRole(Role::where('name', 'admin')->first());
        $token = Auth::login($admin);

        $users = User::factory(5)->create();

        foreach ($users as $user) {
            $user->todoLists()->saveMany(TodoList::factory(random_int(0, 10))->make());
        }

        $response = $this->json(
            'GET',
            'api/lists',
            [],
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertStatus(200);

        foreach ($response->original as $todolist) {
            $this->assertNotEquals($todolist->user_id, $admin->id);
        }

        $user->delete();
    }


    protected function tearDown(): void
    {
        parent::tearDown();
    }

}
