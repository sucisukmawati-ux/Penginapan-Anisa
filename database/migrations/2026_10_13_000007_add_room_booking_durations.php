<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('room_type')->default('fan');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('duration_type')->default('overnight');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['duration_type', 'start_at', 'end_at']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('room_type');
        });
    }
};
