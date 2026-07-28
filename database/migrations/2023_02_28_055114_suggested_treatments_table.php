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
        Schema::create('suggested_treatments', function (Blueprint $table) {
            $table->id('suggested_treatment_id');
			$table->integer('treament_id')->nullable();
			$table->integer('patient_id')->nullable(); 
			$table->integer('SuggestedBydoctor_id')->nullable();
			$table->string('rate');
			$table->string('selected_teeth');
			$table->float('amount');
			$table->integer('selected_teeth_count');
			$table->string('treatment_status');
			$table->timestamp('treatment_datetime')->nullable();
			$table->string('strComments');
			$table->boolean('is_billing');
			$table->string('ref_id');
			$table->integer('is_completed_by_doctorId');
			$table->timestamp('completed_datetime')->nullable();
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
        Schema::dropIfExists('suggested_treatments');
    }
};
