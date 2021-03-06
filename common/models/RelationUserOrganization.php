<?php

namespace common\models;

use common\behaviors\LogDeletedBehavior;

/**
 * This is the model class for table "relation_user_organization".
 *
 * @property int          $id              Идентификатор записи в таблице
 * @property int          $user_id         Идентификатор пользователя
 * @property int          $organization_id Идентификатор типа этой организации
 * @property int          $role_id         Идентификатор роли
 * @property int          $is_active       Флажок состояния активности зависимости пользователя, организации и роли
 *
 * @property User         $user
 * @property Organization $organization
 * @property Role         $role
 */
class RelationUserOrganization extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%relation_user_organization}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                "class" => LogDeletedBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'organization_id', 'role_id'], 'integer'],
            [['user_id', 'organization_id'], 'unique', 'targetAttribute' => ['user_id', 'organization_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'user_id'         => 'User ID',
            'organization_id' => 'Organization ID',
            'role_id'         => 'Role ID',
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
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Role::className(), ['id' => 'role_id']);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function checkRelationExisting(User $user): bool
    {
        $rel = self::findAll(['user_id' => $user->id]);
        if (count($rel) > 1) {
            return true;
        }
        return false;
    }

    /**
     * @param $user_id
     * @param $organization_id
     * @return bool
     */
    public static function relationExists($user_id, $organization_id)
    {
        return self::find()->where(['user_id' => $user_id, 'organization_id' => $organization_id])->exists();
    }

    /**
     * @param int $organizationID
     * @param int $userID
     * @return int|null|string
     */
    public static function getRelationRole(int $organizationID, int $userID)
    {
        $user = User::findIdentity($userID);
        if (in_array($user->role_id, [Role::ROLE_ADMIN, Role::ROLE_FKEEPER_MANAGER, Role::ROLE_FRANCHISEE_OWNER, Role::ROLE_FRANCHISEE_OPERATOR])) {
            return $user->role_id;
        }

        $rel = self::findOne(['user_id' => $userID, 'organization_id' => $organizationID]);
        return $rel->role_id ?? null;
    }

    /**
     * @param bool  $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {

            $organization = $this->organization;
            /**
             * Уведомления по Email
             */
            $emailNotification = new notifications\EmailNotification();
            $emailNotification->user_id = $this->user_id;
            $emailNotification->rel_user_org_id = $this->id;
            $emailNotification->orders = true;
            $emailNotification->requests = true;
            $emailNotification->changes = true;
            $emailNotification->invites = true;
            $emailNotification->order_done = isset($organization) ? (($organization->type_id == Organization::TYPE_SUPPLIER) ? 0 : 1) : 0;
            $emailNotification->save();

            /**
             * Уведомления по СМС
             */
            $smsNotification = notifications\SmsNotification::findOne(['user_id' => $this->id]);
            if (empty($smsNotification)) {
                $smsNotification = new notifications\SmsNotification();
            }
            $smsNotification->user_id = $this->user_id;
            $smsNotification->rel_user_org_id = $this->id;
            $smsNotification->orders = true;
            $smsNotification->requests = true;
            $smsNotification->changes = true;
            $smsNotification->invites = true;

            $smsNotification->save();
            if ($this->role_id == Role::ROLE_SUPPLIER_MANAGER) {
                $userId = $this->user_id;
                $organizationId = $this->organization_id;
                $clients = \common\models\RelationSuppRest::findAll(['supp_org_id' => $organizationId]);
                if ($clients) {
                    foreach ($clients as $client) {
                        $clientId = $client->rest_org_id;
                        $managerAssociate = new ManagerAssociate();
                        $managerAssociate->manager_id = $userId;
                        $managerAssociate->organization_id = $clientId;
                        $managerAssociate->save();
                    }
                }
            }
        }

        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

}
