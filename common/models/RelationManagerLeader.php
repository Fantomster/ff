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
class RelationManagerLeader extends \yii\db\ActiveRecord {


    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'relation_manager_leader';
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
            [['manager_id', 'leader_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'manager_id' => 'Manager ID',
            'leader_id' => 'Leader ID',
        ];
    }
}
