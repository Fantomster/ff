<?php

namespace api\common\models;

use Yii;

class VatData
{

    public static function getVatList(): array
    {
        return [
            '1'    => Yii::t('message', 'frontend.views.order.all', ['ru' => 'Все']),
            '0'    => 0,
            '1000' => 10,
            '1800' => 18,
            '2000' => 20
        ];
    }
}
