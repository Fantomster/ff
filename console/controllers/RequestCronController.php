<?php

namespace console\controllers;

use common\models\CatalogBaseGoods;
use common\models\MpCategory;
use common\models\Profile;
use common\models\User;
use yii\db\Expression;
use yii\db\Query;
use yii\console\Controller;
use common\models\Request;

//`php yii cron/count`
class RequestCronController extends Controller
{
    public function actionMassSendMail()
    {
        //Получаем вчерашний день с 00:00 по 23:59:59
        $start_date = date("Y-m-d", strtotime("yesterday")) . " 00:00:00";
        $end_date = date("Y-m-d", strtotime("yesterday")) . " 23:59:59";
        //Выбираем все актуальные заявки за вчерашний день
        $requests = Request::find()
            ->where([
                'between',
                'created_at',
                $start_date,
                $end_date
            ])
            ->andWhere(['active_status' => Request::ACTIVE])
            ->andWhere('responsible_supp_org_id is null and created_at<=end')
            ->all();
        //выход, если ничего нет
        if (empty($requests)) {
            return;
        }
        //собираем массив менеджеров организаций подходящих под категории заявок
        $vendors = (new Query())
            ->select([
                "user_id"   => new Expression("DISTINCT(u.id)"),
                "email"     => "u.email",
                "full_name" => "p.full_name"
            ])
            ->from([
                "tb" => (new Query())
                    ->select([
                        "supp_org_id",
                        "category_id" => "mpc.parent"
                    ])
                    ->from(["cbg" => CatalogBaseGoods::tableName()])
                    ->join("JOIN", ["mpc" => MpCategory::tableName()], "cbg.category_id = mpc.id")
                    ->where(["deleted" => 0])
                    ->groupBy([
                        "supp_org_id",
                        "mpc.parent"
                    ])
            ])
            ->rightJoin([
                "tt" => (new Query())
                    ->select("category")
                    ->from(Request::tableName())
                    ->where([
                        "BETWEEN",
                        "created_at",
                        new Expression($start_date),
                        new Expression($end_date)
                    ])
                    ->andWhere([
                        "AND",
                        ["active_status" => 1],
                        ["IS", "responsible_supp_org_id", null],
                        ["<=", "created_at", new Expression("end")]
                    ])
            ], "tb.category_id = tt.category")
            ->join("JOIN", ["u" => User::tableName()], "supp_org_id = u.organization_id")
            ->join("JOIN", ["p" => Profile::tableName()], "u.id = p.user_id")
            ->orderBy("u.organization_id")
            ->limit(4)//LIMIT на боевом убрать
            ->all();

        //выход, если ничего нет
        if (empty($vendors)) {
            return;
        }
        // на этом этапе совершаем рассылку по списку манагеров
        foreach ($vendors as $vendor) {
            $mailer = \Yii::$app->mailer;
            //$email = 'marshal1209448@gmail.com'; //для тестов, на боевом заменить
            $email = $vendor['email'];
            $subject = "MixCart.ru - заявки для Вас";
            $mailer->htmlLayout = 'layouts/request';
            $result = $mailer->compose('requestPull', compact("requests", "vendor"))
                ->setTo($email)->setSubject($subject)->send();

        }

    }
}
