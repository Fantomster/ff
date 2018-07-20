<?php

namespace frontend\modules\clientintegr\modules\odinsobsh\controllers;

use api\common\models\one_s\OneSGood;
use api\common\models\one_s\OneSPconst;
use api\common\models\OneSWaybillDataSearch;
use common\models\Organization;
use common\models\search\OrderSearch;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use Yii;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use yii\web\NotFoundHttpException;
use api\common\models\one_s\OneSProduct;
use api\common\models\one_s\OneSService;
use api\common\models\one_s\OneSWaybill;
use api\common\models\one_s\OneSWaybillData;
use yii\web\Response;
use yii\helpers\Url;
use api\common\models\iikoWaybillDataSearch;


class WaybillController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    /**
     * @return array
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'edit' => [
                'class' => EditableColumnAction::className(),
                'modelClass' => OneSWaybillData::className(),
                'outputValue' => function ($model, $attribute) {
                    $value = $model->$attribute;
                    if ($attribute === 'pdenom') {
                        if (is_numeric($model->pdenom)) {
                            $rkProd = OneSGood::findOne(['id' => $value]);
                            $model->product_rid = $rkProd->id;
                            $model->munit = $rkProd->measure;
                            //$model->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                            $model->save(false);
                            return $rkProd->name;
                            return '';
                        }
                    }
                    return '';
                },
                'outputMessage' => function () {
                    return '';
                },
            ],
            'change-coefficient' => [
                'class' => EditableColumnAction::className(),
                'modelClass' => OneSWaybillData::className(),
                'outputValue' => function ($model, $attribute) {
                    if ($attribute === 'vat') {
                        return $model->$attribute / 100;
                    } else {
                        //$model->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                        $model->save(false);
                        return round($model->$attribute, 6);
                    }
                },
                'outputMessage' => function () {
                    return '';
                },
                'showModelErrors' => true,
                'errorOptions' => ['header' => '']
            ]
        ]);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $way = Yii::$app->request->get('way',0);
        Url::remember();
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->searchWaybill(Yii::$app->request->queryParams);
       // $dataProvider->pagination->pageSize=3;


        $lic = OneSService::getLicense();
        $view = $lic ? 'index' : '/default/_nolic';
        $organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id);
        $params = [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'lic' => $lic,
            //'visible' =>OneSPconst::getSettingsColumn(Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id),
            'way' => $way,
            'organization' => $organization,
        ];

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }

    /**
     * @param $waybill_id
     * @return string
     */
    public function actionMap()
    {
        $model = OneSWaybill::findOne(Yii::$app->request->get('waybill_id'));
        if (!$model) {
            die("Cant find wmodel in map controller");
        }

        // Используем определение браузера и платформы для лечения бага с клавиатурой Android с помощью USER_AGENT (YT SUP-3)
        $userAgent = \xj\ua\UserAgent::model();

        /* @var \xj\ua\UserAgent $userAgent */
        $platform = $userAgent->platform;
        $browser = $userAgent->browser;
        $isAndroid = false;
        if (stristr($platform, 'android') OR stristr($browser, 'android')) {
            $isAndroid = true;
        }

        $searchModel = new OneSWaybillDataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        $lic = OneSService::getLicense();
        $view = $lic ? 'indexmap' : '/default/_nolic';
        $params = [
            'dataProvider' => $dataProvider,
            'wmodel' => $model,
            'isAndroid' => $isAndroid,
        ];

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }

    /**
     * @return mixed
     */
    public function actionChangeVat()
    {
        $checked = Yii::$app->request->post('key');

        $arr = explode(",", $checked);
        $wbill_id = $arr[1];
        $is_checked = $arr[0];

        $wmodel = oneSWaybill::findOne($wbill_id);

        if (!$wmodel) {
            die('Waybill model is not found');
        }

        if ($is_checked) { // Добавляем НДС
            $sql = "UPDATE one_s_waybill_data SET sum=round(sum/(vat/10000+1),2) WHERE waybill_id = :w_id";
            $vat = 1;
        } else { // Убираем НДС
            $sql = "UPDATE one_s_waybill_data SET sum=defsum WHERE waybill_id = :w_id";
            $vat = 0;

        }

        $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $wmodel->id])->execute();

        $wmodel->vat_included = $vat;
        if (!$wmodel->save()) {
            die('Cant save wmodel where vat = ' . $wmodel->vat_included);
        }

        return $result;
    }

    /**
     * @param $id
     * @return \yii\web\Response
     */
    public function actionClearData($id)
    {
        $model = $this->findDataModel($id);
        $model->quant = $model->defquant;
        $model->koef = 1;

        $wayModel = oneSWaybill::findOne($model->waybill_id);
        if (!$wayModel) {
            die("Cant find wmodel in map controller cleardata");
        }

        if ($wayModel->vat_included) {
            $model->sum = round($model->defsum / (1 + $model->vat / 10000), 2);
        } else {
            $model->sum = $model->defsum;
        }

        if (!$model->save()) {
            var_dump($model->getErrors());
            exit;
        }

        return $this->redirect(['map', 'waybill_id' => $wayModel->id]);
    }

    /**
     * @param null $term
     * @return mixed
     */
    public function actionAutoComplete($term = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!is_null($term)) {
       /*     $query = new \yii\db\Query;
            $query->select(['id' => 'id', 'text' => 'CONCAT(`denom`," (",unit,")")'])
                ->from('iiko_product')
                ->where('org_id = :acc', [':acc' => User::findOne(Yii::$app->user->id)->organization_id])
                ->andwhere("denom like :denom ", [':denom' => '%' . $term . '%'])
                ->limit(20);

            $command = $query->createCommand();
            $command->db = Yii::$app->db_api;
            $data = $command->queryAll();
            $out['results'] = array_values($data);
       */
            $sql = "( select id, name as `text` from one_s_good where org_id = ".User::findOne(Yii::$app->user->id)->organization_id." and name = '".$term."' )".
                " union ( select id, name as `text` from one_s_good  where org_id = ".User::findOne(Yii::$app->user->id)->organization_id." and name like '".$term."%' limit 10 )".
                "union ( select id, name as `text` from one_s_good where  org_id = ".User::findOne(Yii::$app->user->id)->organization_id." and name like '%".$term."%' limit 5 )".
                "order by case when length(trim(`text`)) = length('".$term."') then 1 else 2 end, `text`; ";

            $db = Yii::$app->db_api;
            $data = $db->createCommand($sql)->queryAll();
            $out['results'] = array_values($data);
        }
        return $out;
    }

    /**
     * @param null $term
     * @param $org
     * @return mixed
     */
    public function actionAutoCompleteAgent($term = null, $org)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out['results'] = [];
        if (!is_null($term)) {
            $query = new \yii\db\Query;
            $query->select(['id' => 'id', 'text' => 'name'])
                ->from('one_s_contragent')
                ->where('org_id = :acc', [':acc' => $org])
                ->andwhere("name like :name ", [':name' => '%' . $term . '%'])
                ->limit(20);

            $command = $query->createCommand();
            $command->db = Yii::$app->db_api;
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        return $out;
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $lic = OneSService::getLicense();
        $vi = $lic ? 'update' : '/default/_nolic';
        if ($model->load(Yii::$app->request->post())) {
            if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }
            $model->save();
            return $this->redirect([$this->getLastUrl().'way='.$model->order_id]);
        } else {
            return $this->render($vi, [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param $order_id
     * @return string|\yii\web\Response
     */
    public function actionCreate($order_id)
    {
        $user = $this->currentUser;
        $ord = \common\models\Order::findOne(['id' => $order_id]);

        if (!$ord) {
            echo "Can't find order";
            die();
        }

        $model = new OneSWaybill();
        $model->order_id = $order_id;
        $model->status_id = 1;
        $model->org = $ord->client_id;
        $model->discount = $ord->discount;
        $model->discount_type = $ord->discount_type;
        $is_invoice = OneSPconst::getSettingsColumn($user->organization_id);
        $model->is_invoice = $is_invoice;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }
            return $this->redirect([$this->getLastUrl().'way='.$model->order_id]);
        } else {
            $model->num_code = $order_id;
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Отправляем накладную
     */
    public function actionSend()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $transaction = Yii::$app->db_api->beginTransaction();

        /**
            header ("Content-Type:text/xml");
            $id = Yii::$app->request->get('id');
            $model = $this->findModel($id);
            echo $model->getXmlDocument();
            exit;
        */

        $api = OneSApi::getInstance();
        try {
            if (!Yii::$app->request->isAjax) {
                throw new \Exception('Only ajax method');
            }

            $id = Yii::$app->request->post('id');
            $model = $this->findModel($id);

            if (!$model) {
                throw new \Exception('Не удалось найти накладную');
            }

            if ($api->auth()) {
                if(!$api->sendWaybill($model)) {
                    throw new \Exception('Ошибка при отправке.');
                }
                $model->status_id = 2;
                $model->save();
            } else {
                throw new \Exception('Не удалось авторизоваться');
            }
            $transaction->commit();
            $api->logout();
            return ['success' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            $api->logout();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function actionMakevat($waybill_id, $vat) {

        $model = $this->findModel($waybill_id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE one_s_waybill_data set vat = :vat where waybill_id = :id', [':vat' => $vat, ':id' =>$waybill_id])->execute();

        return $this->redirect(['map', 'waybill_id' => $model->id]);
    }


    public function actionChvat($id, $vat) {

        $model = $this->findDataModel($id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE one_s_waybill_data set vat = :vat where id = :id', [':vat' => $vat, ':id' =>$id])->execute();

        return $this->redirect(['map', 'waybill_id' => $model->waybill->id]);

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


    /**
     * @param $id
     * @return oneSWaybill
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = oneSWaybill::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return OneSWaybillData
     * @throws NotFoundHttpException
     */
    protected function findDataModel($id)
    {
        $model = OneSWaybillData::findOne($id);
        if (!empty($model)) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
