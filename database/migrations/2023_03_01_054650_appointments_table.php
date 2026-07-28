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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
			$table->integer('patient_id');
			$table->integer('doctor_id');
			$table->integer('clinic_id')->nullable();
			$table->integer('branch_id')->nullable();
			$table->integer('treatment_id')->nullable();
			$table->integer('suggested_treatment_id');
			$table->integer('status');
			$table->string('notes');
			$table->timestamps('appointment_date');
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
        Schema::dropIfExists('appointments');
    }
};
