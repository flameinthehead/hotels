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
        Schema::table('cities', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->change();
        });

        Schema::table('telegram_requests', function (Blueprint $table) {
//            $table->foreignId('city_id')->constrained('cities', 'city_id')->onDelete('CASCADE');
            /*$table->foreign('city_id')
                ->references('city_id')
                ->on('cities')
                ->onDelete('cascade');*/
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
};
