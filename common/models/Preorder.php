<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%preorder}}".
 *
 * @property int               $id              Идентификатор записи в таблице
 * @property int               $organization_id id организации, которая сделала предзаказ
 * @property int               $user_id         id пользователя, который создал предзаказ
 * @property int               $is_active       активен ли данный предзаказ
 * @property string            $created_at      Дата и время создания записи в таблице
 * @property string            $updated_at      Дата и время последнего изменения записи в таблице
 * @property Order[]           $orders
 * @property Organization      $organization
 * @property User              $user
 * @property PreorderContent[] $preorderContents
 */
class Preorder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%preorder}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => \gmdate('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['organization_id', 'user_id', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'organization_id' => 'Organization ID',
            'user_id'         => 'User ID',
            'is_active'       => 'Is Active',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['preorder_id' => 'id']);
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
    public function getPreorderContents()
    {
        return $this->hasMany(PreorderContent::className(), ['preorder_id' => 'id']);
    }
}
