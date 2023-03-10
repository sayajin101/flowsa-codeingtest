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

class TodoTest extends TestCase
{
    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setup();
        $user = $this->generateUser();
        $this->user = $user['user'];
        $this->token = $user['token'];
    }

    public function test_add_todos()
    {
        $list = TodoList::factory()->make();
        $this->user->todoLists()->save($list);

        $title = fake()->sentence();
        $description = fake()->sentences(3, true);

        $response = $this->json(
            'POST',
            'api/lists/' . $list->id . '/todos',
            [
                'title' => $title,
                'description' => $description,
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(201)
            ->assertJsonPath('title', $title)
            ->assertJsonPath('description', $description)
            ->assertJsonPath('status', 'incomplete');

    }

    public function test_update_todos()
    {
        $this->user = User::factory()->create();
        $this->user->assignRole(Role::where('name', 'user')->first());
        $token = Auth::login($this->user);

        $list = TodoList::factory()->make();
        $this->user->todoLists()->save($list);

        $todo = Todo::factory(['status' => Status::INCOMPLETE])->make();
        $list->todos()->save($todo);

        $title = fake()->sentence();
        $description = fake()->sentences(3, true);

        $response = $this->json(
            'PUT',
            'api/lists/' . $list->id . '/todos/' . $todo->id,
            [
                'title' => $title,
                'description' => $description,
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonPath('title', $title)
            ->assertJsonPath('description', $description)
            ->assertJsonPath('status', 'incomplete');

    }

    public function test_update_todos_to_have_deadlines()
    {
        $this->user = User::factory()->create();
        $this->user->assignRole(Role::where('name', 'user')->first());
        $token = Auth::login($this->user);

        $list = TodoList::factory()->make();
        $this->user->todoLists()->save($list);

        $todo = Todo::factory(['status' => Status::INCOMPLETE])->make();
        $list->todos()->save($todo);

        $title = fake()->sentence();
        $description = fake()->sentences(3, true);

        $response = $this->json(
            'PUT',
            'api/lists/' . $list->id . '/todos/' . $todo->id,
            [
                'title' => $title,
                'description' => $description,
                'deadline' => new Carbon('+24 days')
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonPath('title', $title)
            ->assertJsonPath('description', $description)
            ->assertJsonPath('status', 'incomplete')
            ->assertJsonPath('overdue', false);

    }

    public function test_update_todos_to_complete_updates_list_status()
    {
        $this->user = User::factory()->create();
        $this->user->assignRole(Role::where('name', 'user')->first());
        $token = Auth::login($this->user);

        $list = TodoList::factory()->make();
        $this->user->todoLists()->save($list);

        $todo = Todo::factory(['status' => Status::INCOMPLETE])->make();
        $list->todos()->save($todo);

        $response = $this->json(
            'PUT',
            'api/lists/' . $list->id . '/todos/' . $todo->id,
            [
                'status' => 'complete'
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'complete')
            ->assertJsonPath('overdue', false);

        $list->refresh();

        $this->assertEquals(Status::COMPLETE, $list->status, 'List status has updated');

    }

    public function test_deadline_overdue()
    {
        $this->user = User::factory()->create();
        $this->user->assignRole(Role::where('name', 'user')->first());
        $token = Auth::login($this->user);

        $list = TodoList::factory()->make();
        $this->user->todoLists()->save($list);

        $todo = Todo::factory(['status' => Status::INCOMPLETE, 'deadline' => new Carbon('-5 days')])->make();
        $list->todos()->save($todo);

        $response = $this->json(
            'GET',
            'api/lists/' . $list->id . '/todos/' . $todo->id,
            [
                'status' => 'complete'
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'incomplete')
            ->assertJsonPath('overdue', true);

    }


    public function test_adding_todo_to_complete_list_updates_list_status()
    {
        $this->user = User::factory()->create();
        $this->user->assignRole(Role::where('name', 'user')->first());
        $token = Auth::login($this->user);

        $list = TodoList::factory(['status' => Status::COMPLETE])->make();
        $this->user->todoLists()->save($list);

        $todo = Todo::factory(['status' => Status::COMPLETE])->make();
        $list->todos()->save($todo);

        $list->refresh();

        $this->assertEquals(Status::COMPLETE, $list->status, 'List status has updated');

        $response = $this->json(
            'PUT',
            'api/lists/' . $list->id . '/todos/' . $todo->id,
            [
                'status' => 'incomplete',
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'incomplete');

        $list->refresh();
        $this->assertEquals(Status::INCOMPLETE, $list->status, 'List status has updated');

    }

    public function test_deleting_a_todo()
    {
        $this->user = User::factory()->create();
        $this->user->assignRole(Role::where('name', 'user')->first());
        $token = Auth::login($this->user);

        $list = TodoList::factory(['status' => Status::COMPLETE])->make();
        $this->user->todoLists()->save($list);

        $todo = Todo::factory(['status' => Status::COMPLETE])->make();
        $list->todos()->save($todo);

        $list->refresh();

        $this->assertEquals(Status::COMPLETE, $list->status, 'List status has updated');

        $response = $this->json(
            'DELETE',
            'api/lists/' . $list->id . '/todos/' . $todo->id,
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        );
        $response->assertStatus(200);

        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);

    }


    protected function tearDown(): void
    {
        parent::tearDown();
    }

}
