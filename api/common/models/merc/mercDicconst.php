<?php

namespace api\common\models\merc;

use Yii;

/**
 * This is the model class for table "{{%merc_dicconst}}".
 *
 * @property integer $id
 * @property string $denom
 * @property string $def_value
 * @property string $comment
 * @property integer $type
 * @property integer $is_active
 */
class mercDicconst extends \yii\db\ActiveRecord
{
    const TYPE_DROP = 1;
    const TYPE_PASSWORD = 3;
    const TYPE_CHECKBOX = 4;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%merc_dicconst}}';
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
            'is_active' => Yii::t('app', 'Is Active')
        ];
    }

    /**
     * @return float|int|string
     */
    public function getPconstValue()
    {
        $pConst = mercPconst::findOne(['const_id' => $this->id, 'org' => Yii::$app->user->identity->organization_id]);
        $res = (!empty($pConst)) ? $pConst->value : $this->def_value;
        if ($pConst == 'taxVat') {
            $res = $res / 100;
        }
        return $res;
    }

    /**
     * @param      $denom
     * @param null $org
     * @return mixed|string
     * @throws \Exception
     */
    public static function getSetting($denom, $org = null)
    {
        $iskl = ['hand_load_only'=> 0,'vetis_password' => ''];
        $model = self::findOne(['denom' => $denom]);
            if ($model) {
                if (is_a(Yii::$app, 'yii\web\Application') && ($org == null)) {
                    $pConst = mercPconst::findOne(['const_id' => $model->id, 'org' => Yii::$app->user->identity->organization_id]);
                } else {
                    $pConst = mercPconst::findOne(['const_id' => $model->id, 'org' => $org]);
                }
                if (isset($pConst) || (key_exists($denom, $iskl))) {
                    return isset($pConst) ? $pConst->value : $iskl[$denom];
                } else {
                    throw new \Exception('Не заполнено свойство в настройках ' . $denom);
                }
            }
    }

    public static function checkSettings()
    {
        $consts = self::find()->all();
        foreach ($consts as $item)
        {
            try {
                self::getSetting($item->denom);
            }
            catch (\Exception $e)
            {
                return false;
            }
        }

        return true;
    }
}
