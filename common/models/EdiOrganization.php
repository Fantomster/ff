<?php

namespace common\models;

use api\common\models\iiko\iikoService;
use api\common\models\merc\mercService;
use api\common\models\RkServicedata;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use common\behaviors\ImageUploadBehavior;
use Imagine\Image\ManipulatorInterface;
use common\models\guides\Guide;


/**
 * This is the model class for table "organization".
 *
 * @property integer $id
 * @property integer $organization_id
 * @property integer $gln_code
 * @property string $login
 * @property string $pass
 */
class EdiOrganization extends \yii\db\ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'edi_organization';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['organization_id', 'gln_code'], 'integer'],
            [['organization_id'], 'unique'],
            [['login', 'pass', 'int_user_id', 'token'], 'string', 'max' => 255],
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::className(), ['id' => 'organization_id']);
    }

}
