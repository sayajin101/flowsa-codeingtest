<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TodoListController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->prefix('auth')->group(
    function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/logout', 'logout');
        Route::post('/refresh', 'refresh');
    }
);

Route::controller(TodoListController::class)->prefix('lists')->group(
    function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{todo_list}', 'show');
        Route::put('/{todo_list}', 'update');
        Route::patch('/{todo_list}', 'update');
        Route::delete('/{todo_list}', 'destroy');
    }
);

Route::controller(TodoController::class)->prefix('lists/{todo_list}/todos')->group(
    function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{todo}', 'show');
        Route::put('/{todo}', 'update');
        Route::patch('/{todo}', 'update');
        Route::delete('/{todo}', 'destroy');

    }
);

Route::controller(UserController::class)->prefix('users')->group(
    function () {
        Route::get('/', 'index');
    }
);

