<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.07.2018
 * Time: 18:36
 */

namespace frontend\modules\clientintegr\modules\merc\models\transportVsd;

use yii\base\Model;

class step2Form extends Model
{
    public $recipient;
    public $hc;
    public $isTTN;
    public $seriesTTN;
    public $numberTTN;
    public $dateTTN;
    public $typeTTN;
    public $hc_name;

    public function rules()
    {
        return [
            [['recipient', 'hc', 'isTTN'], 'required'],
            [['numberTTN', 'dateTTN', 'typeTTN'], 'required', 'on' => 'isTTN'],
            [['isTTN'],'integer'],
            [['resipient', 'hc', 'seriesTTN', 'numberTTN', 'typeTTN', 'hc_name'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'recipient' => 'Предприятие-получатель',
            'hc_name' => 'Фирма-получатель',
            'isTTN' => 'Наличие TTN',
            'seriesTTN' => 'Серия ТТН',
            'numberTTN' => 'Номер ТТН',
            'dateTTN' => 'Дата ТТН',
            'typeTTN' => 'Тип ТТН'
        ];
    }
}