<?php

namespace common\models;

use Yii;
use common\behaviors\UploadBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "order_attachment".
 *
 * @property int $id
 * @property int $order_id
 * @property string $file
 * @property string $created_at
 *
 * @property Order $order
 * @property integer $size
 * @property string $url
 */
class OrderAttachment extends \yii\db\ActiveRecord
{
    
    public $resourceCategory = 'bill';
    
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
            [['file'], 'file', 'extensions' => 'gif, jpg, jpeg, png, bmp, pdf', 'maxSize' => 52428800, 'tooBig' => Yii::t('app', 'common.models.order_attachment.file', ['ru'=>'Размер файла не должен превышать 50 Мб'])],
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
    
    public function getFile() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $size = $this->getSize();
        header('Content-Disposition: inline; filename=' . $this->file);
        header("Content-type:application/pdf");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);
        flush();
        readfile($this->getRawUploadUrl('file'));
    }
    
    function getSize() {
        $url = $this->getRawUploadUrl('file');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_exec($ch);
        $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        if ($fileSize) {
            return $fileSize;
        }
    }
}
