<?php

namespace common\models\rbac;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name
 * @property string $user_id
 * @property string $created_at
 * @property int    $organization_id
 * @property int    $id
 */
class AuthAssignment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth_assignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_name', 'user_id', 'organization_id'], 'required'],
            [['created_at'], 'safe'],
            [['organization_id', 'user_id'], 'integer'],
            [['item_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'item_name'       => 'Item Name',
            'user_id'         => 'User ID',
            'created_at'      => 'Created At',
            'organization_id' => 'Organization ID',
            'id'              => 'ID',
        ];
    }
}
