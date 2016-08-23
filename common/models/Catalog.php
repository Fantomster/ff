<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "catalog".
 *
 * @property integer $id
 * @property string $name
 * @property integer $org_supp_id
 * @property integer $type
 * @property string $create_datetime
 */
class Catalog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'org_supp_id', 'type'], 'required'],
            [['org_supp_id', 'type'], 'integer'],
            [['create_datetime'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'org_supp_id' => 'Org Supp ID',
            'type' => 'Type',
            'create_datetime' => 'Create Datetime',
        ];
    }
}
