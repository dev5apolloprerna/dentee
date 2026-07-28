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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
			$table->integer('clinic_id')->nullable();
            $table->string('user_name')->unique();
			$table->string('first_name');
			$table->string('last_name');
            $table->string('email')->nullable();
			$table->string('password');
			$table->string('address');
			$table->string('mobile_no');
			$table->integer('last_modify_by')->nullable();
			$table->string('isadmin')->nullable();
            //$table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
