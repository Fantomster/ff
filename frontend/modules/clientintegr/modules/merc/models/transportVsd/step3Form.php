<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.2018
 * Time: 16:13
 */

namespace frontend\modules\clientintegr\modules\merc\models\transportVsd;


use yii\base\Model;

class step3Form extends Model
{
    public $recipient;
    public $hc;
    public $hc_inn;
    public $isTTN;
    public $seriesTTN;
    public $numberTTN;
    public $dateTTN;
    public $typeTTN;
    public $hc_name;

    public static $ttn_types = [
        '1' => 'Товарно-транспортная накладная',
        '2' => 'Коносамент',
        '3' => 'CMR',
        '4' => 'Авианакладная',
        '5' => 'Транспортная накладная ',
    ];

    public function rules()
    {
        return [
            [['recipient', 'hc', 'isTTN'], 'required'],
            [['recipient', 'hc', 'numberTTN', 'dateTTN', 'typeTTN'], 'required', 'on' => 'isTTN'],
            [['isTTN', 'hc_inn'],'integer'],
            [['recipient', 'hc', 'seriesTTN', 'numberTTN', 'typeTTN', 'hc_name', 'dateTTN'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'recipient' => 'Предприятие-получатель',
            'hc_name' => 'Фирма-получатель',
            'hc_inn' => 'ИНН Фирмы-получателя',
            'isTTN' => 'Наличие TTN',
            'seriesTTN' => 'Серия ТТН',
            'numberTTN' => 'Номер ТТН',
            'dateTTN' => 'Дата ТТН',
            'typeTTN' => 'Тип ТТН'
        ];
    }



}