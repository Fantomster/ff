<?php
use yii\base\Model;
namespace common\models\forms;
class ServiceDesk extends \yii\base\Model
{
    public $region;
    public $fio;
    public $phone;
    public $body;
    public $created_at;
    
    public function rules()
    {
        return [
            [['region'], 'string'],
            [['region'], 'required'],
            [['fio'], 'string'],
            [['phone'], 'string'],
            [['body'], 'string'],
            [['body'], 'required'],
            [['created_at'], 'safe'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'region' => 'Регион обращения',
            'fio' => 'ФИО',
            'phone' => 'Контактный телефон',
            'body' => 'Сообщение',
        ];
    }
}

