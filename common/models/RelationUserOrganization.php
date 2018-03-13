<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use common\behaviors\UploadBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "relation_supp_rest".
 *
 * @property integer $id
 * @property integer $manager_id
 * @property integer $leader_id
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


    public function getOrganization(){
        return $this->hasOne(Organization::className(), ['id'=>'organization_id']);
    }


    public function getUser(){
        return $this->hasOne(User::className(), ['id'=>'user_id']);
    }


    public function checkRelationExisting($user):bool
    {
        $rel = RelationUserOrganization::findOne(['user_id'=>$user->id]);
        if($rel){
            return true;
        }
        return false;
    }
}
