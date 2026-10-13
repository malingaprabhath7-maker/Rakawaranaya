<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('insurance_policy_id')->constrained('insurance_policies')->cascadeOnDelete();
            $table->date('paid_on');
            $table->decimal('amount', 12, 2);
            $table->decimal('commission', 12, 2)->default(0);
            $table->string('method', 20)->default('cash');   // cash | bank | card | online | cheque
            $table->string('reference', 80)->nullable();
            $table->date('covers_due_date')->nullable();     // the due date this payment settled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'paid_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
