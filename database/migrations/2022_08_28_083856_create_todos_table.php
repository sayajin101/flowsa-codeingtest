<?php

use \App\Enum\Priority;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->foreignUuid('list_id')->constrained('todo_lists');
            $table->string('title', 1023);
            $table->text('description');
            $table->enum('status', ['incomplete', 'complete'])->default('incomplete');
            $table->integer('priority')->default(Priority::MEDIUM->value);
            $table->dateTime('deadline')->nullable();

            $table->primary('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('todos');
    }
};
