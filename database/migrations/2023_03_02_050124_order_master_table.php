<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_master', function (Blueprint $table) {
            $table->id('order_master_id');
			$table->integer('branch_id');
			$table->integer('patient_id');
			$table->boolean('is_paid')->default(false)->nullable();
			$table->float('net_amount');
			$table->float('discount');
			$table->float('paid_amount');
			$table->float('due_amount');
			$table->float('adjusted_amount');
			$table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_master');
    }
};
