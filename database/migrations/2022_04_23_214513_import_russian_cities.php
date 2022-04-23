<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ImportRussianCities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        try {
            $file = __DIR__.'/source/russia';
            if(!file_exists($file)){
                throw new \Exception('Файл-исходник отсутствует');
            }
            $sourceCityList = json_decode(file_get_contents($file), true);
            if(empty($sourceCityList) || !is_array($sourceCityList)){
                throw new \Exception('Некорректные данные для импорта');
            }

            foreach ($sourceCityList as $cityItem) {
                $citySqlData = ['name' => $cityItem['city']];
                DB::table('cities')->insert($citySqlData);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo 'Ошибка импорта городов - '.$e->getMessage();
            throw $e;
        }
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
}
