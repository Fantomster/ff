<?php

namespace backend\models;

use api\common\models\merc\mercPconst;
use api\common\models\merc\MercVsd;
use common\helpers\DBNameHelper;
use common\models\Organization;
use yii\base\Model;
use yii\data\SqlDataProvider;
use Yii;
use yii\db\Expression;
use yii\db\Query;

class OrgUseMercFrequently extends Model
{
    private $db;
    private $db_api;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->db = $this->dbName(Yii::$app->db->dsn);
        $this->db_api = $this->dbName(Yii::$app->db_api->dsn);
    }

    private function dbName(string $dns)
    {
        $temp1 = explode(';', $dns);
        $temp2 = explode('=', $temp1[1]);
        return $temp2[1];
    }

    public function getOrgList(bool $flag = false)
    {
        $dbName = DBNameHelper::getApiName();
        $mercPconst = mercPconst::tableName();
        $merc_vsd = MercVsd::tableName();

        $value = (new Query())
            ->distinct()
            ->select("recipient_guid")
            ->from("{$dbName}.{$merc_vsd}")
            ->where(["status" => "UTILIZED"])
            ->andWhere("last_update_date >= :last_update_date", [
                ":last_update_date" => new Expression("DATE(NOW()) - INTERVAL 30 DAY")
            ])
            ->createCommand()
            ->getRawSql();

        $condition = (new Query())
            ->select("org")
            ->from("{$dbName}.{$mercPconst}")
            ->where("VALUE IN ({$value})")
            ->createCommand()
            ->getRawSql();

        $query = (new Query())
            ->select([
                "id",
                "name"
            ])
            ->from(Organization::tableName());

        if ($flag) {
            $query->where("NOT id IN ({$condition})");
        } else {
            $query->where("id IN ({$condition})");
        }
        $query->andWhere(['blacklisted' => Organization::STATUS_WHITELISTED]);

        $provider = new SqlDataProvider([
            'sql'        => $query->createCommand()->getRawSql(),
            'totalCount' => $query->count(),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort'       => [
                'attributes' => [
                    'name',
                ],
            ],
        ]);

        return $provider;
    }
}