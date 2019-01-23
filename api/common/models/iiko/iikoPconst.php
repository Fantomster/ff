<?php

namespace api\common\models\iiko;

use Yii;

/**
 * This is the model class for table "{{%iiko_pconst}}".
 *
 * @property integer $id
 * @property integer $const_id
 * @property integer $org
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 *
 * @property iikoDicconst $const
 */
class iikoPconst extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%iiko_pconst}}';
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
            [['const_id', 'org'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['value'], 'string', 'max' => 255],
            [['const_id'], 'exist', 'skipOnError' => true, 'targetClass' => iikoDicconst::className(), 'targetAttribute' => ['const_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'const_id' => Yii::t('app', 'Const ID'),
            'org' => Yii::t('app', 'Org'),
            'value' => Yii::t('app', 'Value'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConst()
    {
        return $this->hasOne(IikoDicconst::className(), ['id' => 'const_id']);
    }

    public static function getSettingsColumn($organization)
    {
        $res = self::find()
            ->select('*')
            ->join('LEFT JOIN', 'iiko_dicconst', 'iiko_dicconst.denom = "column_number_invoice"')
            ->where(['org' => $organization])
            ->andWhere('iiko_pconst.const_id = iiko_dicconst.id')
            ->one();
        if($res)
        {
            return ($res->value == 1)? true:false;
        }

    }
}
