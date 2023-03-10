<?php

namespace Tests;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function generateUser()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::where('name', 'user')->first());
        $token = Auth::login($user);

        return ['user' => $user, 'token' => $token];
    }
}
