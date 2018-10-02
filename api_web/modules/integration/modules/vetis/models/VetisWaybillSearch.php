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
                               then (@offset + (@row - @page_size * @page))
                               else @offset
                              end, 
                     @prev_order_id := order_id,
                     tb.*
                FROM (
                SELECT a.uuid, 
                a.date_doc,
                c.order_id,
                c.product_name,
                a.sender_guid,
                a.status,
                a.type,
                case when c.id is not null then 
                  (
                  select max(date_doc) 
                    from  merc_vsd aa,
                          `' . $tableName . '`.order_content ab 
                    where ab.order_id = c.order_id COLLATE utf8_unicode_ci
                      and aa.uuid = ab.merc_uuid COLLATE utf8_unicode_ci
                  )
                else null end ort
                FROM (SELECT @row := 0, @page_size := :pageSize, @page := 0, @offset := 0, @prev_order_id := NULL) x,
                       merc_vsd a
                join merc_pconst b on b.const_id = 10 and b.value in (a.recipient_guid,  a.sender_guid)
                left join `' . $tableName . '`.order_content c on a.uuid = c.merc_uuid COLLATE utf8_unicode_ci
                where 
                b.org in (' . $strOrgIds . ')
                order by coalesce(ort, a.date_doc) desc, order_id, a.date_doc desc
                ) tb 
              ) tb2 where pg=:page
             ';
        $query_params = [
            ':page'     => $page,
            ':pageSize' => $pageSize,
        ];

        foreach ($params as $key => $param) {
            if ($key == 'from') {
                $start_date = date('Y-m-d 00:00:00', strtotime($this->from));
            } elseif ($key == 'to') {
                $end_date = date('Y-m-d 23:59:59', strtotime($this->to));
            } elseif ($key == 'product_name') {
                $sql .= " and product_name LIKE :product_name";
                $query_params[':product_name'] = '%' . $this->product_name . '%';
            } elseif ($key == 'sender_guid') {
                foreach ($this->sender_guid as $item) {
                    if (!preg_match('/[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}/', $item)) {
                        return ['uuids' => [], 'groups' => []];
                    }
                }
                $sender = implode('\',\'', $this->sender_guid);
                $sql .= " and sender_guid in ('$sender') ";
            } elseif ($key == 'type') {
                $sql .= ' and type=:type';
                $query_params[':type'] = $this->type;
            } elseif ($key == 'status') {
                $sql .= ' and status=:status';
                $query_params[':status'] = $this->status;
            }
        }
        if (isset($start_date) && isset($end_date)) {
            $query_params[':start_date'] = $start_date;
            $query_params[':end_date'] = $end_date;
            $sql .= ' and (date_doc >= :start_date and date_doc <= :end_date) ' . count($query_params);
        }
        $result = \Yii::$app->db_api->createCommand($sql, $query_params)->queryAll();
        $arUuids = $arOrders = [];
        foreach ($result as $row) {
            $arUuids[] = $row['uuid'];
            if (!is_null($row['order_id'])) {
                $arOrders[$row['order_id']] = $row['order_id'];
            }
        }

        return ['uuids' => $arUuids, 'groups' => $arOrders];
    }
}