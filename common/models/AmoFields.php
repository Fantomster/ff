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
class AmoFields extends \yii\db\ActiveRecord {


    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'amo_fields';
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
            [['amo_field'], 'required'],
            [['amo_field'], 'string', 'max' => 255],
            [['responsible_user_id', 'pipeline_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'amo_field' => 'Значение поля из таблицы(напр. franch)',
            'responsible_user_id' => 'ID ответственного менеджера(responsible_user_id)',
            'pipeline_id' => 'ID воронки(pipeline_id)',
        ];
    }
}
