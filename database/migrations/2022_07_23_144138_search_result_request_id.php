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
        Schema::table('search_results', function (Blueprint $table) {
            $table
                ->foreignId('search_request_id')
                ->constrained()
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_results', function (Blueprint $table) {
            $table->dropForeign('search_request_id');
            $table->dropColumn('search_request_id');
        });
    }
};
