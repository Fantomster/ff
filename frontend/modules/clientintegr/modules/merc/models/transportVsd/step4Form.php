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
    const INPUT_MODE = 1;
    const CONFIRM_MODE = 2;

    public $type = 1;
    public $type_name = 'Автомобильный';
    public $car_number;
    public $trailer_number;
    public $container_number;
    public $storage_type;
    public $conditions;
    public $conditionsDescription;
    public $mode = self::INPUT_MODE;

    public function rules()
    {
        return [
            [['type', 'car_number', 'storage_type'], 'required'],
            [['type'],'integer'],
            [['car_number', 'trailer_number', 'container_number', 'storage_type', 'type_name'], 'string'],
            [['conditions'], 'checkConditions'],
            [['conditions', 'mode', 'conditionsDescription', 'trailer_number', 'container_number'], 'safe']
        ];
    }

    public function checkConditions($attribute, $params)
    {
        if($this->mode == self::CONFIRM_MODE) {
            $count = [];
            foreach ($this->conditions as $key => $cond) {
                if ($cond != "0") {
                    $count[$key] = 1;
                }
            }

            if (count($count) != count($this->conditions)) {
                $this->addError($attribute, "Должны быть выбраны условия регионализации");
            }
        }
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