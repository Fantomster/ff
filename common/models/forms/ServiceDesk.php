<?php
use yii\base\Model;
namespace common\models\forms;
class ServiceDesk extends \yii\base\Model
{
    public $region;
    public $fio;
    public $phone;
    public $body;
    public $priority;
    public $created_at;
    
    public function rules()
    {
        return [
            [['region'], 'string'],
            [['fio'], 'string'],
            [['phone'], 'string'],
            [['priority'], 'integer'],
            [['body'], 'string'],
            [['body'], 'required'],
            [['created_at'], 'safe'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'priority' => 'Приоритет',
            'region' => 'Регион клиента',
            'fio' => 'ФИО клиента',
            'phone' => 'Контактный телефон',
            'body' => 'Сообщение',
        ];
    }
}

