<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use api\common\models\RkAgent;
use api\common\models\RkPconst;
use api\common\models\RkStore;
use api\common\models\rkws\RkWaybilldataSearch;
use api\common\models\VatData;
use common\models\CatalogBaseGoods;
use common\models\OrderContent;
use Yii;
use api\common\models\RkWaybill;
use api\common\models\RkWaybilldata;
use common\models\User;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use common\models\Organization;
use common\models\Order;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use common\models\search\OrderSearch2;
use yii\web\BadRequestHttpException;
use common\components\SearchOrdersComponent;

class WaybillController extends \frontend\modules\clientintegr\controllers\DefaultController
{


    /** @var string Все заказы без учета привязанных к ним накладных */
    const ORDER_STATUS_ALL_DEFINEDBY_WB_STATUS = 'allstat';
    /** @var string Все заказы, по которым вообще нет накладных */
    const ORDER_STATUS_NODOC_DEFINEDBY_WB_STATUS = 'nodoc';
    /** @var string Все заказы, по которым есть накладные не подходящие под критерии ready и completed */
    const ORDER_STATUS_FILLED_DEFINEDBY_WB_STATUS = 'filled';
    /** @var string Все заказы, по которым есть накладные со статусом 5 и readytoexport > 0 */
    const ORDER_STATUS_READY_DEFINEDBY_WB_STATUS = 'ready';
    /** @var string Все заказы, по которым накладные в процессе обработки !!! настоящее время не используется */
    const ORDER_STATUS_OUTGOING_DEFINEDBY_WB_STATUS = 'outgoing';
    /** @var string Все заказы, по которым есть накладные со статусом 2 */
    const ORDER_STATUS_COMPLETED_DEFINEDBY_WB_STATUS = 'completed';

    /**
     * @return array
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'edit' => [// identifier for your editable action
                'class' => EditableColumnAction::className(), // action class name
                'modelClass' => RkWaybilldata::className(), // the update model class
                'outputValue' => function ($model, $attribute, $key, $index) {
                    $value = $model->$attribute;                 // your attribute value
                    if ($attribute === 'pdenom') {

                        if (!is_numeric($model->pdenom))
                            return '';

                        $rkProd = \api\common\models\RkProduct::findOne(['id' => $value]);
                        $model->product_rid = $rkProd->id;
                        $model->munit_rid = $rkProd->unit_rid;
                        $model->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');

                        //   $model->koef = 1.8;
                        $model->save(false);
                        return $rkProd->denom;       // return formatted value if desired
                    }
                    return '';                                   // empty is same as $value
                },
                'outputMessage' => function ($model, $attribute, $key, $index) {
                    return '';                                  // any custom error after model save
                },
                // 'showModelErrors' => true,                     // show model errors after save
                // 'errorOptions' => ['header' => '']             // error summary HTML options
                // 'postOnly' => true,
                // 'ajaxOnly' => true,
                // 'findModel' => function($id, $action) {},
                // 'checkAccess' => function($action, $model) {}
            ],
            'changekoef' => [// identifier for your editable column action
                'class' => EditableColumnAction::className(), // action class name
                'modelClass' => RkWaybilldata::className(), // the model for the record being edited
                //   'outputFormat' => ['decimal', 6],
                'outputValue' => function ($model, $attribute, $key, $index) {
                    if ($attribute === 'vat') {
                        return $model->$attribute / 100;
                    } else {
                        return round($model->$attribute, 6);      // return any custom output value if desired
                    }
                    //       return $model->$attribute;
                },
                'outputMessage' => function ($model, $attribute, $key, $index) {
                    return '';                                  // any custom error to return after model save
                },
                'showModelErrors' => true, // show model validation errors after save
                'errorOptions' => ['header' => '']                // error summary HTML options
                // 'postOnly' => true,
                // 'ajaxOnly' => true,
                // 'findModel' => function($id, $action) {},
                // 'checkAccess' => function($action, $model) {}
            ]
        ]);
    }


    public function actionIndex()
    {

        $organization = $this->currentUser->organization;

        Url::remember();

        //  $page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 0;
        //  $perPage = Yii::$app->request->get('per-page') ? Yii::$app->request->get('per-page') : 0;
        //  $dataProvider->pagination->pageSize=3;

        /** @var array $wbStatuses Статусы заказов в соответствии со статусами привязанных к ним накладных!
         * Статусы накладных в таблице rk_waybillstatus - используются, но не все */
        $wbStatuses = [
            self::ORDER_STATUS_ALL_DEFINEDBY_WB_STATUS,
            self::ORDER_STATUS_READY_DEFINEDBY_WB_STATUS,
            self::ORDER_STATUS_NODOC_DEFINEDBY_WB_STATUS,
            self::ORDER_STATUS_FILLED_DEFINEDBY_WB_STATUS,
            // self::ORDER_STATUS_OUTGOING_DEFINEDBY_WB_STATUS,
            self::ORDER_STATUS_COMPLETED_DEFINEDBY_WB_STATUS,
        ];

