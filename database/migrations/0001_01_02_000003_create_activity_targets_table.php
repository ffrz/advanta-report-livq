<?php

use App\Models\ActivityType;
use App\Models\Product;
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
        Schema::create('activity_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->on('activity_types')->restrictOnDelete();
            $table->foreignId('user_id')->on('users')->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->unsignedTinyInteger('quarter_qty')->default(0);
            $table->unsignedTinyInteger('month1_qty')->default(0);
            $table->unsignedTinyInteger('month2_qty')->default(0);
            $table->unsignedTinyInteger('month3_qty')->default(0);
            $table->date('notes');

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
        Schema::dropIfExists('activity_targets');
    }
};
