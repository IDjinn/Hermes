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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedDecimal('price')->nullable()->after('datasheet');
            $table->enum('unit', ['unit', 'meter', 'box', 'linear-meter', 'kit'])->nullable()->after('price');
            $table->foreignId('color')->nullable()->after('unit'); // will be created color table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'unit', 'color']);
        });
    }
};
