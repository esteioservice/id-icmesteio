<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Administracao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administracao', function (Blueprint $table) {
            $table->id();
            $table->string('grupo');
            $table->string('numeral');
            $table->string('nome');
            $table->string('usuario');
            $table->string('senha');
            $table->string('logo');
            $table->string('termos');
            $table->integer('user_update');
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
        Schema::dropIfExists('administracao');
    }
}
