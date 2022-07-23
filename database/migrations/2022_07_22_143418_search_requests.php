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
        Schema::create('search_results', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name', 255);
            $table->integer('price');
            $table->string('book_link', 255);
            $table->string('facilities', 255);
            $table->float('distance_to_center');
            $table->string('preview', 255);
            $table->string('ref', 50);
            $table->float('latitude');
            $table->float('longitude');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('address', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('search_results');
    }
};
