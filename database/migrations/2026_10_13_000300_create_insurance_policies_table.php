<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('insurer', 80);
            $table->string('type', 20);                       // life | motor | health | property | travel | education | retirement | other
            $table->string('policy_number', 60)->nullable();
            $table->string('plan_name', 120)->nullable();
            $table->decimal('sum_assured', 15, 2)->nullable();
            $table->decimal('premium', 12, 2);
            $table->string('frequency', 12);                  // monthly | quarterly | half_yearly | yearly | single
            $table->date('start_date');
            $table->date('next_due_date')->nullable();
            $table->date('end_date')->nullable();             // maturity / expiry
            $table->string('status', 12)->default('active');  // proposal | active | lapsed | matured | cancelled
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->string('vehicle_number', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'next_due_date']);
            $table->index(['user_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_policies');
    }
};
