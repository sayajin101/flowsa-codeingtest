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

class TodoListTest extends TestCase
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

    public function test_create_todos_list()
    {
        $response = $this->json(
            'POST',
            'api/lists',
            [
                'title' => fake()->sentence(),
                'description' => fake()->sentences(3, true),
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(201);
    }


    public function test_create_todos_list_validation_fail_on_missing_title()
    {
        $response = $this->json(
            'POST',
            'api/lists',
            [
                'description' => fake()->sentences(3, true),
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(422);
    }

    public function test_create_todos_list_validation_fail_on_missing_description()
    {
        $response = $this->json(
            'POST',
            'api/lists',
            [
                'title' => fake()->sentence(),
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(422);
    }

    public function test_update_todos_list()
    {
        $list = TodoList::factory()->make();
        $this->user->todoLists()->save($list);

        $title = fake()->sentence();
        $description = fake()->sentences(3, true);

        $response = $this->json(
            'PUT',
            'api/lists/' . $list->id,
            [
                'title' => $title,
                'description' => $description,
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonPath('title', $title)
            ->assertJsonPath('description', $description);
    }

    public function test_update_todos_list_status_does_not_work()
    {
        $list = TodoList::factory(['status' => Status::INCOMPLETE])->make();
        $this->user->todoLists()->save($list);

        $response = $this->json(
            'PUT',
            'api/lists/' . $list->id,
            [
                'status' => 'complete',
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonPath('status', 'incomplete');
    }

    public function test_update_todos_list_user_id_does_not_work()
    {
        $list = TodoList::factory()->make();
        $this->user->todoLists()->save($list);

        $response = $this->json(
            'PUT',
            'api/lists/' . $list->id,
            [
                'user_id' => '123',
            ],
            ['Authorization' => 'Bearer ' . $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonPath('user_id', $list->user_id);
    }



    public function test_todos_list_can_only_be_updated_by_owner()
    {
        $list = TodoList::factory()->make();
        $this->user->todoLists()->save($list);

        $title = fake()->sentence();
        $description = fake()->sentences(3, true);

        $new_user = $this->generateUser();

        $response = $this->json(
            'PUT',
            'api/lists/' . $list->id,
            [
                'title' => $title,
                'description' => $description,
            ],
            ['Authorization' => 'Bearer ' . $new_user['token']]
        );

        $response->assertStatus(403);

        $list->refresh();

        $this->assertNotEquals($title, $list->title, 'Title not updated');
        $this->assertNotEquals($description, $list->description, 'Description not updated');

    }

    public function test_todos_list_can_only_be_deleted_by_owner()
    {
        $list = TodoList::factory()->make();
        $this->user->todoLists()->save($list);

        $new_user = $this->generateUser();

        $response = $this->json(
            'DELETE',
            'api/lists/' . $list->id,
            [],
            ['Authorization' => 'Bearer ' . $new_user['token']]
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas('todo_lists', ['id' => $list->id]);
    }

    public function test_deleting_a_todo_list_deletes_all_todos()
    {

        $list = TodoList::factory(['status' => Status::COMPLETE])->make();
        $this->user->todoLists()->save($list);

        $todo = Todo::factory(['status' => Status::COMPLETE])->make();
        $list->todos()->save($todo);

        $list->refresh();

        $this->assertEquals(Status::COMPLETE, $list->status, 'List status has updated');

        $response = $this->json(
            'DELETE',
            'api/lists/' . $list->id,
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        );
        $response->assertStatus(200);

        $this->assertDatabaseMissing('todo_lists', ['id' => $list->id]);
        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }


    public function test_get_all_todo_lists()
    {
        $this->user->todoLists()->saveMany(TodoList::factory(random_int(1, 10))->make());

        $response = $this->json(
            'GET',
            'api/lists/',
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        );
        $response->assertStatus(200)
            ->assertJsonPath('total', $this->user->todoLists()->count());
    }

    public function test_todo_lists_search()
    {
        $this->user->todoLists()->saveMany(TodoList::factory(random_int(1, 10))->make());
        $result = TodoList::factory(['title' => 'this is a demo list example', 'description' => 'description hello'])->make();
        $this->user->todoLists()->save($result);

        $response = $this->json(
            'GET',
            'api/lists/?search=demo',
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        );
        $response->assertStatus(200);

        foreach ($response->original as $item) {
            $this->assertEquals($item->id, $result->id);
        }

        $result = TodoList::factory(['title' => 'example', 'description' => 'testing hello'])->make();
        $this->user->todoLists()->save($result);

        $response = $this->json(
            'GET',
            'api/lists/?search=example testing',
            [],
            ['Authorization' => 'Bearer ' . $this->token]
        );
        $response->assertStatus(200);

        foreach ($response->original as $item) {
            $this->assertEquals($item->id, $result->id, 'Test search works on title AND description');
        }

    }

    protected function tearDown(): void
    {
        $this->user->delete();
        parent::tearDown();
    }

}
