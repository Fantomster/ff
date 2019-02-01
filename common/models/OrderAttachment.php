<?php

namespace common\models;

use Yii;
use common\behaviors\UploadBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "order_attachment".
 *
 * @property int    $id         Идентификатор записи в таблице
 * @property int    $order_id   Идентификатор заказа, к которому относится прикреплённый файл
 * @property string $file       Наименование прикреплённого файла
 * @property string $created_at Дата и время создания записи в таблице
 *
 * @property Order  $order
 */
class OrderAttachment extends \yii\db\ActiveRecord
{

    public $resourceCategory = 'bill';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_attachment}}';
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
            [['file'], 'file', 'extensions' => 'gif, jpg, jpeg, png, bmp, pdf', 'maxSize' => 52428800, 'tooBig' => Yii::t('app', 'common.models.order_attachment.file', ['ru' => 'Размер файла не должен превышать 50 Мб'])],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            [
                'class'     => UploadBehavior::className(),
                'attribute' => 'file',
                'scenarios' => ['default'],
                'path'      => '@app/web/upload/temp/',
                'url'       => '/upload/temp/',
            ],
            'timestamp' => [
                'class'              => 'yii\behaviors\TimestampBehavior',
                'value'              => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
                'updatedAtAttribute' => false,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'order_id'   => 'Order ID',
            'file'       => 'Файл',
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

    /**
     * load file
     */
    public function getFile()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        header('Content-Disposition: inline; filename=' . $this->file);
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        flush();
        readfile($this->getRawUploadUrl('file'));
    }

    /**
     * @return mixed
     */
    function getSize()
    {
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAssignment()
    {
        return $this->hasOne(OrderAssignment::className(), ['order_id' => 'order_id']);
    }

}
