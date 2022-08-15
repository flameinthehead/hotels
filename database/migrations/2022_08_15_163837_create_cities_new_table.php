<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities_new', function (Blueprint $table) {
            $table->increments('city_id');
            $table->unsignedInteger('country_id')->default(0)->index('country_id');
            $table->unsignedInteger('region_id')->default(0)->index('region_id');
            $table->string('name', 128)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities_new');
    }
}
