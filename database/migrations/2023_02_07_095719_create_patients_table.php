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
        Schema::create('patients', function (Blueprint $table) {
            $table->id('patient_id');
			$table->integer('clinic_id')->nullable();
			$table->integer('branch_id')->nullable();
			$table->integer('doctor_id')->nullable();
			$table->integer('group_id')->nullable();
			$table->string('case_no');
			$table->string('first_name');
			$table->string('middle_name')->nullable();
			$table->string('last_name');
            $table->string('email')->nullable();
		    $table->string('mobile_no');
			$table->string('date_of_birth')->nullable();
			$table->string('address')->nullable();
			$table->string('gender')->nullable();
			$table->string('occumpation')->nullable();
			$table->string('language')->nullable();
			$table->string('note')->nullable();
            //$table->timestamp('email_verified_at')->nullable();
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
        Schema::dropIfExists('patients');
    }
};
