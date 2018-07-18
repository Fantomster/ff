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
use frontend\modules\clientintegr\modules\rkws\components\FullmapHelper;


// use yii\mongosoft\soapserver\Action;

/**
 * Description of FullmapController
 * Controls all the actions of pre-mapping by goods catalog service
 * Author: R.Smirnov
 */

class FullmapController extends \frontend\modules\clientintegr\controllers\DefaultController {

    public function actions() {
        return ArrayHelper::merge(parent::actions(), [
            'edit' => [
                'class' => EditableColumnAction::className(),
                'modelClass' => AllMaps::className(),
                'outputValue' => function ($model, $attribute, $key, $index) {
                    $value = $model->$attribute;
                    if ($attribute === 'pdenom') {

                        if (!is_numeric($model->pdenom))
                            return '';

                        $rkProd = \api\common\models\RkProduct::findOne(['id' => $value]);
                        $model->product_rid = $rkProd->id;
                        $model->munit_rid = $rkProd->unit_rid;
                        $model->pdenom = $rkProd->denom;
                        $model->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

                        $model->save(false);

                        return $rkProd->denom;
                    }
                    return '';                                   // empty is same as $value
                },
                'outputMessage' => function($model, $attribute, $key, $index) {
                    return $model->errors;                                  // any custom error after model save
                },
            ],
            'changekoef' => [// identifier for your editable column action
                'class' => EditableColumnAction::className(), // action class name
                'modelClass' => AllMaps::className(), // the model for the record being edited
                //   'outputFormat' => ['decimal', 6],
                'outputValue' => function ($model, $attribute, $key, $index) {
                    if ($attribute === 'vat') {
                        return $model->$attribute / 100;
                    } else {
                        return round($model->$attribute, 6);      // return any custom output value if desired
                    }
                    //       return $model->$attribute;
                },
                'outputMessage' => function($model, $attribute, $key, $index) {
                    return '';                                  // any custom error to return after model save
                },
                'showModelErrors' => true, // show model validation errors after save
                'errorOptions' => ['header' => '']                // error summary HTML options

            ]
        ]);
    }

    public function actionIndex() {

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        $records =  AllMaps::find()->select('*')->andWhere('org_id = :org',["org" => $organization])
            ->andWhere('service_id = 1');

        $dataProvider = new ActiveDataProvider(['query' => $records,
            'sort' => ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

        $lic0 = Organization::getLicenseList();
        //$lic = $this->checkLic();
        $lic = $lic0['rkws'];
        $licucs = $lic0['rkws_ucs'];
        $vi = (($lic) && ($licucs)) ? 'index' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi,[
                'dataProvider' => $dataProvider,
                'lic' => $lic,
                'licucs' => $licucs,
            ]);
        } else {
            return $this->render($vi,[
                'dataProvider' => $dataProvider,
                'lic' => $lic,
                'licucs' => $licucs,
            ]);
        }

    }

    public function actionRenewcats() {

        $helper = new FullmapHelper();

        $helper->getcats();

       // return $this->redirect(['index']);
          return true;
    }

    public function actionChvat($id, $vat) {

        $model = $this->findModel($id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE all_map set vat = :vat, linked_at = now() where id = :id', [':vat' => $vat, ':id' =>$id])->execute();

        return $this->redirect(['index']);

    }

    public function actionMakevat($vat) {

        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id;

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE all_map set vat = :vat, linked_at = now() where service_id = 1 and org_id = :org',
                [':vat' => $vat, ':org' => $organization])->execute();

        return $this->redirect(['index']);
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

    public function actionAutocomplete($term = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!is_null($term)) {

            $sql = "( select id, denom as `text` from rk_product where acc = ".User::findOne(Yii::$app->user->id)->organization_id." and denom = '".$term."' )".
                " union ( select id, denom as `text` from rk_product  where acc = ".User::findOne(Yii::$app->user->id)->organization_id." and denom like '".$term."%' limit 10 )".
                "union ( select id, denom as `text` from rk_product where  acc = ".User::findOne(Yii::$app->user->id)->organization_id." and denom like '%".$term."%' limit 5 )".
                "order by case when length(trim(`text`)) = length('".$term."') then 1 else 2 end, `text`; ";

            $db = Yii::$app->db_api;
            $data = $db->createCommand($sql)->queryAll();
            $out['results'] = array_values($data);
        }

        return $out;
    }

    protected function checkLic() {

        $lic = \api\common\models\RkServicedata::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
        $t = strtotime(date('Y-m-d H:i:s',time()));

        $lic = \api\common\models\RkServicedata::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
        $t = strtotime(date('Y-m-d H:i:s',time()));

        if ($lic) {
            /*if ($t >= strtotime($lic->fd) && $t<= strtotime($lic->td) && $lic->status_id === 2 ) {*/
            $res = $lic;
            /*} else {
               $res = 0;
            }*/
        } else
            $res = 0;


        return $res ? $res : null;


        return $res ? $res : null;

    }

    protected function findModel($id) {
        if (($model = AllMaps::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
