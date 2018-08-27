<?php

namespace backend\models;

use api\common\models\merc\mercLog;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;
use common\models\Role;
use common\models\Profile;
use common\models\Organization;
use common\models\Job;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class MercuryReportSearch extends mercLog {

    public $dateTo;
    public $dateFrom;
    public $succCount;
    public $errorCount;
    public $org_name;

    /**
     * @inheritdoc
     */
    public function rules() {
        return array_merge(parent::rules(),[
            [['succCount', 'errorCount', 'org_name','dateTo','dateFrom'], 'safe'],
        ]);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params) {
        $query = self::find();

        $organizationTable = Organization::tableName();
        $db = \Yii::$app->db;
        $dbName = $this->getDsnAttribute('dbname', $db->dsn);

        $query->select('org.`name` as org_name, log.organization_id, SUM(case when log.`status` = \'COMPLETED\' then 1 else 0 end) as succCount, SUM(case when log.`status` <> \'COMPLETED\' then 1 else 0 end) as errorCount');
        $query->from('merc_log as log');
        $query->leftJoin("$dbName.$organizationTable as org", 'org.id = log.organization_id');
        $query->where('log.`action` = \'getVetDocumentDone\'');
        $query->groupBy(['log.organization_id']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['organization_id' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $this->dateFrom = date('Y-m-d', strtotime(date('Y-m-d') . ' - 3 month'));
            $this->dateTo = date('Y-m-d');
            $query->andWhere('log.created_at between :dateFrom and :dateTo', [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo]);
            return $dataProvider;
        }

        if (!(isset($this->dateFrom) || isset($this->dateTo))) {
            $this->dateFrom = date('Y-m-d', strtotime(date('Y-m-d') . ' - 3 month'));
            $this->dateTo = date('Y-m-d');
        }
        $query->andWhere('log.created_at between :dateFrom and :dateTo', [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo]);

        if(isset($this->organization_id))
            $query->andWhere('log.organization_id = :org_id', [':org_id' => $this->organization_id]);

        return $dataProvider;
    }

    /**
     * Возвращает пользователей по их статусу
     *
     * @return array
     */
    public static function getListToStatus() {

        $models[]=['id'=>'0','name_allow'=>'Не активен'];
        $models[]=['id'=>'1','name_allow'=>'Активен'];
        $models[]=['id'=>'2','name_allow'=>'Ожидается подтверждение E-mail'];

        return
            ArrayHelper::map($models, 'id', 'name_allow');
        // );
    }

    /**
     * Возвращает пользователей по их языку
     *
     * @return array
     */
    public static function getListToLanguage() {

        $sql = 'SELECT DISTINCT `language` FROM `user` ORDER BY `language`';
        $models0 = \Yii::$app->db->createCommand($sql)->queryAll();
        $models = array();
        foreach($models0 as $m) {
            $models[]=['id'=>$m['language'],'name_allow'=>$m['language']];
        }

        /*print "<pre>";
        print_r($models);
        print "</pre>";
        die();*/
            /*$models = User::find()
            ->select(['language'])
            ->asArray()
            ->all();
            $models = ActiveQuery::removeDuplicateModels($models);*/

            return
                ArrayHelper::map($models, 'id','name_allow');
        }

}
