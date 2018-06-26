<?php

namespace common\models;

use Yii;
use common\behaviors\UploadBehavior;

/**
 * This is the model class for table "order_attachment".
 *
 * @property int $id
 * @property int $order_id
 * @property string $file
 * @property string $created_at
 *
 * @property Order $order
 */
class OrderAttachment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'file'], 'required'],
            [['order_id'], 'integer'],
            [['created_at'], 'safe'],
            [['file'], 'file', 'extensions' => 'jpg, jpeg, png, pdf', 'maxSize' => 52428800, 'tooBig' => Yii::t('app', 'common.models.order_attachment.file', ['ru'=>'Размер файла не должен превышать 50 Мб'])],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return ArrayHelper::merge(parent::behaviors(), [
                    [
                        'class' => UploadBehavior::className(),
                        'attribute' => 'file',
                        'scenarios' => ['default'],
                        'path' => '@app/web/upload/temp/',
                        'url' => '/upload/temp/',
                    ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'file' => 'File',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}
