<?php
namespace frontend\modules\clientintegr\modules\merc\models;


use yii\base\Model;

abstract class dateForm extends Model
{
    public $first_date;
    public $second_date;

    public function rules()
    {
        return [
            [['first_date'], 'required'],
            [['first_date', 'second_date'], 'datetime', 'format' => 'php:d.m.Y H:i'],
            [['first_date'], 'checkInterval']
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

    public function checkInterval()
    {
        if(!empty($this->second_date))
        {
            if(strtotime($this->first_date) > strtotime($this->second_date))
            {
                $this->addError('second_date', 'Конечная дата в интервале должна быть больше начальной');
            }
        }
    }
}