<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.07.2018
 * Time: 18:36
 */

namespace frontend\modules\clientintegr\modules\merc\models;


use api\common\models\merc\MercStockEntry;

class TransportVsd extends MercStockEntry
{
    public $select_amount;

    public function rules()
    {
        return [
            [['product_name', 'select_amount'], 'required'],
            [['amount'], 'number'],
            [['product_name'], 'string'],
        ];
    }
}