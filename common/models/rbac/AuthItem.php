<?php

namespace common\models\rbac;

/**
 * This is the model class for table "auth_item".
 *
 * @property int      $id
 * @property string   $name
 * @property int      $type
 * @property string   $description
 * @property string   $rule_name
 * @property resource $data
 * @property string   $created_at
 * @property string   $updated_at
 */
class AuthItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type'], 'integer'],
            [['description', 'data'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'rule_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'name'        => 'Name',
            'type'        => 'Type',
            'description' => 'Description',
            'rule_name'   => 'Rule Name',
            'data'        => 'Data',
            'created_at'  => 'Created At',
            'updated_at'  => 'Updated At',
        ];
    }
}
