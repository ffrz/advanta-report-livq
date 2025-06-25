<?php

use App\Models\DemoPlot;
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
        Schema::create('demo_plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->date('plant_date');
            $table->string('owner_name', 100);
            $table->string('owner_phone', 40)->nullable();
            $table->string('field_location', 500)->nullable();
            $table->string('latlong', 100)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();

            $table->enum('plant_status', array_keys(DemoPlot::PlantStatuses))->default(DemoPlot::PlantStatus_NotYetPlanted);
            $table->date('last_visit')->nullable();

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
        Schema::dropIfExists('demo_plots');
    }
};
