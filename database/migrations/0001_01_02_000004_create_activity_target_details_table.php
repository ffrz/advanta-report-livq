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
        Schema::create('activity_target_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->on('activity_targets')->cascadeOnDelete();
            $table->foreignId('type_id')->on('activity_types')->restrictOnDelete();
            $table->unsignedTinyInteger('quarter_qty')->default(0);
            $table->unsignedTinyInteger('month1_qty')->default(0);
            $table->unsignedTinyInteger('month2_qty')->default(0);
            $table->unsignedTinyInteger('month3_qty')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_target_details');
    }
};
