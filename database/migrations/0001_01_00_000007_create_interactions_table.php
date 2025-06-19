<?php

use App\Models\Interaction;
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
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->enum('type', array_keys(Interaction::Types))->default(Interaction::Type_Visit);
            $table->enum('status', array_keys(Interaction::Statuses))->default(Interaction::Status_Planned);
            $table->enum('engagement_level', array_keys(Interaction::EngagementLevels))->default(Interaction::EngagementLevel_None);
            $table->date('date');
            $table->text('subject')->nullable();
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->string('location', 100)->nullable();
            $table->string('image_path', 500)->nullable();

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
        Schema::dropIfExists('interactions');
    }
};
