<?php

namespace backend\models;

use common\models\Journal;
use yii\data\ActiveDataProvider;
use common\models\Organization;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class MercuryReportSearch extends Journal
{

    public $dateTo;
    public $dateFrom;
    public $succCount;
    public $errorCount;
    public $orgName;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['succCount', 'errorCount', 'orgName', 'dateTo', 'dateFrom'], 'safe'],
        ]);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find();

        $organizationTable = Organization::tableName();
        $db = \Yii::$app->db;
        $dbName = "`" . $this->getDsnAttribute('dbname', $db->dsn) . "`";

        $query->select('org.`name` as orgName, log.organization_id, SUM(case when log.`type` = \'success\' then 1 else 0 end) as succCount, SUM(case when log.`type` <> \'success\' then 1 else 0 end) as errorCount');
        $query->from('journal as log');
        $query->leftJoin("$dbName.$organizationTable as org", 'org.id = log.organization_id');
        $query->where('log.`operation_code` = 3');
        $query->groupBy(['log.organization_id']);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'attributes'   => ['orgName', 'succCount', 'errorCount'],
                'defaultOrder' => ['orgName' => SORT_ASC]],
            'pagination' => [
                'pageSize' => 20
            ]
        ]);

        $this->load($params);
        $this->service_id = 4;
        $this->operation_code = '3';

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

        $query->andWhere('log.created_at1 between :dateFrom and :dateTo', [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo]);

        if (isset($this->orgName))
            $query->andWhere('org.name like :org_name', [':org_name' => '%' . $this->orgName . '%']);

        return $dataProvider;
    }

    private function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }

}
