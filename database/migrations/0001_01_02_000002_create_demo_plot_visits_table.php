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
        Schema::create('demo_plot_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('demo_plot_id')->constrained()->restrictOnDelete();
            $table->date('visit_date');
            $table->enum('plant_status', array_keys(DemoPlot::PlantStatuses))->default(DemoPlot::PlantStatus_Satisfactoy);
            $table->string('latlong', 100)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->text('notes')->nullable();

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
