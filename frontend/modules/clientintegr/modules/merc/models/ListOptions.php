<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 14.05.2018
 * Time: 16:06
 */

namespace frontend\modules\clientintegr\modules\merc\models;


class ListOptions
{
    public $count;
    public $offset;

    public function rules()
    {
        return [
            [['count', 'offset'], 'integer'],
        ];
    }
}