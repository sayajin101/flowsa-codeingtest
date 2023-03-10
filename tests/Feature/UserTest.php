<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;
use App\Enum\Status;
use App\Models\Todo;
use App\Models\User;
use App\Models\TodoList;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setup();
    }


    public function test_user_register()
    {

        $response = $this->json('POST',
            'api/auth/register',
            [
                'name' => fake()->name,
                'email' => fake()->email,
                'password' => 'password'
            ]
        );

        $response->assertStatus(201)
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

        $this->assertTrue($response->original['user']->hasRole('user'));
        $this->assertFalse($response->original['user']->hasRole('admin'));

    }

    public function test_user_register_validation_fail()
    {
        $response = $this->json('POST',
            'api/auth/register',
            [
            ]
        );

        $response->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'The name field is required.')
            ->assertJsonPath('errors.email.0', 'The email field is required.')
            ->assertJsonPath('errors.password.0', 'The password field is required.');
    }

    public function test_user_register_validation_fail_on_non_email()
    {
        $response = $this->json('POST',
            'api/auth/register',
            [
                'email' => 'hello'
            ]
        );

        $response->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'The email must be a valid email address.');
    }

    public function test_user_login()
    {

        $user = User::factory()->create();
        $user->assignRole(Role::where('name', 'user')->first());

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

    public function test_user_login_credentials_fail()
    {

        $user = User::factory()->create();
        $user->assignRole(Role::where('name', 'user')->first());

        $response = $this->json('POST',
            'api/auth/login',
            [
                'email' => $user->email,
                'password' => 'password123'
            ]
        );

        $response->assertStatus(401);
        $user->delete();
    }

    public function test_user_login_credentials_fail_email()
    {

        $user = User::factory()->create();
        $user->assignRole(Role::where('name', 'user')->first());

        $response = $this->json('POST',
            'api/auth/login',
            [
                'email' => $user->email . '123',
                'password' => 'password'
            ]
        );

        $response->assertStatus(401);
        $user->delete();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

}
