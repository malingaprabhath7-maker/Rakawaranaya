<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('title', 160);
            $table->string('type', 20)->default('follow_up');  // call | meeting | follow_up | document | other
            $table->date('due_on');
            $table->time('due_time')->nullable();
            $table->timestamp('done_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'done_at', 'due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
