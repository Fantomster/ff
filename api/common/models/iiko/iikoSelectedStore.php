<?php

namespace api\common\models\iiko;

use Yii;

/**
 * This is the model class for table "iiko_selected_store".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $organization_id
 */
class iikoSelectedStore extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'iiko_selected_store';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'organization_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
        ];
    }


    public function getIikoStore()
    {
        return $this->hasOne(iikoStore::className(), ['id' => 'store_id']);
    }
}
