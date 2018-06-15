<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use api\common\models\AllMaps;
use common\models\CatalogBaseGoods;
use common\models\OrderContent;
use Yii;
use yii\web\Controller;
use api\common\models\RkWaybill;
use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use api\common\models\RkWaybilldata;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use common\models\Organization;
use common\models\Order;
use yii\helpers\Url;


// use yii\mongosoft\soapserver\Action;

/**
 * Description of FullmapController
 * Controls all the actions of pre-mapping by goods catalog service
 * Author: R.Smirnov
 */

class FullmapController extends \frontend\modules\clientintegr\controllers\DefaultController {


    public function actionIndex() {

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        $records =  AllMaps::find()->select('*')->andWhere('org_id = :org',["org" => $organization])
            ->andWhere('service_id = 1');

        $dataProvider = new ActiveDataProvider(['query' => $records,
            'sort' => ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

        $lic = $this->checkLic();
        $vi = $lic ? 'index' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->render($vi, [
                'dataProvider' => $dataProvider,
            ]);
        }


        /*

        $records = RkWaybilldata::find()->select('rk_waybill_data.*, rk_product.denom as pdenom ')->andWhere(['waybill_id' => $waybill_id])->leftJoin('rk_product', 'rk_product.id = product_rid');

        $wmodel = RkWaybill::find()->andWhere('id= :id',[':id' => $waybill_id])->one();

        // Используем определение браузера и платформы для лечения бага с клавиатурой Android с помощью USER_AGENT (YT SUP-3)

        $platform = $userAgent->platform;
        $browser = $userAgent->browser;

        if (stristr($platform,'android') OR stristr($browser,'android')) {
            $isAndroid = true;
        } else $isAndroid = false;

        if(!$wmodel) {
            echo "Cant find wmodel in map controller";
            die();
        }

        $dataProvider = new ActiveDataProvider(['query' => $records,
            'sort' => ['defaultOrder' => ['munit_rid' => SORT_ASC]],
        ]);

        $lic = $this->checkLic();
        $vi = $lic ? 'indexmap' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                'dataProvider' => $dataProvider,
                'wmodel' => $wmodel,
                'isAndroid' => $isAndroid,
            ]);
        } else {
            return $this->render($vi, [
                'dataProvider' => $dataProvider,
                'wmodel' => $wmodel,
                'isAndroid' => $isAndroid,
            ]);
        }
        */
    }

    public function getLastUrl() {

        $lastUrl = Url::previous();
        $lastUrl = substr($lastUrl, strpos($lastUrl,"/clientintegr"));

        $lastUrl = $this->deleteGET($lastUrl,'way');

        if(!strpos($lastUrl,"?")) {
            $lastUrl .= "?";
        } else {
            $lastUrl .= "&";
        }
        return $lastUrl;
    }

    public function deleteGET($url, $name, $amp = true) {
        $url = str_replace("&amp;", "&", $url); // Заменяем сущности на амперсанд, если требуется
        list($url_part, $qs_part) = array_pad(explode("?", $url), 2, ""); // Разбиваем URL на 2 части: до знака ? и после
        parse_str($qs_part, $qs_vars); // Разбиваем строку с запросом на массив с параметрами и их значениями
        unset($qs_vars[$name]); // Удаляем необходимый параметр
        if (count($qs_vars) > 0) { // Если есть параметры
            $url = $url_part."?".http_build_query($qs_vars); // Собираем URL обратно
            if ($amp) $url = str_replace("&", "&amp;", $url); // Заменяем амперсанды обратно на сущности, если требуется
        }
        else $url = $url_part; // Если параметров не осталось, то просто берём всё, что идёт до знака ?
        return $url; // Возвращаем итоговый URL
    }

    protected function checkLic() {

        $lic = \api\common\models\RkServicedata::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
        $t = strtotime(date('Y-m-d H:i:s',time()));

        if ($lic) {
            if ($t >= strtotime($lic->fd) && $t<= strtotime($lic->td) && $lic->status_id === 2 ) {
                $res = $lic;
            } else {
                $res = 0;
            }
        } else
            $res = 0;


        return $res ? $res : null;

    }

}
