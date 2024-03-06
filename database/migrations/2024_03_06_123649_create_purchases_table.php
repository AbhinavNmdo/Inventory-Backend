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
        Schema::create('purchases', function (Blueprint $table) {
            $table->mediumIncrements('id')->unsigned();
            $table->mediumInteger('product_id')->unsigned();
            $table->string('vendor')->nullable();
            $table->string('bill_no', 50)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('quantity', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->mediumInteger('created_by')->unsigned()->nullable();
            $table->mediumInteger('updated_by')->unsigned()->nullable();

            $table->index('product_id');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
