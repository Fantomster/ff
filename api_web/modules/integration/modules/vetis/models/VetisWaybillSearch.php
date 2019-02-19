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
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\modules\vetis\helpers\VetisHelper;
use common\helpers\DBNameHelper;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;

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
        $tableName = DBNameHelper::getMainName();
        if (!empty($this->acquirer_id)) {
            if (is_array($this->acquirer_id)) {
                $strOrgIds = implode(',', $this->acquirer_id);
            } else {
                $strOrgIds = $this->acquirer_id;
            }
            $arOrgIds = $this->acquirer_id;
        } else {
            $orgIds = (new UserWebApi())->getUserOrganizationBusinessList('id');
            $strOrgIds = implode(',', array_keys($orgIds['result']));
            $arOrgIds = array_keys($orgIds['result']);
        }
        $enterpriseGuides = implode('\',\'', (new VetisHelper())->getEnterpriseGuids($arOrgIds));

        $queryParams = [
            ':page'     => $page,
            ':pageSize' => $pageSize,
        ];

        $arWhereAndCount = $this->generateWhereStatementAndCount($params, $strOrgIds, $queryParams);
        $mercPconst = $arWhereAndCount['merc_pconst'] ?? 'a.recipient_guid, a.sender_guid';

        $vsdDirection = 'case when a.recipient_guid in (\'' . $enterpriseGuides . '\') and a.sender_guid not in (\'' . $enterpriseGuides . '\') then \'incoming\'
                     else \'outgoing\'
                end vsd_direction';

        if (isset($params['type'])) {
            if ($params['type'] == 'INCOMING') {
                $vsdDirection = 'case when a.recipient_guid in (\'' . $enterpriseGuides . '\') then \'incoming\'
                     else \'outgoing\'
                end vsd_direction';
            } elseif ($params['type'] == 'OUTGOING') {
                $vsdDirection = 'case when a.sender_guid in (\'' . $enterpriseGuides . '\') then \'outgoing\'
                     else \'incoming\'
                end vsd_direction';
            }
        }

        $sql = 'SELECT * FROM (
                SELECT 
                     @page := case 
                               when (@row >= (@page_size * @page + @offset)) and
                                    ((@prev_order_id is null) or (@prev_order_id is not null and @prev_order_id != coalesce(ord_id, -1)))
                               then @page + 1
                               else @page
                              end pg,
                     @row := @row + 1 rn,
                     @offset := case 
                               when (@row >= @page_size * @page + @offset) 
                               then (@row - @page_size * @page)
                               else @offset := 0
                              end offsett, 
                     @prev_order_id := ord_id,
                     tb.*
                FROM (
                SELECT DISTINCT 
                a.uuid, 
                a.date_doc,
                a.sender_name,
                a.last_update_date,
                a.amount,
                a.unit,
                a.production_date,
                a.product_name,
                a.sender_guid,
                a.status,
                a.type,
                a.last_error,
                a.user_status,
                a.r13nClause,
                a.location_prosperity,
                o.id ord_id,
                o.created_at,
                o.total_price,
                c.id oc_id,
                vendor.name vendor_name,
                ' . $vsdDirection . ',
                case when c.id is not null then 
                  (
                  select max(date_doc) 
                    from  merc_vsd aa,
                          ' . $tableName . '.order_content ab 
                    where ab.order_id = c.order_id
                      and aa.uuid = ab.merc_uuid
                  )
                else null end ort
                
                FROM (SELECT @row := 0, @page_size := :pageSize, @page := 0, @offset := 0, @prev_order_id := NULL) x,
                       merc_vsd a
                left join integration_setting i on i.name=\'enterprise_guid\'
                join integration_setting_value b on b.setting_id = i.id and b.value in (' . $mercPconst . ')
                left join ' . $tableName . '.order_content c on a.uuid = c.merc_uuid
                left join ' . $tableName . '.order o on o.id = c.order_id
                left join ' . $tableName . '.organization vendor on o.vendor_id = vendor.id

                where 
                b.org_id in (' . $strOrgIds . ') ' . $arWhereAndCount['sql'] . '
                order by coalesce(ort, a.date_doc) desc, ord_id, a.date_doc desc
                ) tb 
              ) tb2 where pg=:page
             ';

        $result = \Yii::$app->db_api->createCommand($sql, $queryParams)->queryAll();
        $arItems = $arGroups = $arContentCount = [];
        foreach ($result as $row) {
            $arItems[] = [
                'uuid'                => $row['uuid'],
                'document_id'         => $row['ord_id'],
                'product_name'        => $row['product_name'],
                'sender_name'         => $row['sender_name'],
                'status'              => $row['status'],
                'status_text'         => MercVsd::$statuses[$row['status']],
                'status_date'         => WebApiHelper::asDatetime($row['last_update_date']),
                'amount'              => $row['amount'],
                'unit'                => $row['unit'],
                'production_date'     => $row['production_date'],
                'date_doc'            => WebApiHelper::asDatetime($row['date_doc']),
                'vsd_direction'       => $row['vsd_direction'],
                'last_error'          => $row['last_error'],
                'user_status'         => $row['user_status'],
                'r13nClause'          => (bool)$row['r13nClause'],
                'location_prosperity' => (bool)!MercVsd::parsingLocationProsperity($row['location_prosperity']),
            ];
            if (!is_null($row['ord_id'])) {
                $status = $arGroups[$row['ord_id']]['statuses'] ?? '';
                $arContentCount[$row['ord_id']][] = $row['oc_id'];
                $arGroups[$row['ord_id']] = [
                    'count'       => count(array_unique($arContentCount[$row['ord_id']])),
                    'created_at'  => $row['created_at'],
                    'total_price' => $row['total_price'],
                    'vendor_name' => $row['vendor_name'],
                    'statuses'    => $status ? $status . ',' . $row['status'] : $row['status'],
                ];
            }
        }
        foreach ($arGroups as $ordId => $group) {
            $arGroups[$ordId]['statuses'] = (new VetisHelper())->getStatusForGroup($group['statuses']);
        }

        return [
            'items'   => $arItems,
            'groups'  => $arGroups,
            'count'   => ceil($arWhereAndCount['count'] / $pageSize),
            'org_ids' => $arOrgIds,
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
            } elseif ($key == 'status' && $this->status != 'all') {
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
        $count = MercVsd::find()->distinct()
            ->leftJoin(IntegrationSetting::tableName() . ' i', 'i.name=\'enterprise_guid\'')
            ->leftJoin(IntegrationSettingValue::tableName() . ' b', 'b.setting_id = i.id and b.value in (' . $pConstForCount . ')')
            ->where(array_merge(['b.org_id' => explode(',', $strOrgIds)], $arCount)
            );
        if ($between) {
            $count->andWhere($between);
        }
        $count = $count->count();

        return ['sql' => $sql, 'count' => $count, 'merc_pconst' => $mercPconst ? 'a.' . $mercPconst : null];
    }
}
