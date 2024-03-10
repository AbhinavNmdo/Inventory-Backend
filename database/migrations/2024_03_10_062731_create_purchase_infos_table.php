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
        Schema::create('purchase_infos', function (Blueprint $table) {
            $table->mediumIncrements('id')->unsigned();
            $table->mediumInteger('purchase_id')->unsigned();
            $table->mediumInteger('product_id')->unsigned();
            $table->decimal('quantity', 6, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->mediumInteger('created_by')->unsigned()->nullable();
            $table->mediumInteger('updated_by')->unsigned()->nullable();

            $table->index('purchase_id');
            $table->index('product_id');
            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_infos');
    }
};
