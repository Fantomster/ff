<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.05.2018
 * Time: 12:03
 */

namespace frontend\modules\clientintegr\modules\merc\models;


use yii\base\Model;

class rejectedForm extends Model {

    const INPUT_MODE = 1;
    const CONFIRM_MODE = 2;

    public $volume;
    public $reason;
    public $description;
    public $uuid;
    public $decision;
    public $conditions;
    public $mode = self::INPUT_MODE;

    public function rules()
    {
        return [
            [['volume', 'reason'], 'required'],
            [['volume'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],
            ['volume', 'filter', 'filter' => function ($value) {
                $newValue = 0 + str_replace(',', '.', $value);
                return $newValue;
            }],
            [['reason', 'description'], 'string', 'max' => 255],
            [['uuid','conditions', 'mode'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'volume' => 'Фактическое количество',
            'reason' => 'Причина составления акта несоответствия',
            'description' => 'Описание несоответствия',
        ];
    }

}