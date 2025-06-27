<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('type_id')->constrained('activity_types')->restrictOnDelete();
            $table->date('date');
            $table->string('latlong', 100)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('responded_by_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->datetime('responded_datetime')->nullable();
            $table->enum('status', ['approved', 'rejected', 'not_responded'])->default('not_responded');

            $table->datetime('created_datetime')->nullable();
            $table->datetime('updated_datetime')->nullable();

            $table->foreignId('created_by_uid')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_uid')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
