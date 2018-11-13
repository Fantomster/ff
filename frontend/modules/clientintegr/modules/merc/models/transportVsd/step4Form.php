<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.07.2018
 * Time: 18:36
 */

namespace frontend\modules\clientintegr\modules\merc\models\transportVsd;

use yii\base\Model;

class step4Form extends Model
{
    public $type = 1;
    public $type_name = 'Автомобильный';
    public $car_number;
    public $trailer_number;
    public $container_number;
    public $storage_type;
    public $confirm = false;

    public function rules()
    {
        return [
            [['type', 'car_number', 'trailer_number', 'container_number', 'storage_type'], 'required'],
            [['type'],'integer'],
            [['car_number', 'trailer_number', 'container_number', 'storage_type', 'type_name'], 'string'],
            [['confirm'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type_name' => 'Вид транспорта',
            'car_number' => 'Номер авто',
            'trailer_number' => 'Номер полуприцепа',
            'container_number' => 'Номер контейнера',
            'storage_type' => 'Способ хранения'
        ];
    }
}