<?php

namespace common\models;

use Yii;
use common\models\notifications\EmailFails;

/**
 * This is the model class for table "email_queue".
 *
 * @property int        $id            Идентификатор записи в таблице
 * @property string     $to            Е-мэйл получателя электронного письма (кому)
 * @property string     $from          Е-мэйл отправителя электронного письма (от кого)
 * @property string     $subject       Заголовок электронного письма
 * @property string     $body          Содержание электронного письма
 * @property int        $order_id      Идентификатор заказа, по которому отправлено письмо
 * @property string     $message_id    Идентификатор очереди на Amazon
 * @property int        $status        Статус электронного письма (0 - новое, 1 - отправлено, 2 - получено, 3 -
 *           отправить не удалось, неудача)
 * @property int        $email_fail_id Идентификатор неудачи при отправке электронного письма
 * @property string     $created_at    Дата и время создания записи в таблице
 * @property string     $updated_at    Дата и время последнего изменения записи в таблице
 *
 * @property EmailFails $emailFail
 * @property Order      $order
 */
class EmailQueue extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_SENDING = 1;
    const STATUS_CONFIRMED = 2;
    const STATUS_FAILED = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%email_queue}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['to', 'from'], 'required'],
            [['body'], 'string'],
            [['order_id', 'status', 'email_fail_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['to', 'from', 'subject', 'message_id'], 'string', 'max' => 255],
            [['message_id'], 'unique'],
            [['email_fail_id'], 'exist', 'skipOnError' => true, 'targetClass' => EmailFails::className(), 'targetAttribute' => ['email_fail_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('app', 'ID'),
            'to'            => Yii::t('app', 'To'),
            'from'          => Yii::t('app', 'From'),
            'subject'       => Yii::t('app', 'Subject'),
            'body'          => Yii::t('app', 'Body'),
            'order_id'      => Yii::t('app', 'Order ID'),
            'message_id'    => Yii::t('app', 'Message ID'),
            'status'        => Yii::t('app', 'Status'),
            'email_fail_id' => Yii::t('app', 'Email Fail ID'),
            'created_at'    => Yii::t('app', 'Created At'),
            'updated_at'    => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailFail()
    {
        return $this->hasOne(EmailFails::className(), ['id' => 'email_fail_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        switch ($this->status) {
            case self::STATUS_NEW:
                return Yii::t('app', 'common.models.email_queue.status_new');
            case self::STATUS_SENDING:
                return Yii::t('app', 'common.models.email_queue.status_sending');
            case self::STATUS_CONFIRMED:
                return Yii::t('app', 'common.models.email_queue.status_confirmed');
            case self::STATUS_FAILED:
                return Yii::t('app', 'common.models.email_queue.status_failed');
        }
    }
}
