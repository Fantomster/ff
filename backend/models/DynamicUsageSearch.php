<?php

namespace backend\models;

use Yii;
use yii\data\SqlDataProvider;

/**
 * Description of GuideProductsSearch
 *
 * @author elbabuino
 */
class DynamicUsageSearch extends \yii\base\Model {
    
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

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['org', 'cbg_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
           'org_name' => 'Организация',
           'org_id' => 'ID',
            'org_contact_name' => 'Контактное лицо',
            'org_city' => 'Город',
            'org_email' => 'Email',
            'org_type' => 'Тип',
           'org_registred' => 'Дата регистрации',
            'franchisee_name' => 'Франчайзи',
            'franchisee_region' => 'Регион',
            'order_max_date' => 'Дата последнего заказа',
            'order_cnt' => 'Общее количество заказов',
            'w5_sum' => '',
            'w5_count' => 'Заказов 5 недель назад',
            'w5_vendor' => '',
            'w4_sum'  => '',
            'w4_count' => 'Заказов 4 недели назад',
            'w4_vendor' => '',
            'w3_sum' => '',
            'w3_count' => 'Заказов 3 недели назад',
            'w3_vendor' => '',
            'w2_sum' => '',
            'w2_count' => 'Заказов 2 недели назад',
            'w2_vendor' => '',
            'w1_sum' => '',
            'w1_count' => 'Заказов 1 неделю назад',
            'w1_vendor' => '',
        ];
    }
    
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param integer $guideId
     * @param integer $clientId
     *
     * @return SqlDataProvider
     */
    public function search(array $params) {
        $this->load($params);

        $query = "select q.*
                      from (
                    select org.*,
                           DATE_FORMAT(max(o.created_at), '%Y-%m-%d') order_max_date,
                           count(o.id) order_cnt,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 37 day) and DATE_SUB(NOW(), INTERVAL 30 day) then o.total_price else 0 end) w5_sum,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 37 day) and DATE_SUB(NOW(), INTERVAL 30 day) then 1 else 0 end) w5_count,
                           count(distinct case when o.created_at between DATE_SUB(NOW(), INTERVAL 37 day) and DATE_SUB(NOW(), INTERVAL 30 day) then o.vendor_id else null end) w5_vendor,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 31 day) and DATE_SUB(NOW(), INTERVAL 24 day) then o.total_price else 0 end) w4_sum,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 31 day) and DATE_SUB(NOW(), INTERVAL 24 day) then 1 else 0 end) w4_count,
                           count(distinct case when o.created_at between DATE_SUB(NOW(), INTERVAL 31 day) and DATE_SUB(NOW(), INTERVAL 24 day) then o.vendor_id else null end) w4_vendor,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 23 day) and DATE_SUB(NOW(), INTERVAL 16 day) then o.total_price else 0 end) w3_sum,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 23 day) and DATE_SUB(NOW(), INTERVAL 16 day) then 1 else 0 end) w3_count,
                           count(distinct case when o.created_at between DATE_SUB(NOW(), INTERVAL 23 day) and DATE_SUB(NOW(), INTERVAL 16 day) then o.vendor_id else null end) w3_vendor,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 15 day) and DATE_SUB(NOW(), INTERVAL 8 day) then o.total_price else 0 end) w2_sum,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 15 day) and DATE_SUB(NOW(), INTERVAL 8 day) then 1 else 0 end) w2_count,
                           count(distinct case when o.created_at between DATE_SUB(NOW(), INTERVAL 15 day) and DATE_SUB(NOW(), INTERVAL 8 day) then o.vendor_id else null end) w2_vendor,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 7 day) and NOW() then o.total_price else 0 end) w1_sum,
                           sum(case when o.created_at between DATE_SUB(NOW(), INTERVAL 7 day) and NOW() then 1 else 0 end) w1_count,
                           count(distinct case when o.created_at between DATE_SUB(NOW(), INTERVAL 7 day) and NOW() then o.vendor_id else null end) w1_vendor
                    from (select a.name org_name, a.contact_name org_contact_name, a.city org_city, a.email org_email, a.id org_id,
                                   case a.type_id 
                                     when 1 then 'Ресторан'
                                     when 2 then 'Поставщик'
                                     else 'Неизвестный'
                                   end org_type,
                                   a.type_id org_type_id,
                                   DATE_FORMAT(a.created_at,'%Y-%m-%d') org_registred,
                                   DATE_FORMAT(a.created_at,'%Y-%m') org_registred_peroiod,
                                   case when c.id in (1, 2, 34) then 'MixCart Москва'
                                        when c.id is null then 'MixCart n/a'
                                        else c.legal_entity
                                   end franchisee_name,
                                   case when c.id in (1, 2, 34) or c.id is null then 'Москва'
                                        else 'Регионы'
                                   end franchisee_region
                            from organization a
                            left join franchisee_associate b on a.id = b.organization_id
                            left join franchisee c on b.franchisee_id = c.id
                            where a.blacklisted = 0) as org
                        left join (select id, client_id org_id, vendor_id, created_at, total_price from `order`
                                   union all
                                   select id, vendor_id, vendor_id, created_at, total_price from `order`
                            ) o on org.org_id = o.org_id
                        group by org_name, org_contact_name, org_city, org_email, org_id, org_type, org_registred,
                    org_registred_peroiod, franchisee_name, franchisee_region) as q
                    order by case when order_cnt > 0 then 1 else 2 end, franchisee_region, org_type_id,
                             case when w1_count=0 and w2_count=0 and w3_count=0 and w4_count=0 and w5_count>0 then 1 else 2 end,
                             case when w1_count=0 and w2_count=0 and w3_count=0 and (w4_count>0 or w5_count>0) then 1 else 2 end,
                             case when w1_count=0 and w2_count=0 and (w3_count>0 or w4_count>0 or w5_count>0) then 1 else 2 end,
                             case when w1_count=0 and (w2_count>0 or w3_count>0 or w4_count>0 or w5_count>0) then 1 else 2 end,
                             case when w1_count>0 and w2_count>0 and w3_count>0 and w4_count>0 and w5_count>0 then 1 else 2 end,
                             case when w1_count=0 and w2_count=0 and w3_count=0 and w4_count=0 and w5_count=0 then 1 else 2 end,  
                             case when order_cnt > 1 then 1 when order_cnt > 0 then 2 else 3 end,
                             order_max_date desc,
                             order_cnt desc
                ";

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => [
                'page' => isset($params['page']) ? ($params['page']-1) : 0,
                'pageSize' => 20,],
        ]);
        
        return $dataProvider;
    }
}
