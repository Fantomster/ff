<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 13.07.2018
 * Time: 14:39
 */

namespace frontend\modules\clientintegr\modules\merc\models;

class expiryDate extends dateForm
{
    public $production_date;

    public function rules()
    {
        return array_merge(parent::rules(),
            [
                [['first_date'], 'checkProductionDate'],
                [['production_date'], 'safe']
            ]
        );
    }

    public function checkProductionDate()
    {
        $expiry_date = !empty($this->second_date) ? $this->second_date : $this->first_date;
        if (strtotime($this->production_date) > strtotime($expiry_date)) {
            $attribute = (!empty($this->second_date)) ? 'second_date' : 'first_date';
            $this->addError($attribute, 'Дата выработки должна быть меньше Срока годности');
        }
    }
}