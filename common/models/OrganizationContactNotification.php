<?php

namespace common\models;

use common\models\notifications\EmailNotification;
use common\models\notifications\SmsNotification;

/**
 * This is the model class for table "{{%organization_contact_notification}}".
 *
 * @property int                 $organization_contact_id  Связь с таблицей organization_contact
 * @property int                 $client_id                Связь с рестораном
 * @property int                 $order_created            Подписка на создание заказа
 * @property int                 $order_canceled           Подписка на отмену заказа
 * @property int                 $order_changed            Подписка на изменение заказа
 * @property int                 $order_done               Подписка на завершение заказа
 * @property string              $created_at
 * @property string              $updated_at
 * @property Organization        $client
 * @property OrganizationContact $organizationContact
 */
class OrganizationContactNotification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organization_contact_notification}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_contact_id', 'client_id'], 'required'],
            [['organization_contact_id', 'client_id', 'order_created', 'order_canceled', 'order_changed', 'order_done'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::class, 'targetAttribute' => ['client_id' => 'id']],
            [['organization_contact_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrganizationContact::class, 'targetAttribute' => ['organization_contact_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'organization_contact_id' => 'Связь с таблицей organization_contact',
            'client_id'               => 'Связь с рестораном',
            'order_created'           => 'Подписка на создание заказа',
            'order_canceled'          => 'Подписка на отмену заказа',
            'order_changed'           => 'Подписка на изменение заказа',
            'order_done'              => 'Подписка на завершение заказа',
            'created_at'              => 'Created At',
            'updated_at'              => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public function getRulesAttributes()
    {
        $prefix = 'order_';
        $return = [];
        foreach ($this->getAttributes() as $attribute => $item) {
            if (strpos($attribute, $prefix) === 0) {
                $return[$attribute] = $item;
            } else {
                continue;
            }
        }
        return $return;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Organization::class, ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganizationContact()
    {
        return $this->hasOne(OrganizationContact::class, ['id' => 'organization_contact_id']);
    }
}
