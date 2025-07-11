<?php

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('list tasks', function () {
    $numOfTasks = 3;
    Task::factory()->count(3)->create();

    $response = $this->get('api/v1/tasks');
    $response
        ->assertStatus(200)
        ->assertJsonCount($numOfTasks, 'data');
});

test('creating task with invalid data fails', function () {
    $task = Task::factory()->make();
    $task->status = 'invalid status';
    $response = $this->postJson('api/v1/tasks', $task->toArray());
    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors('status');
});

test('create task', function () {
    $task = Task::factory()->make();
    $response = $this->postJson('api/v1/tasks', $task->toArray());
    $response
        ->assertStatus(201)
        ->assertJsonPath('data.id', 1);
});

test('updating task with invalid data fails', function () {
    $task = Task::factory()->create();
    $response = $this->putJson("api/v1/tasks/{$task->id}", ['status' => 'invalid status']);
    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors('status');
});

test('updating task by non existing id fails', function () {
    $task = Task::factory()->create();
    $task->title = 'Title updated';
    $nonExistingId = $task->id + 1;
    $response = $this->putJson("api/v1/tasks/{$nonExistingId}", $task->toArray());
    $response
        ->assertStatus(404)
        ->assertJson([
            'message' => "No query results for model [App\\Models\\Task] {$nonExistingId}",
        ]);
});

test('update task (put)', function () {
    $task = Task::factory()->create();
    $titleUpdated = 'Title updated';
    $task->title = $titleUpdated;
    $response = $this->putJson("api/v1/tasks/{$task->id}", $task->toArray());
    $response
        ->assertStatus(200)
        ->assertJsonPath('data.title', $titleUpdated);
});

test('update task (patch)', function () {
    $task = Task::factory()->create();
    $titleUpdated = 'Title updated';
    $response = $this->putJson("api/v1/tasks/{$task->id}", ['title' => $titleUpdated]);
    $response
        ->assertStatus(200)
        ->assertJsonPath('data.title', $titleUpdated)
        ->assertJsonPath('data.description', $task->description);
});

test('fetching task by invalid id fails', function () {
    $task = Task::factory()->create();
    $nonExistingId = $task->id + 1;
    $response = $this->getJson("api/v1/tasks/{$nonExistingId}");
    $response
        ->assertStatus(404)
        ->assertJson([
            'message' => "No query results for model [App\\Models\\Task] {$nonExistingId}",
        ]);
});

test('fetch task by id', function () {
    $task = Task::factory()->create();
    $response = $this->getJson("api/v1/tasks/{$task->id}");
    $response
        ->assertStatus(200)
        ->assertJsonPath('data.title', $task->title);
});

test('delete task', function () {
    $task = Task::factory()->create();
    $response = $this->deleteJson("api/v1/tasks/{$task->id}");
    $response->assertStatus(200);
});
