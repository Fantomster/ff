<?php


namespace backend\models;

use yii\base\Model;
use yii\data\SqlDataProvider;
use Yii;


class OrgUseMercFrequently extends Model
{
    public $db;
    public $db_api;
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->setDbNames();
    }

    private function setDbNames()
    {
        $temp1 = explode(';', Yii::$app->db->dsn);
        $temp2 = explode('=', $temp1[1]);
        $this->db=$temp2[1];

        $temp3 = explode(';', Yii::$app->db_api->dsn);
        $temp4 = explode('=', $temp3[1]);
        $this->db_api=$temp4[1];
    }

    public function getOrgListIn()
    {
        $sql1 = "SELECT COUNT(name) FROM `".$this->db."`.`organization` WHERE id in
(
SELECT org FROM `".$this->db_api."`.`merc_pconst` where VALUE in (
SELECT DISTINCT recipient_guid FROM `".$this->db_api."`.`merc_vsd` WHERE status = :status AND  last_update_date >= DATE(NOW()) - INTERVAL 30 DAY)
)";
        $sql2 = "SELECT id, name FROM `".$this->db."`.`organization` WHERE id in
(
SELECT org FROM `".$this->db_api."`.`merc_pconst` where VALUE in (
SELECT DISTINCT recipient_guid FROM `".$this->db_api."`.`merc_vsd` WHERE status = :status AND  last_update_date >= DATE(NOW()) - INTERVAL 30 DAY)
)";

//        print_r(Yii::$app->db2->dsn);
        $count = Yii::$app->db->createCommand($sql1, [':status' => 'UTILIZED'])->queryScalar();


        $provider = new SqlDataProvider([
            'sql' => $sql2,
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

    public function getOrgListNotIn()
    {

        $sql1 = "SELECT COUNT(name) FROM `".$this->db."`.`organization` WHERE id not in
(
SELECT org FROM `".$this->db_api."`.`merc_pconst` where VALUE in (
SELECT DISTINCT recipient_guid FROM `".$this->db_api."`.`merc_vsd` WHERE status = :status AND  last_update_date >= DATE(NOW()) - INTERVAL 30 DAY)
)";
        $sql2 = "SELECT id, name FROM `".$this->db."`.`organization` WHERE id not in
(
SELECT org FROM `".$this->db_api."`.`merc_pconst` where VALUE in (
SELECT DISTINCT recipient_guid FROM `".$this->db_api."`.`merc_vsd` WHERE status = :status AND  last_update_date >= DATE(NOW()) - INTERVAL 30 DAY)
)";

//        print_r(Yii::$app->db2->dsn);
        $count = Yii::$app->db->createCommand($sql1, [':status' => 'UTILIZED'])->queryScalar();


        $provider = new SqlDataProvider([
            'sql' => $sql2,
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