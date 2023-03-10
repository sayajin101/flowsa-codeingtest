<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoList;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;

class TodoController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Todo::class, 'todo');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(TodoList $todoList)
    {
        $this->authorize('view', $todoList);

        return $todoList->todos()->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTodoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TodoList $todoList, StoreTodoRequest $request)
    {
        $this->authorize('add_todo', $todoList);

        $todo = new Todo($request->all());
        $todo->list_id = $todoList->id;
        $todo->save();

        $todo->setHidden(['todoList']);
        $todo->refresh();
        return $todo;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function show(TodoList $todoList, Todo $todo)
    {
        $todo->setHidden(['todoList']);
        return $todo;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTodoRequest  $request
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(TodoList $todoList, UpdateTodoRequest $request, Todo $todo)
    {
        $todo->update($request->all());
        $todo->setHidden(['todoList']);
        return $todo;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(TodoList $todoList, Todo $todo)
    {
        $todo->delete();
        return response()->json(['message' => 'success'], 200);
    }
}
