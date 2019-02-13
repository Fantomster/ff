<?php

namespace backend\models;

use api\common\models\merc\mercPconst;
use api\common\models\merc\MercVsd;
use common\helpers\DBNameHelper;
use common\models\Franchisee;
use common\models\FranchiseeAssociate;
use common\models\Order;
use common\models\OrganizationType;
use common\models\RelationUserOrganization;
use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yii\db\Expression;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class ExtendedReportsSearch extends Model
{

    public $dateTo;
    public $dateFrom;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dateTo', 'dateFrom'], 'safe'],
        ];
    }

    public function initDates()
    {
        if (empty(\Yii::$app->request->get("date") || \Yii::$app->request->get("date2"))) {
            $this->dateFrom = date('Y-m-d  00:00:00', strtotime(date('Y-m-d') . ' - 3 month'));
            $this->dateTo = date('Y-m-d  23:59:59');
        } else {
            $today = new \DateTime();
            $dateFilterFrom = !empty(\Yii::$app->request->get("date")) ? \Yii::$app->request->get("date") : "01.12.2016";
            $dateFilterTo = !empty(\Yii::$app->request->get("date2")) ? \Yii::$app->request->get("date2") : $today->format('d.m.Y');

            $dt = \DateTime::createFromFormat('d.m.Y H:i:s', $dateFilterFrom . " 00:00:00");
            $dtEnd = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', $dateFilterTo . " 23:59:59");
            $end = $dtEnd->add(new \DateInterval('P1D'));

            $this->dateFrom = $dt->format('Y-m-d 00:00:00');
            $this->dateTo = $end->format('Y-m-d 23:59:59');
        }
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param $params
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function FranchiseeTurnoverFiguresSearch()
    {
        $orderTbName = Order::tableName();
        $orgTbName = Organization::tableName();
        $franchiseeAssociateTbname = FranchiseeAssociate::tableName();
        $franchiseeTbName = Franchisee::tableName();
        $orgTypeTbname = OrganizationType::tableName();

        $query = (new \yii\db\Query())
            ->select([new Expression('case when a.id is null or a.id in (1, 2, 34) then \'Москва\'
                       else a.legal_entity
                       end owners'),
                       new Expression('replace(cast(sum(e.total_price) as char), \'.\', \',\') total_sum'),
                       'count(distinct d.id) org_count',
                       'count(distinct e.id) ord_count',
                        new Expression('DATE_FORMAT(e.created_at,\'%Y-%m\') order_period')])
            ->from("$orderTbName as e, $orgTbName as d")
            ->leftJoin("$franchiseeAssociateTbname as b", 'd.id = b.organization_id')
            ->leftJoin("$franchiseeTbName as a", 'a.id = b.franchisee_id')
            ->leftJoin("$orgTypeTbname as c", 'd.type_id = c.id')
            ->where("e.client_id = d.id
                              and d.blacklisted in (0, 1)
                              and e.created_at between :dateFrom and :dateTo
                              and e.status in (3,4,2,1)", [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo])
            ->groupBy(['owners', 'order_period'])
            ->orderBy([new Expression('order_period'),
                new Expression('case when a.id is null or a.id in (1, 2, 34) then 1 else 2 end'),
                new Expression('upper(owners)')]);


        $dataProvider = new SqlDataProvider([
            'sql'  => $query->createCommand()->getRawSql(),
            'totalCount' => $query->count()
            /*'pagination' => [
                'pageSize' => 10
            ]*/
        ]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param $params
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function NewRegistrationsSearch()
    {
        $userTbName = User::tableName();
        $orgTbName = Organization::tableName();
        $orgTypeTbname = OrganizationType::tableName();
        $relationUserOrgTbname = RelationUserOrganization::tableName();

        $query = (new \yii\db\Query())
            ->select('d.name as name, count(c.id) as cnt')
            ->from("$userTbName as a, $relationUserOrgTbname as b, $orgTbName as c, $orgTypeTbname as d")
            ->where("a.id = b.user_id
                              and b.organization_id = c.id
                              and c.created_at between :dateFrom and :dateTo
                              and c.blacklisted in (0, 1)
                              and c.type_id = d. id", [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo])
            ->groupBy(['d.name'])
            ->orderBy('d.name');

        $dataProvider = new SqlDataProvider([
            'sql'  => $query->createCommand()->getRawSql(),
            'totalCount' => $query->count()
            /*'pagination' => [
                'pageSize' => 10
            ]*/
        ]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param $params
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function MercuryReportSearch()
    {
        $apiDB = DBNameHelper::getApiName().'.';
        $mainDB = DBNameHelper::getMainName().'.';

        $mercVSDTbName = $apiDB.MercVsd::tableName();
        $mercPconstTbName = $apiDB.mercPconst::tableName();

        $orderTbName = $mainDB.Order::tableName();
        $orgTbName = $mainDB.Organization::tableName();
        $orgTypeTbname = $mainDB.OrganizationType::tableName();
        $franchiseeAssociateTbname = $mainDB.FranchiseeAssociate::tableName();
        $franchiseeTbName = $mainDB.Franchisee::tableName();

        //Query 1

        $orgIdList = (new \yii\db\Query())
            ->select([new Expression('distinct d.id')])
            ->from("$orderTbName as e, $orgTbName as d")
            ->leftJoin("$franchiseeAssociateTbname as b ", 'd.id = b.organization_id')
            ->leftJoin("$franchiseeTbName as a", 'a.id = b.franchisee_id')
            ->leftJoin("$orgTypeTbname as c", 'd.type_id = c.id')
            ->where("e.client_id = d.id
                              and d.blacklisted = 0
                              and e.created_at between :dateFrom and :dateTo
                              and e.status in (3,4,2,1)",[':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo]);

        $queryCount = (new \yii\db\Query())
            ->select(['count(a.uuid)',
                new Expression('coalesce(case when trim(c.legal_entity) = \'\' then null else trim(c.legal_entity) end, trim(c.name)) orgname')])
            ->from("$mercVSDTbName as a")
            ->leftJoin("$mercPconstTbName as b", 'b.const_id = 10 and b.value = a.recipient_guid')
            ->leftJoin ("$orgTbName as c", 'c.id = b.org')
            ->where("c.blacklisted = 0
                              and a.last_update_date between :dateFrom and :dateTo
                              and c.created_at between :dateFrom and :dateTo", [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo])
            ->andWhere(['not in','c.id', $orgIdList])
            ->groupBy(['orgname'])
            ->orderBy('orgname');

        $queryRow1 = (new \yii\db\Query())
        ->select([new Expression('\'Новые и только Меркурий\' name, count(distinct orgname) cnt')])
        ->from($queryCount);


        //Query 2
        $queryCount2 = (new \yii\db\Query())
            ->select(['count(a.uuid)',
                new Expression('coalesce(case when trim(c.legal_entity) = \'\' then null else trim(c.legal_entity) end, trim(c.name)) orgname')])
            ->from("$mercVSDTbName as a")
            ->leftJoin("$mercPconstTbName as b", 'b.const_id = 10 and b.value = a.recipient_guid')
            ->leftJoin ("$orgTbName as c", 'c.id = b.org')
            ->where("c.blacklisted = 0
                              and a.last_update_date between :dateFrom and :dateTo", [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo])
            ->andWhere(['in','c.id', $orgIdList])
            ->groupBy(['orgname'])
            ->orderBy('orgname');

        $queryRow2 = (new \yii\db\Query())
            ->select([new Expression('\'Использовали Меркурий\' name, count(distinct orgname) cnt')])
            ->from($queryCount2);


        $queryUnion1 = (new \yii\db\Query())
            ->select('*')
            ->from(['tb1' => $queryRow1->union($queryRow2)]);

        //Query 3
        $orgIdList2 = (new \yii\db\Query())
            ->select([new Expression('distinct d.id')])
            ->from("$orderTbName as e, $orgTbName as d")
            ->leftJoin("$franchiseeAssociateTbname as b ", 'd.id = b.organization_id')
            ->leftJoin("$franchiseeTbName as a", 'a.id = b.franchisee_id')
            ->leftJoin("$orgTypeTbname as c", 'd.type_id = c.id')
            ->where("e.client_id = d.id
                              and d.blacklisted = 0
                              and e.created_at between :dateFrom and :dateTo",[':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo]);

        $queryCount3 = (new \yii\db\Query())
            ->select(['count(a.uuid)',
                new Expression('coalesce(case when trim(c.legal_entity) = \'\' then null else trim(c.legal_entity) end, trim(c.name)) orgname')])
            ->from("$mercVSDTbName as a")
            ->leftJoin("$mercPconstTbName as b", 'b.const_id = 10 and b.value = a.recipient_guid')
            ->leftJoin ("$orgTbName as c", 'c.id = b.org')
            ->where("c.blacklisted = 0
                              and a.last_update_date between :dateFrom and :dateTo", [':dateFrom' => $this->dateFrom, ':dateTo' => $this->dateTo])
            ->andWhere(['not in','c.id', $orgIdList2])
            ->groupBy(['orgname'])
            ->orderBy('orgname');

        $queryRow3 = (new \yii\db\Query())
            ->select([new Expression('\'Не делали заказов, но использовали Меркурий\' name, count(distinct orgname) cnt')])
            ->from($queryCount3);

        $query = (new \yii\db\Query())
            ->select('*')
            ->from(['tb1' => $queryUnion1->union($queryRow3)]);

        $dataProvider = new SqlDataProvider([
            'sql'  => $query->createCommand()->getRawSql(),
            'totalCount' => $query->count()
            /*'pagination' => [
                'pageSize' => 10
            ]*/
        ]);

        return $dataProvider;
    }
}
