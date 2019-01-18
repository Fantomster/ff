<?php

namespace backend\models;

use common\helpers\DBNameHelper;
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
     * @param $params
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function search($params)
    {
        $query = self::find();

        $organizationTable = Organization::tableName();
        $dbName = DBNameHelper::getMainName();

        $query->select('org.name as orgName, log.organization_id, SUM(case when log.type = \'success\' then 1 else 0 end) as succCount, SUM(case when log.type <> \'success\' then 1 else 0 end) as errorCount');
        $query->from('journal as log');
        $query->leftJoin("$dbName.$organizationTable as org", 'org.id = log.organization_id');
        $query->where('log.operation_code = 3');
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



        if (empty(\Yii::$app->request->get("date") || \Yii::$app->request->get("date2"))) {
            $this->dateFrom = date('Y-m-d', strtotime(date('Y-m-d') . ' - 3 month'));
            $this->dateTo = date('Y-m-d');
        }
        else {
            $today = new \DateTime();
            $dateFilterFrom = !empty(\Yii::$app->request->get("date")) ? \Yii::$app->request->get("date") : "01.12.2016";
            $dateFilterTo = !empty(\Yii::$app->request->get("date2")) ? \Yii::$app->request->get("date2") : $today->format('d.m.Y');

            $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
            $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 00:00:00");
            $end = $dtEnd->add(new \DateInterval('P1D'));

            $this->dateFrom = $dt->format('Y-m-d');
            $this->dateTo = $end->format('Y-m-d');
        }
        $query->andWhere('log.created_at between :dateFrom and :dateTo', [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo]);

        if (isset($this->orgName))
            $query->andWhere('org.name like :org_name', [':org_name' => '%' . $this->orgName . '%']);

        return $dataProvider;
    }
}
