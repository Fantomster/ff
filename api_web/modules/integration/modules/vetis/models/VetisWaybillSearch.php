<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 8/31/2018
 * Time: 1:51 PM
 */

namespace api_web\modules\integration\modules\vetis\models;

use api_web\classes\UserWebApi;
use api\common\models\merc\MercVsd;
use common\helpers\DBNameHelper;
use yii\db\Query;

/**
 * Class VetisWaybillSearch
 *
 * @package api_web\modules\integration\modules\vetis\models
 */
class VetisWaybillSearch extends MercVsd
{
    /**
     * @var
     */
    public $from;
    /**
     * @var
     */
    public $to;
    /**
     * @var
     */
    public $acquirer_id;

    /**
     * @param $params
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function search($params, $page, $pageSize)
    {
        $tableName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db->dsn);
        if (!empty($this->acquirer_id)) {
            if (is_array($this->acquirer_id)) {
                $strOrgIds = implode(',', $this->acquirer_id);
            } else {
                $strOrgIds = $this->acquirer_id;
            }
        } else {
            $orgIds = (new UserWebApi())->getUserOrganizationBusinessList();
            $strOrgIds = array_map(function ($el) {
                return $el['id'];
            }, $orgIds['result']);
            $strOrgIds = implode(',', $strOrgIds);
        }
        $sql = 'SELECT * FROM (
                SELECT 
                     @page := case 
                               when (@row >= (@page_size * @page + @offset)) and
                                    ((@prev_order_id is null) or (@prev_order_id is not null and @prev_order_id != coalesce(order_id, -1)))
                               then @page + 1
                               else @page
                              end pg,
                     @row := @row + 1 rn,
                     @offset := case 
                               when (@row >= @page_size * @page + @offset) 
                               then (@row - @page_size * @page)
                               else @offset := 0
                              end, 
                     @prev_order_id := order_id,
                     tb.*
                FROM (
                SELECT DISTINCT a.uuid, 
                a.date_doc,
                c.order_id,
                a.product_name,
                a.sender_guid,
                a.status,
                a.type,
                case when c.id is not null then 
                  (
                  select max(date_doc) 
                    from  merc_vsd aa,
                          `' . $tableName . '`.order_content ab 
                    where ab.order_id = c.order_id
                      and aa.uuid = ab.merc_uuid
                  )
                else null end ort
                FROM (SELECT @row := 0, @page_size := :pageSize, @page := 0, @offset := 0, @prev_order_id := NULL) x,
                       merc_vsd a
                join merc_pconst b on b.const_id = 10 and b.value in (a.recipient_guid,  a.sender_guid)
                left join `' . $tableName . '`.order_content c on a.uuid = c.merc_uuid
                where 
                b.org in (' . $strOrgIds . ')';
        $query_params = [
            ':page'     => $page,
            ':pageSize' => $pageSize,
        ];
        $arCount = [];
        foreach ($params as $key => $param) {
            if ($key == 'date') {
                $start_date = date('Y-m-d 00:00:00', strtotime($this->from));
                $end_date = date('Y-m-d 23:59:59', strtotime($this->to));
            } elseif ($key == 'product_name') {
                $sql .= " and a.product_name in (";
                for ($i=0; $i < count($this->product_name); $i++){
                    $sql .=  ($i==0 ? '' : ",") . ":product_name" . $i;
                    $query_params[':product_name' . $i] = $this->product_name[$i];
                }
                $arCount['product_name'] = $this->product_name;
                $sql .= ")";
            } elseif ($key == 'sender_guid') {
                foreach ($this->sender_guid as $item) {
                    if (!preg_match('/[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}/', $item)) {
                        return ['uuids' => [], 'groups' => []];
                    }
                }
                $sender = implode('\',\'', $this->sender_guid);
                $sql .= " and a.sender_guid in ('$sender') ";
                $arCount['sender_guid'] = $this->sender_guid;
            } elseif ($key == 'type') {
                $sql .= ' and a.type=:type';
                $query_params[':type'] = $this->type;
                $arCount['type'] = $this->type;
            } elseif ($key == 'status') {
                $sql .= ' and a.status=:status';
                $query_params[':status'] = $this->status;
                $arCount['status'] = $this->status;
            }
        }
        $between = null;
        if (isset($start_date) && isset($end_date)) {
            $query_params[':start_date'] = $start_date;
            $query_params[':end_date'] = $end_date;
            $sql .= ' and (a.date_doc >= :start_date and a.date_doc <= :end_date) ';
            $between = ['between', "date_doc", $start_date, $end_date];
        }

        $sql .= '
                order by coalesce(ort, a.date_doc) desc, order_id, a.date_doc desc
                ) tb 
              ) tb2 where pg=:page
             ';

        $result = \Yii::$app->db_api->createCommand($sql, $query_params)->queryAll();

        $arUuids = $arOrders = [];
        foreach ($result as $row) {
            $arUuids[$row['uuid']] = $row['order_id'];
            if (!is_null($row['order_id'])) {
                $arOrders[$row['order_id']] = $row['order_id'];
            }
        }

        $count = MercVsd::find()->distinct()->leftJoin('merc_pconst b', 'b.const_id = 10 and b.value in (merc_vsd.recipient_guid,  merc_vsd.sender_guid)')->where(
            array_merge(['b.org' => explode(',', $strOrgIds)], $arCount)
        );
        if ($between){
            $count->andWhere($between);
        }
        $count = $count->count();

        return ['uuids' => $arUuids, 'groups' => $arOrders, 'count' => ceil($count / $pageSize)];
    }
}