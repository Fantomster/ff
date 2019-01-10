<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "operator_vendor_comment".
 *
 * @property int          $vendor_id  Идентификатор организации-поставщика
 * @property string       $comment    Комментарий сотрудников Mixcarta о поставщике
 * @property string       $created_at Дата и время создания записи в таблице
 * @property string       $updated_at Дата и время последнего изменения записи в таблице
 * @property Organization $vendor
 */
class OperatorVendorComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'operator_vendor_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['comment'], 'string', 'max' => 300],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'vendor_id'  => Yii::t('app', 'id поставщика'),
            'comment'    => Yii::t('app', 'комментарий'),
            'created_at' => Yii::t('app', 'Дата создания записи'),
            'updated_at' => Yii::t('app', 'Дата последнего изменения записи'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(Organization::className(), ['id' => 'vendor_id']);
    }
}
