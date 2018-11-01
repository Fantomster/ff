<?php

namespace api_web\modules\integration\classes\documents;

use api_web\modules\integration\interfaces\DocumentInterface;

class OrderContentEmail extends OrderContent implements DocumentInterface
{
    /**
     * Порлучение данных из модели
     *
     * @return mixed
     */
    public function prepare()
    {
        if (empty($this->attributes)) {
            return [];
        }
        $return = parent::prepare();
        return $return;
    }
}