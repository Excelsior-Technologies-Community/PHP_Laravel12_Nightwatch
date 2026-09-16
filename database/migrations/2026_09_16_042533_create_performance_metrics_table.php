<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();

            $table->string('method', 10);
            $table->string('path', 500);
            $table->string('route_name')->nullable();

            $table->unsignedSmallInteger('status_code')->nullable();

            $table->decimal('duration_ms', 12, 3);
            $table->decimal('memory_mb', 10, 3)->nullable();

            $table->string('category', 20);

            $table->timestamps();

            $table->index('method');
            $table->index('status_code');
            $table->index('category');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};