<?php

namespace common\models;


/**
 * This is the model class for table "relation_user_organization".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $leader_id
 * @property integer $organization_id
 * @property integer $role_id
 */
class RelationUserOrganization extends \yii\db\ActiveRecord {


    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'relation_user_organization';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'organization_id', 'role_id'], 'integer'],
            [['user_id', 'organization_id'], 'unique', 'targetAttribute' => ['user_id', 'organization_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'organization_id' => 'Organization ID',
            'role_id' => 'Role ID',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization(){
        return $this->hasOne(Organization::className(), ['id'=>'organization_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser(){
        return $this->hasOne(User::className(), ['id'=>'user_id']);
    }


    public function checkRelationExisting(User $user):bool
    {
        $rel = self::findAll(['user_id'=>$user->id]);
        if(count($rel)>1){
            return true;
        }
        return false;
    }


    public function getRelationRole(int $organizationID, int $userID):int
    {
        $rel = self::findOne(['user_id'=>$userID, 'organization_id'=>$organizationID]);
        return $rel->role_id ?? null;
    }


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
            if(empty($smsNotification)) {
                $smsNotification = new notifications\SmsNotification();
            }
            $smsNotification->user_id = $this->user_id;
            $smsNotification->rel_user_org_id = $this->id;
            $smsNotification->orders = true;
            $smsNotification->requests = true;
            $smsNotification->changes = true;
            $smsNotification->invites = true;

            $smsNotification->save();
            if($this->role_id == Role::ROLE_SUPPLIER_MANAGER){
                $userId = $this->id;
                $organizationId = $this->organization_id;
                $clients = \common\models\RelationSuppRest::findAll(['supp_org_id' => $organizationId]);
                if ($clients){
                    foreach ($clients as $client){
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
