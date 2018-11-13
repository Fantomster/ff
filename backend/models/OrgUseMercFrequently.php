<?php


namespace backend\models;

use yii\base\Model;
use yii\data\SqlDataProvider;
use Yii;


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
        $not = '';
        if ($flag) {
            $not = 'not';
        }
        $sql = "SELECT id, name FROM `{$this->db}`.`organization` WHERE id $not in
       (
           SELECT org FROM `{$this->db_api}`.`merc_pconst` where VALUE in (
           SELECT DISTINCT recipient_guid FROM `{$this->db_api}`.`merc_vsd` WHERE status = :status AND  last_update_date >= DATE(NOW()) - INTERVAL 30 DAY)
       )";

        $count = Yii::$app->db->createCommand($sql, [':status' => 'UTILIZED'])->execute();

        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => [':status' => 'UTILIZED'],
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'name',
                ],
            ],
        ]);

        return $provider;
    }
}