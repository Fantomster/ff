<?php

namespace api_web\modules\integration\interfaces;

interface DocumentInterface
{
    /**
     * Порлучение данных из модели
     * @return array
     */
    public function prepare();

    /**
     * Загрузка модели и получение данных
     *
     * @param $key
     * @return  $array
     */
    public static function prepareModel($key);

}