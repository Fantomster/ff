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

    public $volume;
    public $reason;
    public $description;
    public $uuid;
    public $decision;

    public function rules()
    {
        return [
            [['volume', 'reason', 'description'], 'required'],
            [['volume'], 'number'],
            [['reason', 'description'], 'string', 'max' => 255],
            [['uuid'], 'safe']
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