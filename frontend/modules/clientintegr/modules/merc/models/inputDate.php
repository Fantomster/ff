<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 13.07.2018
 * Time: 14:40
 */

namespace frontend\modules\clientintegr\modules\merc\models;


class inputDate extends dateForm
{
    public function rules()
    {
        return [
            [['first_date'], 'required'],
            [['first_date', 'second_date'], 'date', 'format' => 'php:d.m.Y'],
            [['first_date'], 'checkInterval']
        ];
    }
}