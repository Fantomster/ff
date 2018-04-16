<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "cart".
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Organization $organization
 * @property User $user
 * @property CartContent[] $cartContents
 */
class Cart extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cart';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['organization_id', 'user_id'], 'required'],
            [['organization_id', 'user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id'], 'unique'],
            [['organization_id', 'user_id'], 'unique', 'targetAttribute' => ['organization_id', 'user_id']],
            [['organization_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['organization_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'organization_id' => Yii::t('app', 'Organization ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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
     * @return array
     */
    public function getVendors()
    {
        $vendors = $this->getCartContents()->select('vendor_id as id')->distinct()->all();
        $result = [];
        foreach($vendors as $vendor) {
            $result[] = Organization::findOne($vendor['id']);
        }
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCartContents()
    {
        return $this->hasMany(CartContent::className(), ['cart_id' => 'id']);
    }
}
