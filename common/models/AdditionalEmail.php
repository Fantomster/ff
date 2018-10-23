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
 * @property integer $merc_vsd
* @property integer $merc_stock_expiry
 * @property integer $confirmed
 * @property string $token
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
            [['organization_id', 'order_created', 'order_canceled', 'order_changed', 'order_processing', 'order_done', 'request_accept', 'merc_vsd', 'confirmed', 'merc_stock_expiry'], 'integer'],
            [['email', 'token'], 'string', 'max' => 255],
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
            'organization_id' => Yii::t('app', 'common.models.additional_email.org', ['ru' => 'Организация']),
            'order_created' => Yii::t('app', 'common.models.additional_email.creation', ['ru' => 'Создание']),
            'order_canceled' => Yii::t('app', 'common.models.additional_email.cancel', ['ru' => 'Отмена']),
            'order_changed' => Yii::t('app', 'common.models.additional_email.changing', ['ru' => 'Изменение']),
            'order_processing' => Yii::t('app', 'common.models.additional_email.working', ['ru' => 'В работе']),
            'order_done' => Yii::t('app', 'common.models.additional_email.ready', ['ru' => 'Выполнен']),
            'request_accept' => Yii::t('app', 'common.models.additional_email.ready.accepted_two', ['ru' => 'Принятие заявки']),
            'merc_vsd' => Yii::t('app', 'common.models.additional_email.vsd_short', ['ru' => 'ВСД']),
            'merc_stock_expiry' =>  Yii::t('app', 'frontend.views.settings.stock_expiry_notification', ['ru'=>'Рассылки о проблемной продукции']),
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
     * @param null $org_id
     * @return EmailNotification
     */
    public function getEmailNotification($org_id = null)
    {
        if ($org_id === null || $org_id == $this->organization_id) {
            $model = new EmailNotification();
            $model->order_created = $this->order_created;
            $model->order_canceled = $this->order_canceled;
            $model->order_changed = $this->order_changed;
            $model->order_processing = $this->order_processing;
            $model->order_done = $this->order_done;
            return $model;
        }

        if (!empty($org_id) && $org_id !== $this->organization_id) {
            return EmailNotification::emptyInstance();
        }
    }

    /**
     * @return SmsNotification
     */
    public function getSmsNotification()
    {
        return SmsNotification::emptyInstance();
    }

    /**
     * @return \amnah\yii2\user\models\Profile
     */
    public function getProfile()
    {
        return new \amnah\yii2\user\models\Profile();
    }
    
    /**
     * Send confirmation mail
     * @param Organization $organization
     * @return int
     */
    public function sendConfirmationEmail() {
        /** @var Mailer $mailer */
        $mailer = Yii::$app->mailer;
        $this->token = md5($this->email);
        $this->save();
        $token = $this->token;
        $subject = Yii::t('app', 'common.models.additional_mail_subject', ['ru'=>"Дополнительная почта для MixCart"]);
        $result = $mailer->compose('confirmAdditionalEmail', compact("token"))
                ->setTo($this->email)
                ->setSubject($subject)
                ->send();

        return $result;
    }
}
