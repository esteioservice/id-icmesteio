<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // Create UNIQUE KEY FOR EMAIL INSTITUCIONAL
      Schema::table("users", function (Blueprint $table) {
        $table->unique('email_institucional');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
