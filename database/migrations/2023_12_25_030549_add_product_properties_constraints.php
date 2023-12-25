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
        Schema::table('product_properties', function (Blueprint $table){
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('property_type')
                ->references('id')
                ->on('property_types')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_properties', function (Blueprint $table){
            $table->dropConstrainedForeignId('product_id');
            $table->dropConstrainedForeignId('property_types');
        });
    }
};
