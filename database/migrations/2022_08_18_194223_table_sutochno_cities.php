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
        Schema::create('sutochno_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')
                ->constrained('cities', 'city_id', 'city_id')
                ->onDelete('CASCADE');
            $table->string('sutochno_city_data');
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
        Schema::drop('sutochno_cities');
    }
};
