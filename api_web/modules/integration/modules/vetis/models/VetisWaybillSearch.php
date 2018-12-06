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
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use common\helpers\DBNameHelper;
use yii\web\BadRequestHttpException;

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
     * @throws \Exception
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
            if (empty($orgIds['result'])){
                //todo_refactor localization
                throw new BadRequestHttpException(\Yii::t('api_web', 'You dont have available businesses, plz add relation to organization for your user',
                    ['ru'=>'У вас нет доступных предприятий, пожалуйста добавьте привязку предприятия к вашей организации']));
            }
            $strOrgIds = array_map(function ($el) {
                return $el['id'];
            }, $orgIds['result']);
            $strOrgIds = implode(',', $strOrgIds);
        }
        $entGuids = implode('\',\'', (new VetisHelper())->getEnterpriseGuids());

        $queryParams = [
            ':page'     => $page,
            ':pageSize' => $pageSize,
        ];
        $arWhereAndCount = $this->generateWhereStatementAndCount($params, $strOrgIds, $queryParams);
        $mercPconst = $arWhereAndCount['merc_pconst'] ?? 'a.recipient_guid, a.sender_guid';

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
                case when a.recipient_guid in (\''.$entGuids.'\') and a.sender_guid not in (\''.$entGuids.'\') then \'incoming\'
                     else \'outgoing\'
                end vsd_direction,
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
                join merc_pconst b on b.const_id = 10 and b.value in (' . $mercPconst . ')
                left join `' . $tableName . '`.order_content c on a.uuid = c.merc_uuid
                where 
                b.org in (' . $strOrgIds . ') ' . $arWhereAndCount['sql'] . '
                order by coalesce(ort, a.date_doc) desc, order_id, a.date_doc desc
                ) tb 
              ) tb2 where pg=:page
             ';

        $result = \Yii::$app->db_api->createCommand($sql, $queryParams)->queryAll();
        $arIncomingOutgoing = [];
        $arUuids = $arOrders = [];
        foreach ($result as $row) {
            $arUuids[$row['uuid']] = $row['order_id'];
            if (!is_null($row['order_id'])) {
                $arOrders[$row['order_id']] = $row['order_id'];
            }
            $arIncomingOutgoing[$row['uuid']] = $row['vsd_direction'];
        }


        return [
            'uuids'    => $arUuids,
            'groups'   => $arOrders,
            'count'    => ceil($arWhereAndCount['count'] / $pageSize),
            'arIncOut' => $arIncomingOutgoing,
        ];
    }

    /**
     * Cant separate method, count depends on where
     *
     * @param $params
     * @param $strOrgIds
     * @param $queryParams
     * @return array
     */
    private function generateWhereStatementAndCount($params, $strOrgIds, &$queryParams)
    {
        $sql = '';
        $arCount = [];
        $mercPconst = null;
        foreach ($params as $key => $param) {
            if ($key == 'date') {
                $startDate = date('Y-m-d 00:00:00', strtotime($this->from));
                $endDate = date('Y-m-d 23:59:59', strtotime($this->to));
            } elseif ($key == 'product_name') {
                $sql .= " and a.product_name in (";
                for ($i = 0; $i < count($this->product_name); $i++) {
                    $sql .= ($i == 0 ? '' : ",") . ":product_name" . $i;
                    $queryParams[':product_name' . $i] = $this->product_name[$i];
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
                if ($this->type == 'INCOMING') {
                    $mercPconst = 'recipient_guid';
                } elseif ($this->type == 'OUTGOING') {
                    $mercPconst = 'sender_guid';
                }
            } elseif ($key == 'status') {
                $sql .= ' and a.status=:status';
                $queryParams[':status'] = $this->status;
                $arCount['status'] = $this->status;
            }
        }
        $between = null;
        if (isset($startDate) && isset($endDate)) {
            $queryParams[':start_date'] = $startDate;
            $queryParams[':end_date'] = $endDate;
            $sql .= ' and (a.date_doc >= :start_date and a.date_doc <= :end_date) ';
            $between = ['between', "date_doc", $startDate, $endDate];
        }

        $pConstForCount = 'merc_vsd.recipient_guid,  merc_vsd.sender_guid';
        if ($mercPconst) {
            $pConstForCount = 'merc_vsd.' . $mercPconst;
        }
        $count = MercVsd::find()->distinct()->leftJoin('merc_pconst b', 'b.const_id = 10 and b.value in ('
            . $pConstForCount . ')')->where(
            array_merge(['b.org' => explode(',', $strOrgIds)], $arCount)
        );
        if ($between) {
            $count->andWhere($between);
        }
        $count = $count->count();

        return ['sql' => $sql, 'count' => $count, 'merc_pconst' => $mercPconst ? 'a.' . $mercPconst : null];
    }
}