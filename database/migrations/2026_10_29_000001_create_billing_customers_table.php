<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_customers', function (Blueprint $table) {
            $table->id();
            $table->string('idpel')->unique();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('officer')->nullable();
            $table->string('source_status')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->date('billing_period');
            $table->date('due_date');
            $table->string('status')->default('unpaid');
            $table->unsignedTinyInteger('reminders_sent')->default(0);
            $table->timestamp('first_reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_customers');
    }
};
