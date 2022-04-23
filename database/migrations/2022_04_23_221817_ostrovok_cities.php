<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OstrovokCities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ostrovok_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('CASCADE');
            $table->integer('ostrovok_city_id');
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
        Schema::drop('ostrovok_cities');
    }
}
