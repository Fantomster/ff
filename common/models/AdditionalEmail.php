<?php

namespace common\models;

use Yii;
use common\components\mailer\Mailer;
use common\models\notifications\{EmailNotification, SmsNotification};


/**
 * This is the model class for table "additional_email".
 *
 * @property int          $id                Идентификатор записи в таблице
 * @property string       $email             Е-мэйл
 * @property int          $organization_id   Идентификатор организации
 * @property int          $order_created     Показатель состояния необходимости отправлять оповещения о создании
 *           заказов (0 - не отправлять, 1 - отправлять)
 * @property int          $order_canceled    Показатель состояния необходимости отправлять оповещения об отмене заказов
 *           (0 - не отправлять, 1 - отправлять)
 * @property int          $order_changed     Показатель состояния необходимости отправлять оповещения об изменении
 *           заказов (0 - не отправлять, 1 - отправлять)
 * @property int          $order_processing  Показатель состояния необходимости отправлять оповещения о взятии заказов
 *           в работу (0 - не отправлять, 1 - отправлять)
 * @property int          $order_done        Показатель состояния необходимости отправлять оповещения о завершении
 *           заказов (0 - не отправлять, 1 - отправлять)
 * @property int          $request_accept    Показатель состояния согласия получения оповещений на дополнительный
 *           е-мэйл
 * @property int          $merc_vsd          Показатель состояния необходимости отправлять оповещения о непогашенных
 *           ВСД (0 - не отправлять, 1 - отправлять)
 * @property int          $confirmed         Показатель статуса подтверждения дополнительного е-мэйла (0 - не
 *           подтверждён, 1 - подтверждён)
 * @property string       $token             Хэш данного е-мэйла
 * @property int          $merc_stock_expiry Уведомление о проблемной продукции в меркурии
 * @property Organization $organization
 */
class AdditionalEmail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%additional_email}}';
    }

    /**
     * {@inheritdoc}
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
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::class, 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'email'             => 'Email',
            'organization_id'   => Yii::t('app', 'common.models.additional_email.org', ['ru' => 'Организация']),
            'order_created'     => Yii::t('app', 'common.models.additional_email.creation', ['ru' => 'Создание']),
            'order_canceled'    => Yii::t('app', 'common.models.additional_email.cancel', ['ru' => 'Отмена']),
            'order_changed'     => Yii::t('app', 'common.models.additional_email.changing', ['ru' => 'Изменение']),
            'order_processing'  => Yii::t('app', 'common.models.additional_email.working', ['ru' => 'В работе']),
            'order_done'        => Yii::t('app', 'common.models.additional_email.ready', ['ru' => 'Выполнен']),
            'request_accept'    => Yii::t('app', 'common.models.additional_email.ready.accepted_two', ['ru' => 'Принятие заявки']),
            'merc_vsd'          => Yii::t('app', 'common.models.additional_email.vsd_short', ['ru' => 'ВСД']),
            'merc_stock_expiry' => Yii::t('app', 'frontend.views.settings.stock_expiry_notification', ['ru' => 'Рассылки о проблемной продукции']),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    /**
     * @param null $org_id
     * @return EmailNotification
     */
    public function getEmailNotification($org_id = null)
    {
        if ($org_id === null || $org_id == $this->organization_id) {
            $model = new EmailNotification();
            $model->setAttributes($this->attributes);
            return $model;
        }

        return EmailNotification::emptyInstance();
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
     *
     * @return int
     */
    public function sendConfirmationEmail()
    {
        /** @var Mailer $mailer */
        $mailer = Yii::$app->mailer;
        $this->token = md5($this->email);
        $this->save();
        $token = $this->token;
        $subject = Yii::t('app', 'common.models.additional_mail_subject', ['ru' => "Дополнительная почта для MixCart"]);
        $result = $mailer->compose('confirmAdditionalEmail', compact("token"))
            ->setTo($this->email)
            ->setSubject($subject)
            ->send();

        return $result;
    }
}
