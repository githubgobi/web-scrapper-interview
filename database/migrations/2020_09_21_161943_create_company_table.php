<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('industries_id')->unsigned();
            $table->text('full_text')->nullable();
            $table->string('link')->unique();
            $table->string('cin')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('class')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->foreign('industries_id')
            ->references('id')
            ->on('industries')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company');
    }
}
