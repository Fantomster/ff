<?php

namespace backend\models;

use common\models\Franchisee;
use common\models\FranchiseeAssociate;
use common\models\Order;
use common\models\Organization;
use Yii;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class DynamicUsageSearch
 *
 * @package backend\models
 */
class DynamicUsageSearch extends \yii\base\Model
{
    public $org_name;
    public $org_id;
    public $org_contact_name;
    public $org_city;
    public $org_email;
    public $org_type;
    public $org_registred;
    public $franchisee_name;
    public $franchisee_region;
    public $order_max_date;
    public $order_cnt;
    public $w5_sum;
    public $w5_count;
    public $w5_vendor;
    public $w4_sum;
    public $w4_count;
    public $w4_vendor;
    public $w3_sum;
    public $w3_count;
    public $w3_vendor;
    public $w2_sum;
    public $w2_count;
    public $w2_vendor;
    public $w1_sum;
    public $w1_count;
    public $w1_vendor;
    public $sort;
    public $start_date;
    public $searchString;

    public function __construct(array $config = [])
    {
        $this->start_date = Yii::$app->request->get("start_date");
        if (!empty($this->start_date)) {
            $dateTime = \DateTime::createFromFormat('d.m.Y H:i:s', "{$this->start_date} 00:00:00");
            $this->start_date = " '{$dateTime->format('Y-m-d H:i:s')}' ";
        } else {
            $this->start_date = " NOW() ";
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_date','searchString'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'org_name'          => 'Организация',
            'org_id'            => 'ID',
            'org_contact_name'  => 'Контактное лицо',
            'org_city'          => 'Город',
            'org_email'         => 'Email',
            'org_type'          => 'Тип',
            'org_registred'     => 'Дата регистрации',
            'franchisee_name'   => 'Франчайзи',
            'franchisee_region' => 'Регион',
            'order_max_date'    => 'Дата последнего заказа',
            'order_cnt'         => 'Общее количество заказов',
            'w5_sum'            => '',
            'w5_count'          => '5 недель назад<br>Оборот / Заказов / Поставщиков',
            'w5_vendor'         => '',
            'w4_sum'            => '',
            'w4_count'          => '4 недели назад<br>Оборот / Заказов / Поставщиков',
            'w4_vendor'         => '',
            'w3_sum'            => '',
            'w3_count'          => '3 недели назад<br>Оборот / Заказов / Поставщиков',
            'w3_vendor'         => '',
            'w2_sum'            => '',
            'w2_count'          => '2 недели назад<br>Оборот / Заказов / Поставщиков',
            'w2_vendor'         => '',
            'w1_sum'            => '',
            'w1_count'          => '1 неделю назад<br>Оборот / Заказов / Поставщиков',
            'w1_vendor'         => '',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return SqlDataProvider
     */
    public function search(array $params)
    {
        $this->load($params);

        $query = (new Query())
            ->select("q.*")
            ->from([
                "q" => (new Query())
                    ->select([
                        "org.*",
                        "order_max_date" => new Expression("DATE_FORMAT(MAX(o.created_at), '%Y-%m-%d')"),
                        "order_cnt"      => new Expression("COUNT(o.id)"),
                        "w5_sum"         => $this->getSum("sum", 37, 30),
                        "w5_count"       => $this->getSum("count", 37, 30),
                        "w5_vendor"      => $this->getCount(37, 30),
                        "w4_sum"         => $this->getSum("sum", 31, 24),
                        "w4_count"       => $this->getSum("count", 31, 24),
                        "w4_vendor"      => $this->getCount(31, 24),
                        "w3_sum"         => $this->getSum("sum", 23, 16),
                        "w3_count"       => $this->getSum("count", 23, 16),
                        "w3_vendor"      => $this->getCount(23, 16),
                        "w2_sum"         => $this->getSum("sum", 15, 8),
                        "w2_count"       => $this->getSum("count", 15, 8),
                        "w2_vendor"      => $this->getCount(15, 8),
                        "w1_sum"         => $this->getSum("sum", 7, false),
                        "w1_count"       => $this->getSum("count", 7, false),
                        "w1_vendor"      => $this->getCount(7, false),
                    ])
                    ->from([
                        "org" => (new Query())
                            ->select([
                                "org_name"              => "a.name",
                                "org_contact_name"      => "a.contact_name",
                                "org_city"              => "a.city",
                                "org_email"             => "a.email",
                                "org_id"                => "a.id",
                                "org_type"              => new Expression("CASE a.type_id WHEN 1 THEN 'Ресторан' WHEN 2 THEN 'Поставщик' ELSE 'Неизвестный'end"),
                                "org_type_id"           => "a.type_id",
                                "org_registred"         => new Expression("DATE_FORMAT(a.created_at,'%Y-%m-%d')"),
                                "org_registred_peroiod" => new Expression("DATE_FORMAT(a.created_at,'%Y-%m')"),
                                "franchisee_name"       => new Expression("CASE WHEN c.id IN (1, 2, 34) THEN 'MixCart Москва' WHEN c.id IS NULL THEN 'MixCart n/a' ELSE c.legal_entity END"),
                                "franchisee_region"     => new Expression("CASE WHEN c.id in (1, 2, 34) OR c.id IS NULL THEN 'Москва' ELSE 'Регионы' END")
                            ])
                            ->from(["a" => Organization::tableName()])
                            ->leftJoin(["b" => FranchiseeAssociate::tableName()], "a.id = b.organization_id")
                            ->leftJoin(["c" => Franchisee::tableName()], "b.franchisee_id = c.id")
                            ->where(["a.blacklisted" => 0])
                    ])
                    ->leftJoin([
                        "o" => (new Query())
                            ->select([
                                "id",
                                "org_id" => "client_id",
                                "vendor_id",
                                "created_at",
                                "total_price",
                            ])
                            ->from(Order::tableName())
                            ->union((new Query())
                                ->select([
                                    "id",
                                    "vendor_id",
                                    "vendor_id" => "vendor_id",
                                    "created_at",
                                    "total_price"
                                ])
                                ->from(Order::tableName()))
                    ], "org.org_id = o.org_id")
                    ->groupBy([
                        "org_name",
                        "org_contact_name",
                        "org_city",
                        "org_email",
                        "org_id",
                        "org_type",
                        "org_registred",
                        "org_registred_peroiod",
                        "franchisee_name",
                        "franchisee_region"
                    ])
            ])
            ->filterWhere(["LIKE", "org_name", $this->searchString])
            ->orFilterWhere(["org_id" => $this->searchString])
            ->orFilterWhere(["LIKE", "franchisee_name", $this->searchString])
            ->orFilterWhere(["LIKE", "org_contact_name", $this->searchString])
            ->orFilterWhere(["LIKE", "org_email", $this->searchString])
            ->orFilterWhere(["LIKE", "org_city", $this->searchString])
            ->orderBy([
                new Expression("CASE WHEN order_cnt > 0 then 1 else 2 end"),
                new Expression("franchisee_region"),
                new Expression("org_type_id"),
                new Expression("CASE WHEN w1_count = 0 AND w2_count = 0 AND w3_count = 0 and w4_count = 0 and w5_count > 0 then 1 else 2 end"),
                new Expression("CASE WHEN w1_count = 0 AND w2_count = 0 AND w3_count = 0 and (w4_count > 0 or w5_count > 0) then 1 else 2 end"),
                new Expression("CASE WHEN w1_count = 0 AND w2_count = 0 AND (w3_count > 0 or w4_count > 0 or w5_count > 0) then 1 else 2 end"),
                new Expression("CASE WHEN w1_count = 0 AND (w2_count > 0 OR w3_count > 0 or w4_count > 0 or w5_count > 0) then 1 else 2 end"),
                new Expression("CASE WHEN w1_count > 0 AND w2_count > 0 AND w3_count > 0 and w4_count > 0 and w5_count > 0 then 1 else 2 end"),
                new Expression("CASE WHEN w1_count = 0 AND w2_count = 0 AND w3_count = 0 and w4_count = 0 and w5_count = 0 then 1 else 2 end"),
                new Expression("CASE WHEN order_cnt > 1 THEN 1 WHEN order_cnt > 0 THEN 2 ELSE 3 END"),
                "order_max_date" => SORT_DESC,
                "order_cnt"      => SORT_DESC,
            ]);

        $dataProvider = new SqlDataProvider([
            'sql'        => $query->createCommand()->getRawSql(),
            'pagination' => [
                'page'     => isset($params['page']) ? ($params['page'] - 1) : 0,
                'pageSize' => 20,],
        ]);

        return $dataProvider;
    }

    private function getSum($nameField, $fromInterval, $toInterval): string
    {
        $then = ($nameField === "sum") ? "o.total_price" : 1;

        $format = "SUM(CASE WHEN o.created_at "
            . "BETWEEN DATE_SUB({$this->start_date}, INTERVAL %s DAY) "
            . "AND %s THEN %s ELSE 0 END)";

        return new Expression(sprintf($format, $fromInterval, $this->checkInterval($toInterval), $then));
    }

    private function getCount($fromInterval, $toInterval): string
    {
        $format = "COUNT(DISTINCT CASE WHEN o.created_at "
            . "BETWEEN DATE_SUB({$this->start_date}, INTERVAL %s DAY) "
            . "AND %s then o.vendor_id ELSE NULL END)";

        return new Expression(sprintf($format, $fromInterval, $this->checkInterval($toInterval)));
    }

    private function checkInterval($toInterval): string
    {
        if ($toInterval !== false) {
            $toInterval = sprintf("DATE_SUB(%s, INTERVAL %s DAY)", $this->start_date, $toInterval);
        } else {
            $toInterval = $this->start_date;
        }

        return $toInterval;
    }
}
