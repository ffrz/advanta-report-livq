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
        Schema::table('demo_plots', function (Blueprint $table) {
            $table->unsignedBigInteger('population')->nullable()->after('field_location'); // ubah posisi 'after' sesuai kolom yang cocok
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demo_plots', function (Blueprint $table) {
            $table->dropColumn('population');
        });
    }
};
