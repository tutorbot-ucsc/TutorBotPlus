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
        Schema::create('envio_solucion_problemas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');
            $table->integer('cant_casos_resuelto');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_problema');
            $table->unsignedBigInteger('id_certamen')->nullable();
            $table->boolean('solucionado');
            $table->unsignedBigInteger('id_juez');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_problema')->references('id')->on('problemas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_juez')->references('id')->on('jueces_virtuales')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envio_solucion_problemas');
    }
};
