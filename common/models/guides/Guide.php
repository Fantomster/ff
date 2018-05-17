<?php

namespace common\models\guides;

use Yii;
use common\models\Organization;
use yii\db\Expression;

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

    public static $COLORS = [
        "D81B60",
        "8E24AA",
        "D81B60",
        "8E24AA",
        "5E35B1",
        "5C6BC0",
        "039BE5",
        "009688",
        "C0CA33",
        "FFD600",
        "FB8C00",
        "F4511E",
        "D32F2F",
        "A1887F",
        "5D4037",
        "BDBDBD",
        "757575",
        "000000"];

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
            ['color','custom_validate_color'],
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
        $result = GuideProduct::find()->where(['guide_id' => $this->id])->select('cbg_id')->orderBy(['id'=>'desc'])->asArray()->all();
        return \yii\helpers\ArrayHelper::getColumn($result, 'cbg_id');
    }

    /**
     * @return integer
     */
    public function getProductCount() {
        return GuideProduct::find()->where(['guide_id' => $this->id])->count();
    }

    /**
     * Валидация цвета
     */
    public function custom_validate_color() {
        $allow_color = [
            "D81B60",
            "8E24AA",
            "5E35B1",
            "5C6BC0",
            "039BE5",
            "009688",
            "C0CA33",
            "FFD600",
            "FB8C00",
            "F4511E",
            "D32F2F",
            "A1887F",
            "5D4037",
            "BDBDBD",
            "757575",
            "000000"
        ];

        $this->color = mb_strtoupper(ltrim(trim($this->color), '#'));

        if(strlen($this->color) != 6) {
            $this->addError('color','the field is not equal to 6 characters, please pass a value in HEX');
        }

        if(!in_array($this->color, $allow_color)) {
            $this->addError('color',
                'This color is forbidden to the selection, a list of available colors: '
                . implode(', ', $allow_color)
            );
        }
    }
    
    public function delete() {
        GuideProduct::deleteAll(['guide_id' => $this->id]);
        parent::delete();
    }

    public function beforeSave($insert)
    {

        if(!$insert) {
            $this->updated_at = new Expression('NOW()');
        } else {
            $this->created_at = new Expression('NOW()');
        }

        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        if (!is_a(Yii::$app, 'yii\console\Application')) {
//            \api\modules\v1\modules\mobile\components\NotificationHelper::actionGuide($this->id); 
        }
    }
}
