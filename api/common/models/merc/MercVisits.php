<?php

namespace api\common\models\merc;

use Yii;

/**
 * This is the model class for table "merc_visits".
 *
 * @property int $id
 * @property int $org
 * @property string $last_visit
 * @property string $guid
 */
class MercVisits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'merc_visits';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['org'], 'integer'],
            [['last_visit', 'guid'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('messages', 'ID'),
            'org' => Yii::t('messages', 'Org'),
            'last_visit' => Yii::t('messages', 'Last Visit'),
        ];
    }

    public static function updateLastVisit($org_id)
    {
        $guid = mercDicconst::getSetting('enterprise_guid');
        $visit = MercVisits::findOne(['org' => $org_id, 'guid' => $guid]);

        if($visit == null)
        {
            $visit = new self();
            $visit->org = $org_id;
            $visit->guid = $guid;
        }

        $visit->last_visit = gmdate("Y-m-d H:i:s");
        $visit->save();
        return $visit;
    }

    public static function getLastVisit($org_id)
    {
        $guid = mercDicconst::getSetting('enterprise_guid');
        $visit = MercVisits::findOne(['org' => $org_id, 'guid' => $guid]);

        if(isset($visit))
            return $visit->last_visit;
        return null;
    }
}
