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
        Schema::table('property_types', function (Blueprint $table) {
            $table->foreignId('product_type_id')->after('id')->nullable();
            $table->foreignId('category_id')->after('product_type_id')->nullable();
            $table->foreignId('sub_category_id')->after('category_id')->nullable();
            $table->string('name', 40)->after('sub_category_id')->nullable();
            $table->enum('type', [
                'default',
                'selection',
                'range',
                'boolean',
                'checkbox',
            ])->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_types', function (Blueprint $table) {
            $table->dropColumn(['name', 'product_type_id', 'category_id', 'sub_category_id']);
        });
    }
};
