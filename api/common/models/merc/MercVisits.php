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
 * @property string $action
 */
class MercVisits extends \yii\db\ActiveRecord
{
    const LOAD_VSD = 'loadVsd';
    const LOAD_STOCK_ENTRY = 'loadStockEntry';
    const LOAD_VSD_LIST = 'MercVSDList';
    const LOAD_STOCK_ENTRY_LIST = 'MercStockEntryList';

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
            [['last_visit', 'guid', 'action'], 'safe'],
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

    public static function updateLastVisit($org_id, $action, $guid = null)
    {
        $guid = $guid ?? mercDicconst::getSetting('enterprise_guid');
        $visit = MercVisits::findOne(['org' => $org_id, 'guid' => $guid, 'action' => $action]);

        if($visit == null)
        {
            $visit = new self();
            $visit->org = $org_id;
            $visit->guid = $guid;
            $visit->action = $action;
        }

        $visit->last_visit = gmdate("Y-m-d H:i:s");
        $visit->save();
        return $visit;
    }

    public static function getLastVisit($org_id, $action, $guid = null)
    {
        $guid = $guid ?? mercDicconst::getSetting('enterprise_guid');
        $visit = MercVisits::findOne(['org' => $org_id, 'guid' => $guid, 'action' => $action]);

        return isset($visit->last_visit) ? gmdate("Y-m-d H:i:s", strtotime($visit->last_visit ) - 60 * 60) : date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1, 2000));
    }
}
