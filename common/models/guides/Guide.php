<?php

namespace common\models\guides;

use Yii;
use common\models\Organization;

/**
 * This is the model class for table "guide".
 *
 * @property integer $id
 * @property integer $client_id
 * @property integer $type
 * @property string $name
 * @property integer $deleted
 * @property string $created_at
 * @property string $updated_at
* @property string $color
 *
 * @property Organization $client
 * @property GuideProduct[] $guideProducts
 * @property integer $productCount
 * @property integer[] $guideProductsIds
 */
class Guide extends \yii\db\ActiveRecord {

    const TYPE_FAVORITE = 1;
    const TYPE_GUIDE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'guide';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['client_id', 'type', 'name'], 'required'],
            [['client_id', 'type', 'deleted'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name','color'], 'string', 'max' => 255],
            [['name'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['client_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'value' => function ($event) {
                    return gmdate("Y-m-d H:i:s");
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'client_id' => Yii::t('app', 'Client ID'),
            'type' => Yii::t('app', 'Type'),
            'name' => Yii::t('app', 'Name'),
            'deleted' => Yii::t('app', 'Deleted'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient() {
        return $this->hasOne(Organization::className(), ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGuideProducts() {
        return $this->hasMany(GuideProduct::className(), ['guide_id' => 'id']);
    }

    /**
     * @return integer[]
     */
    public function getGuideProductsIds() {
        $result = GuideProduct::find()->where(['guide_id' => $this->id])->select('cbg_id')->asArray()->all();
        return \yii\helpers\ArrayHelper::getColumn($result, 'cbg_id');
    }

    /**
     * @return integer
     */
    public function getProductCount() {
        return count($this->guideProducts);
    }
    
    public function delete() {
        GuideProduct::deleteAll(['guide_id' => $this->id]);
        parent::delete();
    }
    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        if (!is_a(Yii::$app, 'yii\console\Application')) {
//            \api\modules\v1\modules\mobile\components\NotificationHelper::actionGuide($this->id); 
        }
    }
}
