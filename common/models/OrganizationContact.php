<?php

namespace common\models;

use api_web\exceptions\ValidationException;
use common\components\mailer\Mailer;
use common\models\notifications\{EmailNotification, SmsNotification};
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "{{%organization_contact}}".
 *
 * @property int                               $id
 * @property int                               $organization_id ID организации
 * @property int                               $type_id         Тип контакта Email или Телефон
 * @property string                            $contact         Телефон или Email
 * @property string                            $created_at
 * @property string                            $updated_at
 * @property string                            $email
 * @property \stdClass                         $profile
 * @property Organization                      $organization
 * @property OrganizationContactNotification[] $organizationContactNotifications
 */
class OrganizationContact extends ActiveRecord
{

    const TYPE_EMAIL = 1;
    const TYPE_PHONE = 2;

    const TYPE_CLASS = [
        self::TYPE_EMAIL => EmailNotification::class,
        self::TYPE_PHONE => SmsNotification::class
    ];

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_contact}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'type_id', 'contact'], 'required'],
            [['organization_id', 'type_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['contact'], 'string', 'max' => 50],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::class, 'targetAttribute' => ['organization_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'organization_id' => 'ID организации',
            'type_id'         => 'Тип контакта Email или Телефон',
            'contact'         => 'Телефон или Email',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
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
     * @return \yii\db\ActiveQuery
     */
    public function getOrganizationContactNotifications()
    {
        return $this->hasMany(OrganizationContactNotification::class, ['organization_contact_id' => 'id']);
    }

    /**
     * Установка уведомлений для текущего контакта
     *
     * @param       $client_id
     * @param array $rules
     * @throws ValidationException
     */
    public function setNotifications($client_id, $rules = []): void
    {
        //Поиск или создание модели
        $model = OrganizationContactNotification::findOne([
            'organization_contact_id' => $this->id,
            'client_id'               => $client_id
        ]);
        if (!$model) {
            $model = new OrganizationContactNotification([
                'organization_contact_id' => $this->id,
                'client_id'               => $client_id
            ]);
        }
        //Заполняем атрибуты уведомлений
        $attributes = $model->getRulesAttributes();
        foreach ($attributes as $attribute => $value) {
            $model->setAttribute($attribute, $rules[$attribute] ?? $value ?? 0);
        }
        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }
    }

    /**
     * @param $org_id
     * @return EmailNotification
     */
    public function getEmailNotification($org_id)
    {
        return $this->createInstanceNotification(self::TYPE_EMAIL, $org_id);
    }

    /**
     * @param $org_id
     * @return SmsNotification
     */
    public function getSmsNotification($org_id)
    {
        return $this->createInstanceNotification(self::TYPE_PHONE, $org_id);
    }

    /**
     * @param $type
     * @param $client_id
     * @return EmailNotification|SmsNotification
     */
    private function createInstanceNotification($type, $client_id)
    {
        /** @var EmailNotification|SmsNotification $class */
        $class = self::TYPE_CLASS[$type] ?? null;
        if (!is_null($class) AND $client_id != $this->organization_id) {
            $return = $class::emptyInstance();
            /** @var OrganizationContactNotification $ocn */
            $ocn = $this->getOrganizationContactNotifications()->andWhere(['client_id' => $client_id])->one();
            if (!empty($ocn)) {
                /** @var ActiveRecord $model */
                $model = new $class();
                $model->setAttributes($ocn->getRulesAttributes());
                $return = $model;
            }
        } else {
            $return = current(self::TYPE_CLASS)::emptyInstance();
        }
        return $return;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        if ($this->type_id == self::TYPE_EMAIL) {
            return $this->contact;
        }
        return null;
    }

    /**
     * @return \stdClass
     */
    public function getProfile()
    {
        $object = new \stdClass();
        if ($this->type_id == self::TYPE_PHONE) {
            $object->phone = $this->contact;
        } else {
            $object->phone = null;
        }
        return $object;
    }

    /**
     * @return bool
     */
    public function sendTestMessage()
    {
        try {
            $message = \Yii::t('app', 'organization_contact.test_message', ['ru' => 'MixCart шлет привет!']);
            if ($this->type_id == self::TYPE_EMAIL) {
                /** @var \common\components\mailer\Message $s */
                $s = new \common\components\mailer\Message();
                $s->setFrom('no-reply@mixcart.ru');
                $s->setTo($this->contact);
                $s->setSubject($message);
                $s->setBody($message);
                /** @var Mailer $mailer */
                \Yii::$app->mailer->send($s);
            }
            if ($this->type_id == self::TYPE_PHONE) {
                \Yii::$app->get('sms')->send($message, $this->contact);
            }
            return true;
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * Получить тип контакта
     *
     * @param $contact
     * @return int
     * @throws BadRequestHttpException
     */
    public static function checkType($contact)
    {
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            return self::TYPE_EMAIL;
        }
        if (self::validatePhoneNumber($contact)) {
            return self::TYPE_PHONE;
        }
        throw new BadRequestHttpException('organization_contact.type_check_error');
    }

    /**
     * Валидация номера телефона
     *
     * @param $number
     * @return bool
     */
    public static function validatePhoneNumber($number)
    {
        $formats = [
            '+###########',
            '###########',
            '+#(###)#######',
            '#(###)#######',
            '##########'
        ];

        return in_array(
            trim(preg_replace('/[0-9]/', '#', $number)),
            $formats
        );
    }
}
