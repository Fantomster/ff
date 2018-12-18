<?php

namespace api\common\models;

use Yii;
use common\models\Organization;

/**
 * This is the model class for table "rk_dicconst".
 *
 * @property integer $id
 * @property string  $denom
 * @property string  $def_value
 * @property string  $comment
 */
class RkDicconst extends \yii\db\ActiveRecord
{

    const PC_TYPE_DROP = 1;
    const PC_TYPE_STRING = 2;
    const PC_TYPE_TREE = 7;
    const SH_VERSION = [
        4 => 'Store House v.4',
        5 => 'Store House v.5'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_dicconst';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['denom', 'def_value', 'comment'], 'string', 'max' => 255],
            [['denom', 'def_value', 'comment'], 'safe'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'denom'     => 'Название константы',
            'def_value' => 'Значение по умолчанию',
            'comment'   => 'Комментарий',
        ];
    }

    public function getPconstValue()
    {

        $pConst = \api\common\models\RkPconst::findOne(['const_id' => $this->id, 'org' => Yii::$app->user->identity->organization_id]);
        $res = (!isset($pConst->value)) ? $this->def_value : $pConst->value;

        if ($this->denom == 'taxVat') {
            $res = $res / 100;
        }

        return $res;
    }

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

}
