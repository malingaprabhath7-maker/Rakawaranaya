<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->text('nic')->nullable();                 // encrypted at rest
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->unsignedTinyInteger('dependents')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('occupation', 120)->nullable();
            $table->decimal('monthly_income', 14, 2)->nullable();
            $table->string('stage', 20)->default('lead');     // lead | contacted | proposal | client | inactive
            $table->string('source', 40)->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'stage']);
            $table->index(['user_id', 'full_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
