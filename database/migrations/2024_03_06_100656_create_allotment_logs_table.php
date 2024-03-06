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
        Schema::create('allotment_logs', function (Blueprint $table) {
            $table->mediumIncrements('id')->unsigned();
            $table->mediumInteger('user_id')->unsigned()->nullable();
            $table->mediumInteger('product_info_id')->unsigned()->nullable();
            $table->date('allotment_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->mediumInteger('created_by')->unsigned()->nullable();
            $table->mediumInteger('updated_by')->unsigned()->nullable();

            $table->index('user_id');
            $table->index('product_info_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('product_info_id')->references('id')->on('product_infos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allotment_logs');
    }
};
