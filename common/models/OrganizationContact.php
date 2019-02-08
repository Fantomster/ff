<?php

namespace common\models;

use common\models\notifications\{EmailNotification, SmsNotification};
use yii\db\ActiveRecord;

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
}
