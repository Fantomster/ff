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
            'priority' => Yii::t('app', 'common.models.forms.service_desk.priority', ['ru'=>'Приоритет']),
            'region' => Yii::t('app', 'common.models.forms.service_desk.region', ['ru'=>'Регион клиента']),
            'fio' => Yii::t('app', 'common.models.forms.service_desk.clients_fio', ['ru'=>'ФИО клиента']),
            'phone' => Yii::t('app', 'common.models.forms.service_desk.contact_phone', ['ru'=>'Контактный телефон']),
            'body' => Yii::t('app', 'common.models.forms.service_desk.message', ['ru'=>'Сообщение']),
        ];
    }
}

