<?php

namespace console\controllers;

use Yii;
use yii\web\View;
use yii\console\Controller;
use common\models\Organization;
use common\models\Request;
//`php yii cron/count`
class RequestCronController extends Controller {
    public function actionMassSendMail() {
          //Получаем вчерашний день с 00:00 по 23:59:59
          $start_date = date("Y-m-d", strtotime("yesterday")) . " 00:00:00";
          $end_date = date("Y-m-d", strtotime("yesterday")) . " 23:59:59";
          //Выбираем все актуальные заявки за вчерашний день
          $requests = Request::find()->where(['between', 'created_at', $start_date, $end_date])
                  ->andWhere(['active_status'=>  Request::ACTIVE])
                  ->andWhere('responsible_supp_org_id is null and created_at<=end')
                  ->all();
          //выход, если ничего нет
          if(empty($requests)){ 
          return; 
          }
          //собираем массив менеджеров организаций подходящих под категории заявок
          $sql = "SELECT distinct(u.id) as user_id,u.email as email,
p.full_name as full_name from (
select supp_org_id,mpc.parent as category_id
from catalog_base_goods cbg 
join mp_category mpc on cbg.category_id = mpc.id
where deleted = 0 group by supp_org_id, mpc.parent)tb 
right join (SELECT category FROM request 
WHERE (created_at BETWEEN '{$start_date}' AND '{$end_date}') AND 
(active_status=1) AND 
(responsible_supp_org_id is null and created_at<=end))tt
on tb.category_id = tt.category
join user u on supp_org_id = u.organization_id
join profile p on u.id = p.user_id
order by u.organization_id LIMIT 4";//LIMIT на боевом убрать
        $vendors = \Yii::$app->db->createCommand($sql)->queryAll();
        //выход, если ничего нет
        if(empty($vendors)){
          return; 
        }
        // на этом этапе совершаем рассылку по списку манагеров
        foreach($vendors as $vendor){
            $mailer = \Yii::$app->mailer;
            //$email = 'marshal1209448@gmail.com'; //для тестов, на боевом заменить
            $email = $vendor['email'];
            $subject = "MixCart.ru - заявки для Вас";
            $mailer->htmlLayout = 'layouts/request';
            $result = $mailer->compose('requestPull', compact("requests","vendor"))
                        ->setTo($email)->setSubject($subject)->send();
            
        } 
        
    }
}
