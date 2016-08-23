<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "relation_supp_rest".
 *
 * @property integer $id
 * @property integer $rest_org_id
 * @property integer $sup_org_id
 * @property integer $cat_id
 */
class RelationSuppRest extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'relation_supp_rest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rest_org_id', 'sup_org_id', 'cat_id'], 'required'],
            [['rest_org_id', 'sup_org_id', 'cat_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rest_org_id' => 'Rest Org ID',
            'sup_org_id' => 'Sup Org ID',
            'cat_id' => 'Cat ID',
        ];
    }
}
