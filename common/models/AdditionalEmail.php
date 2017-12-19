<?php

namespace common\models;

use common\models\notifications\EmailNotification;
use common\models\notifications\SmsNotification;
use Yii;

/**
 * This is the model class for table "additional_email".
 *
 * @property integer $id
 * @property string $email
 * @property integer $organization_id
 * @property integer $order_created
 * @property integer $order_canceled
 * @property integer $order_changed
 * @property integer $order_processing
 * @property integer $order_done
 * @property integer $request_accept
 *
 * @property Organization $organization
 */
class AdditionalEmail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%additional_email}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'organization_id'], 'required'],
            [['organization_id', 'order_created', 'order_canceled', 'order_changed', 'order_processing', 'order_done', 'request_accept'], 'integer'],
            [['email'], 'string', 'max' => 255],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'organization_id' => Yii::t('app', 'common.models.additional_email.org', ['ru'=>'Организация']),
            'order_created' => Yii::t('app', 'common.models.additional_email.creation', ['ru'=>'Создание']),
            'order_canceled' => Yii::t('app', 'common.models.additional_email.cancel', ['ru'=>'Отмена']),
            'order_changed' => Yii::t('app', 'common.models.additional_email.changing', ['ru'=>'Изменение']),
            'order_processing' => Yii::t('app', 'common.models.additional_email.working', ['ru'=>'В работе']),
            'order_done' => Yii::t('app', 'common.models.additional_email.ready', ['ru'=>'Выполнен']),
            'request_accept' => Yii::t('app', 'common.models.additional_email.ready.accepted_two', ['ru'=>'Принятие заявки']),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

    /**
     * @return EmailNotification
     */
    public function getEmailNotification() {
        $model = new EmailNotification();
        $model->order_created = $this->order_created;
        $model->order_canceled = $this->order_canceled;
        $model->order_changed = $this->order_changed;
        $model->order_processing = $this->order_processing;
        $model->order_done = $this->order_done;
        return $model;
    }

    /**
     * @return SmsNotification
     */
    public function getSmsNotification() {
        return new SmsNotification();
    }

    /**
     * @return \amnah\yii2\user\models\Profile
     */
    public function getProfile() {
        return new \amnah\yii2\user\models\Profile();
    }
}
