<?php
//use yii\base\Model;
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
            'priority' => Yii::t('app', 'Приоритет'),
            'region' => Yii::t('app', 'Регион клиента'),
            'fio' => Yii::t('app', 'ФИО клиента'),
            'phone' => Yii::t('app', 'Контактный телефон'),
            'body' => Yii::t('app', 'Сообщение'),
        ];
    }
}

