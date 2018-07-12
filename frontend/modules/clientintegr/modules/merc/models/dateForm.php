<?php
namespace frontend\modules\clientintegr\modules\merc\models;


use yii\base\Model;

class dateForm extends Model
{
    public $first_date;
    public $second_date;

    public function rules()
    {
        return [
            [['first_date'], 'required'],
            [['first_date', 'second_date'], 'datetime'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'first_date' => 'Начальная дата в интервале, либо единичная дата',
            'second_date' => 'Конечная дата в интервале',
        ];
    }
}