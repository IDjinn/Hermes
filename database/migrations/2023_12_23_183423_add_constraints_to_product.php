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
            $table->foreign('product_type')
                ->references('id')
                ->on('product_types')
                ->nullOnDelete()
                ->nullable();

            $table->foreign('category')
                ->references('id')
                ->on('categories')
                ->nullOnDelete()
                ->nullable();

            $table->foreign('sub_category')
                ->references('id')
                ->on('sub_categories')
                ->nullOnDelete()
                ->nullable();

            $table->index(['product_type', 'category', 'sub_category'], 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropForeign('product_product_type_foreign');
            $table->dropForeign('product_category_foreign');
            $table->dropForeign('product_sub_category_foreign');
        });
    }
};
