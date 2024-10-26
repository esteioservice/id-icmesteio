<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmailDelegado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_delegado', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->boolean('status');
            $table->string('user_id');
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
        Schema::dropIfExists('email_delegado');
    }

}
