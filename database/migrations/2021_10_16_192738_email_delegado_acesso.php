<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmailDelegadoAcesso extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_delegado_acesso', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('emaildelegado_id');
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
        Schema::dropIfExists('email_delegado_acesso');
    }
}