        $searchModel = new OrderSearch2();
        $searchModel->prepareDates(Yii::$app->formatter->asTime($organization->getEarliestOrderDate(), "php:d.m.Y"));

        if ($organization->type_id != Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Access denied');
        }
        $search = new SearchOrdersComponent();
        $search->getRestaurantIntegration('rkws', $searchModel, $organization->id, $this->currentUser->organization_id,
            $wbStatuses, ['pageSize' => 20], ['defaultOrder' => ['id' => SORT_DESC]]);
        $lisences = $organization->getLicenseList();

        $view = ($lisences['rkws'] && $lisences['rkws_ucs']) ? 'index' : '/default/_nolic';
        $renderParams = [
            'searchModel' => $searchModel,
            'affiliated' => $search->affiliated,
            'dataProvider' => $search->dataProvider,
            'searchParams' => $search->searchParams,
            'businessType' => $search->businessType,
            'lic' => $lisences['rkws'],
            'licucs' => $lisences['rkws_ucs'],
            'visible' => RkPconst::getSettingsColumn($organization->id),
            'wbStatuses' => $wbStatuses,
            'way' => Yii::$app->request->get('way', 0),
        ];

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $renderParams);
        } else {
            return $this->render($view, $renderParams);
        }

    }


    /**
     * @param $waybill_id
     * @return string
     */
    public function actionMap($waybill_id)
    {
        $wmodel = RkWaybill::find()->andWhere('id= :id', [':id' => $waybill_id])->one();
        $vatData = VatData::getVatList();

        // Используем определение браузера и платформы для лечения бага с клавиатурой Android с помощью USER_AGENT (YT SUP-3)

        $userAgent = \xj\ua\UserAgent::model();
        /* @var \xj\ua\UserAgent $userAgent */

        $platform = $userAgent->platform;
        $browser = $userAgent->browser;

        if (stristr($platform, 'android') OR stristr($browser, 'android')) {
            $isAndroid = true;
        } else $isAndroid = false;

        if (!$wmodel) {
            echo "Cant find wmodel in map controller";
            die();
        }

        $searchModel = new RkWaybilldataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $agentModel = RkAgent::findOne(['rid' => $wmodel->corr_rid, 'acc' => $wmodel->org]);
        $storeModel = RkStore::findOne(['rid' => $wmodel->store_rid]);

        $lic = $this->checkLic();
        $vi = $lic ? 'indexmap' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'wmodel' => $wmodel,
                'agentName' => $agentModel['denom'],
                'storeName' => $storeModel['denom'],
                'isAndroid' => $isAndroid,
                'vatData' => $vatData
            ]);
        } else {
            return $this->render($vi, [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'wmodel' => $wmodel,
                'agentName' => $agentModel['denom'],
                'storeName' => $storeModel['denom'],
                'isAndroid' => $isAndroid,
                'vatData' => $vatData
            ]);
        }
    }

    /**
     * @return string
     */
    public function actionGetpopover()
    {
        $id = Yii::$app->request->post('key');
        $goodCount = OrderContent::find()->andWhere('order_id = :id', ['id' => $id])->count('*');
        $listIds = OrderContent::find()->select('product_id')->andWhere('order_id = :id', ['id' => $id])->limit(10)->asArray()->all();

        foreach ($listIds as $ids) {
            foreach ($ids as $key => $value) {
                $fList[] = $value;
            }
        }

        $listGoods = CatalogBaseGoods::find()->select('product')->andWhere(['IN', 'id', $fList])->asArray()->all();
        $result = "";
        $ind = 1;

        foreach ($listGoods as $ids) {
            foreach ($ids as $key => $value) {
                $result .= $ind++ . ')&nbsp;' . $value . "<br>";
            }
        }

        if ($goodCount > 10) {
            $result .= "и другие...";
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function actionChangevat()
    {
        $checked = Yii::$app->request->post('key');
        $arr = explode(",", $checked);
        $wbill_id = $arr[1];
        $is_checked = $arr[0];
        $wmodel = RkWaybill::find()->andWhere('id = :acc', [':acc' => $wbill_id])->one();

        if (!$wmodel) {
            die('Waybill model is not found');
        }

        if ($is_checked) { // Добавляем НДС
            $rress = Yii::$app->db_api
                ->createCommand('UPDATE rk_waybill_data SET sum=round(sum/(vat/10000+1),2) WHERE waybill_id = :acc', [':acc' => $wbill_id])
                ->execute();

            $wmodel->vat_included = 1;
            if (!$wmodel->save()) {
                die('Cant save wmodel where vat = 1');
            }
        } else { // Убираем НДС
            $rress = Yii::$app->db_api
                ->createCommand('UPDATE rk_waybill_data SET sum=defsum WHERE waybill_id = :acc', [':acc' => $wbill_id])
                ->execute();

            $wmodel->vat_included = 0;
            if (!$wmodel->save()) {
                die('Cant save wmodel where vat = 0');
            }
        }

        if ($rress) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     */
    public function actionCleardata($id)
    {
        $model = $this->findDataModel($id);
        $model->quant = $model->defquant;
        $model->koef = 1;
        $wmodel = RkWaybill::find()->andWhere('id= :id', [':id' => $model->waybill_id])->one();
        if (!$wmodel) {
            echo "Cant find wmodel in map controller cleardata";
            die();
        }

        if ($wmodel->vat_included) {
            $model->sum = round($model->defsum / (1 + $model->vat / 10000), 2);
        } else {
            $model->sum = $model->defsum;
        }

        if (!$model->save()) {
            echo $model->getErrors();
            die();
        }

        return $this->redirect(['map', 'waybill_id' => $model->waybill->id]);
    }

    /**
     * @param $waybill_id
     * @param $vat
     * @return \yii\web\Response
     */
    public function actionMakevat($waybill_id, $vat)
    {
        $model = $this->findModel($waybill_id);

        Yii::$app->db_api
            ->createCommand('UPDATE rk_waybill_data SET vat = :vat, linked_at = now() WHERE waybill_id = :id', [':vat' => $vat, ':id' => $waybill_id])
            ->execute();

        return $this->redirect(['map', 'waybill_id' => $model->id]);
    }

    /**
     * @param $id
     * @param $vat
     * @return \yii\web\Response
     */
    public function actionChvat($id, $vat)
    {
        $model = $this->findDataModel($id);
        $model->vat = $vat;
        $model->save();
        return $this->redirect(['map', 'waybill_id' => $model->waybill->id]);
    }

    /**
     * @param null $term
     * @return mixed
     */
    public function actionAutocomplete($term = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out['results'] = [];

        if (!is_null($term)) {
            $organization_id = User::findOne(Yii::$app->user->id)->organization_id;

            $sql = "( select id, CONCAT(`denom`, '(' ,unitname, ')') as `text` from rk_product where acc = " . $organization_id . " and denom = '" . $term . "' )" .
                "union ( select id, CONCAT(`denom`, '(' ,unitname, ')') as `text` from rk_product  where acc = " . $organization_id . " and denom like '" . $term . "%' limit 15 )" .
                "union ( select id, CONCAT(`denom`, '(' ,unitname, ')') as `text` from rk_product where  acc = " . $organization_id . " and denom like '%" . $term . "%' limit 10 )" .
                "order by case when length(trim(`text`)) = length('" . $term . "') then 1 else 2 end, `text`; ";

            $data = Yii::$app->get('db_api')->createCommand($sql)->queryAll();
            $out['results'] = array_values($data);
        }

        return $out;
    }

    /**
     * @param null $term
     * @param $org
     * @return mixed
     */
    public function actionAutocompleteagent($term = null, $org)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out['results'] = [];
        if (!is_null($term)) {
            $query = new \yii\db\Query;
            $query->select(['id' => 'rid', 'text' => 'denom'])
                ->from('rk_agent')
                ->where('acc = :acc', [':acc' => $org])
                ->andwhere("denom like :denom ", [':denom' => '%' . $term . '%'])
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
        $lic = $this->checkLic();
        $vi = $lic ? 'update' : '/default/_nolic';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }
            return $this->redirect([$this->getLastUrl() . 'way=' . $model->order_id]);
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
        $ord = \common\models\Order::findOne(['id' => $order_id]);
        if (!$ord) {
            echo "Can't find order";
            die();
        }

        $model = new RkWaybill();
        $model->order_id = $order_id;
        $model->status_id = 1;
        $model->org = $ord->client_id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }
            return $this->redirect([$this->getLastUrl() . 'way=' . $model->order_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @return bool|mixed|null|string
     */
    public function getLastUrl()
    {
        $lastUrl = Url::previous();
        $lastUrl = substr($lastUrl, strpos($lastUrl, "/clientintegr"));
        $lastUrl = $this->deleteGET($lastUrl, 'way');

        if (!strpos($lastUrl, "?")) {
            $lastUrl .= "?";
        } else {
            $lastUrl .= "&";
        }
        return $lastUrl;
    }

    /**
     * @param $url
     * @param $name
     * @param bool $amp
     * @return mixed|string
     */
    public function deleteGET($url, $name, $amp = true)
    {
        $url = str_replace("&amp;", "&", $url); // Заменяем сущности на амперсанд, если требуется
        list($url_part, $qs_part) = array_pad(explode("?", $url), 2, ""); // Разбиваем URL на 2 части: до знака ? и после
        parse_str($qs_part, $qs_vars); // Разбиваем строку с запросом на массив с параметрами и их значениями
        unset($qs_vars[$name]); // Удаляем необходимый параметр

        if (count($qs_vars) > 0) { // Если есть параметры
            $url = $url_part . "?" . http_build_query($qs_vars); // Собираем URL обратно
            if ($amp) {
                $url = str_replace("&", "&amp;", $url); // Заменяем амперсанды обратно на сущности, если требуется
            }
        } else {
            $url = $url_part; // Если параметров не осталось, то просто берём всё, что идёт до знака ?
        }
        return $url; // Возвращаем итоговый URL
    }

    /**
     * @param null $waybill_id
     * @return string
     */
    public function actionSendws($waybill_id = null)
    {
        if (is_null($waybill_id)) {
            $waybill_id = Yii::$app->request->post('id');
        }

        $res = new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper();
        $res->sendWaybill($waybill_id);

        return 'true';
    }

    /**
     *  Отправка нескольких накладных
     */
    public function actionMultiSend()
    {
        $ids = Yii::$app->request->post('ids');
        $succesCnt = 0;
        foreach ($ids as $id) {
            $res = $this->actionSendws($id);
            if ($res === 'true') {
                $succesCnt++;
            }
        }
        return ['success' => true, 'count' => $succesCnt];
    }

    /**
     * Отправляем накладную по нажатию кнопки при соспоставлении товаров
     */
    public function actionSendwsByButton()
    {
        /**
         * header ("Content-Type:text/xml");
         * $id = Yii::$app->request->get('id');
         * $model = $this->findModel($id);
         * echo $model->getXmlDocument();
         * exit;
         */

        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        $error = '';

        if (!$model) {
            $error .= 'Не удалось найти накладную. ';
        }

        if ($model->readytoexport == 0) {
            $error .= 'Не все товары сопоставлены! ';
        }

        if ($error == '') {
            $res = new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper();
            $res->sendWaybill($id);
            $model = $this->findModel($id);
            if ($model->status_id != 2) $error .= 'Ошибка при отправке. ';
        }

        if ($error == '') {
            Yii::$app->session->set("rkws_waybill", $model->order_id);
            return 'true';
        } else {
            return $error;
        }
    }

    /**
     * @return \api\common\models\RkServicedata|array|int|null|\yii\db\ActiveRecord
     */
    protected function checkLic()
    {
        $lic = \api\common\models\RkServicedata::find()->andWhere('org = :org', ['org' => Yii::$app->user->identity->organization_id])->one();
        $res = 0;
        if ($lic) {
            $res = $lic;
        }
        return $res ?? null;
    }

    /**
     * @param $id
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = RkWaybill::findOne($id);
        if ($model) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findDataModel($id)
    {
        $model = RkWaybilldata::findOne($id);
        if ($model) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $org_id
     * @return mixed|null|string
     */
    protected function getEarliestOrder($org_id)
    {
        $eDate = Order::find()->andWhere(['client_id' => $org_id])->orderBy('updated_at ASC')->one();
        return isset($eDate) ? $eDate->updated_at : null;
    }
}
