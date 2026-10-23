<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('phone', 40);
            $table->string('license_plate', 30);
            $table->string('service');
            $table->date('preferred_date');
            $table->string('preferred_time', 40);
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('pending');
            $table->timestamps();
            $table->index(['status', 'preferred_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
