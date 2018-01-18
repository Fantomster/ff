<?php

namespace api\common\models\iiko;

use Yii;

/**
 * This is the model class for table "{{%iiko_dicconst}}".
 *
 * @property integer $id
 * @property string $denom
 * @property string $def_value
 * @property string $comment
 * @property integer $type
 * @property integer $is_active
 */
class iikoDicconst extends \yii\db\ActiveRecord
{
    const TYPE_DROP = 1;
    const TYPE_PASSWORD = 3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%iiko_dicconst}}';
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
            [['type', 'is_active'], 'integer'],
            [['denom', 'def_value', 'comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'denom' => Yii::t('app', 'Denom'),
            'def_value' => Yii::t('app', 'Def Value'),
            'comment' => Yii::t('app', 'Comment'),
            'type' => Yii::t('app', 'Type'),
            'is_active' => Yii::t('app', 'Is Active'),
        ];
    }

    /**
     * @return float|int|string
     */
    public function getPconstValue()
    {
        $pConst = iikoPconst::findOne(['const_id' => $this->id, 'org' => Yii::$app->user->identity->organization_id]);
        $res = (!empty($pConst)) ? $pConst->value : $this->def_value;
        if ($pConst == 'taxVat') {
            $res = $res / 100;
        }
        return $res;
    }

    public static function getSetting($denom)
    {
        $model = self::findOne(['denom' => $denom]);
        if ($model) {
            $pConst = iikoPconst::findOne(['const_id' => $model->id, 'org' => Yii::$app->user->identity->organization_id]);
            if (!empty($pConst)) {
                return $pConst->value;
            } else {
                throw new \Exception('Не заполнено свойство в настройках ' . $denom);
            }
        }
    }
}
