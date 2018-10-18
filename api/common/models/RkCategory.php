<?php

namespace api\common\models;

use Yii;
use common\models\Organization;

/**
 * This is the model class for table "rk_access".
 *
 * @property integer  $id
 * @property integer  $fid
 * @property integer  $org
 * @property integer  $type
 * @property string   $rid
 * @property string   $acc
 * @property string   $login
 * @property string   $password
 * @property string   $token
 * @property string   $lic
 * @property datetime $fd
 * @property datetime $td
 * @property integer  $ver
 * @property integer  $locked
 * @property string   $usereq
 * @property string   $comment
 * @property string   $salespoint
 * @method prependTo() prependTo(\kartik\tree\models\Tree $node)
 */
class RkCategory extends \kartik\tree\models\Tree
{

    const STATUS_UNLOCKED = 0;
    const STATUS_LOCKED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_category';
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['type', 'fid', 'acc', 'version'], 'safe'];
        return $rules;
    }

    /*
    /**
     * @inheritdoc
     */
    /*
    public function rules()
    {
        return [
            [['acc','rid','denom'], 'required'],
            [['acc','rid'], 'integer'],
          //  [['comment'], 'string', 'max' => 255],
            [['id','root', 'rid', 'lft', 'rgt', 'lvl', 'acc','rid','denom',
            'prnt','denom','icon','icon_type','active','selected','disabled','readonly',
            'visible','collapsed','movable_u','movable_d','movable_l','movable_r',
                'removable','removable_all','created_at','store_type','updated_at','type','fid','acc', 'version'],'safe']
            
        ];
    }
    */

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'fid'        => 'FID',
            'token'      => 'Token',
            'Nonce'      => 'Nonce',
            'rid'        => 'RID Store House',
            'denom'      => 'Наименование Store House',
            'updated_at' => 'Обновлено',
        ];
    }

    public static function getDb()
    {
        return \Yii::$app->db_api;
    }

}
