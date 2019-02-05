<?php

namespace frontend\modules\clientintegr\modules\tillypad\controllers;

use api\common\models\iiko\iikoAgent;
use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use api\common\models\VatData;
use api\common\models\iiko\iikoStore;
use common\models\Order;
use common\models\Organization;
use frontend\modules\clientintegr\modules\tillypad\helpers\TillypadApi;
use Yii;
use common\models\User;
use api\common\models\tillypad\TillypadService;
use api\common\models\iiko\iikoWaybill;
use yii\web\Response;
use yii\helpers\Url;
use api\common\models\iikoWaybillDataSearch;
use common\models\search\OrderSearch2;
use common\components\SearchOrdersComponent;
use api_web\components\Registry;
use yii\web\NotFoundHttpException;
use api\common\models\iiko\iikoWaybillData;

class WaybillController extends \frontend\modules\clientintegr\modules\iiko\controllers\WaybillController
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

    public function actionEditNew()
    {
        $page_size = 20;

        $product_rid = Yii::$app->request->post('id');
        $number = Yii::$app->request->post('number');
        $button = Yii::$app->request->post('button');
        $vatf = Yii::$app->request->post('vatf');
        $sort = Yii::$app->request->post('sort');
        $page0 = Yii::$app->request->post('page');
        $temp1 = explode('-', $page0);
        $temp2 = explode('<', $temp1[1]);
        $last_row = $temp2[0];
        $ostatok = $last_row % $page_size;
        if ($ostatok == 0) {
            $page = $last_row / $page_size;
        } else {
            $page = intdiv($last_row, $page_size) + 1;
        }

        $waybill_data = iikoWaybillData::find()->where('id = :w_id', [':w_id' => $number])->one();
        $waybill_id = $waybill_data->waybill_id;
        $product_id = $waybill_data->product_id;
        $org_id = $waybill_data->org;
        $vat = $waybill_data->vat;
        $koef = $waybill_data->koef;

        $product = iikoProduct::find()->where('id = :w_id', [':w_id' => $product_rid])->one();
        $munit = $product->unit;

        $waybill_data->product_rid = $product_rid;
        $waybill_data->munit = $munit;
        $waybill_data->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        if (!$waybill_data->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной Tillypad не удалось.']));
        }

        $kolvo_nesopost = iikoWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $waybill_id])->andWhere(['product_rid' => null])->count();
        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $waybill = iikoWaybill::find()->where('id = :w_wid', [':w_wid' => $waybill_id])->one();
        $agent_uuid = $waybill->agent_uuid;
        $num_code = $waybill->num_code;
        $text_code = $waybill->text_code;
        $store_id = $waybill->store_id;
        if (($agent_uuid === null) or ($num_code === null) or ($text_code === null) or ($store_id === null)) {
            $shapka = 0;
        } else {
            $shapka = 1;
        }
        if ($kolvo_nesopost == 0) {
            if ($shapka == 1) {
                $waybill->readytoexport = 1;
                $waybill->status_id = 4;
            } else {
                $waybill->readytoexport = 0;
                $waybill->status_id = 1;
            }
        } else {
            if ($shapka == 1) {
                $waybill->readytoexport = 0;
                $waybill->status_id = 1;
            } else {
                $waybill->readytoexport = 0;
            }
        }
        if (!$waybill->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную Tillypad не удалось.']));
        }

        if ($button == 'forever') {
            $existence = AllMaps::find()->where(['service_id' => Registry::TILLYPAD_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
            if ($existence == 0) {
                $position = new AllMaps();
                $position->service_id = Registry::TILLYPAD_SERVICE_ID;
                $position->org_id = $org_id;
                $position->product_id = $product_id;
                $position->supp_id = $supp_id;
                $position->serviceproduct_id = $product_rid;
                $position->store_rid = $store_id;
                $position->koef = $koef;
                $position->vat = $vat;
                $position->is_active = 1;
            } else {
                $position = AllMaps::find()->where(['service_id' => Registry::TILLYPAD_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
                $position->serviceproduct_id = $product_rid;
            }
            $position->unit_rid = null;
            $position->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            if (!$position->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
            }
        }
        return $munit;
    }

    public function actionChangeCoefficientNew()
    {
        $page_size = 20;
        $est = 0;
        $i = 0;
        $massiv_post = Yii::$app->request->post('iikoWaybillData');
        while ($est == 0) {
            if (isset($massiv_post[$i]["koef"])) {
                $koef_old = $massiv_post[$i]["koef"];
                $est = 1;
            }
            $i++;
        }
        $koef = str_replace(',', '.', $koef_old);
        $koef = round($koef, 6);
        $buttons = $massiv_post["koef_buttons"];
        $koef_id = Yii::$app->request->post('editableKey');
        $querys = $massiv_post["querys"];

        $waybill_data = iikoWaybillData::find()->where('id = :w_id', [':w_id' => $koef_id])->one();
        $waybill_id = $waybill_data->waybill_id;
        $product_id = $waybill_data->product_id;
        $product_rid = $waybill_data->product_rid;
        $org_id = $waybill_data->org;
        $vat = $waybill_data->vat;
        $koef_old = $waybill_data->koef;
        $quant_old = $waybill_data->quant;

        if ($koef == 0) {
            $koef = $koef_old;
        }
        $quant_new = $quant_old * ($koef / $koef_old);
        $quant_new = round($quant_new, 4);

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $waybill_data->quant = $quant_new;
        $waybill_data->koef = $koef;
        if (!$waybill_data->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной Tillypad не удалось.']));
        }
        if ($buttons == 'forever') {
            $existence = AllMaps::find()->where(['service_id' => Registry::TILLYPAD_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
            if ($existence == 0) {
                $waybill = iikoWaybill::find()->where('id = :w_wid', [':w_wid' => $waybill_id])->one();
                $store = $waybill->store_id;
                $position = new AllMaps();
                $position->service_id = Registry::TILLYPAD_SERVICE_ID;
                $position->org_id = $org_id;
                $position->product_id = $product_id;
                $position->supp_id = $supp_id;
                $position->serviceproduct_id = $product_rid;
                $position->store_rid = $store;
                $position->koef = $koef;
                $position->vat = $vat;
                $position->is_active = 1;
                if (!(is_null($product_rid))) {
                    $position->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                }
            } else {
                $position = AllMaps::find()->where(['service_id' => Registry::TILLYPAD_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
                $position->koef = $koef;
                $position->vat = $vat;
            }
            $position->unit_rid = null;
            if (!$position->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
            }
            $temp0 = explode('+', $querys);
            $sort = $temp0[1];
            $temp1 = explode('-', $temp0[0]);
            $temp2 = explode('<', $temp1[1]);
            $last_row = $temp2[0];
            $ostatok = $last_row % $page_size;
            $vat_filter = $temp0[2];
            if ($ostatok == 0) {
                $page = $last_row / $page_size;
            } else {
                $page = intdiv($last_row, $page_size) + 1;
            }
            return $this->redirect(['map', 'waybill_id' => $waybill_id, 'way' => $koef_id, 'sort' => $sort, 'iikoWaybillDataSearch[vat]' => $vat_filter, 'page' => $page]);
        }
        return $koef;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {

        $organization = $this->currentUser->organization;

        Url::remember();

        //  $page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 0;
        //  $perPage = Yii::$app->request->get('per-page') ? Yii::$app->request->get('per-page') : 0;
        //  $dataProvider->pagination->pageSize=3;

        /** @var array $wbStatuses Статусы заказов в соответствии со статусами привязанных к ним накладных!
         * Статусы накладных в таблице iiko_waybill_status */
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
            throw new NotFoundHttpException(Yii::t('error', 'api.access.denied', ['ru' => 'Доступ запрещён.']));
        }
        $search = new SearchOrdersComponent();
        $search->getRestaurantIntegration(SearchOrdersComponent::INTEGRATION_TYPE_IIKO, $searchModel,
            $organization->id, $this->currentUser->organization_id, $wbStatuses, ['pageSize' => 20],
            ['defaultOrder' => ['id' => SORT_DESC]]);
        $lisences = $organization->getLicenseList();
        if (isset($lisences['tillypad']) && $lisences['tillypad']) {
            $lisences = $lisences['tillypad'];
            $view = 'index';
        } else {
            $view = '/default/_nolic';
            $lisences = null;
        }

        $renderParams = [
            'searchModel'  => $searchModel,
            'affiliated'   => $search->affiliated,
            'dataProvider' => $search->dataProvider,
            'searchParams' => $search->searchParams,
            'businessType' => $search->businessType,
            'lic'          => $lisences,
            'visible'      => iikoPconst::getSettingsColumn($organization->id),
            'wbStatuses'   => $wbStatuses,
            'way'          => Yii::$app->request->get('way', 0),
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
    public function actionMap()
    {
        $model = iikoWaybill::findOne(Yii::$app->request->get('waybill_id'));
        $vatData = VatData::getVatList();
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.waybill.not.find', ['ru' => 'Приходной накладной Tillypad с таким номером не существует.']));
        }

        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']);

        // Используем определение браузера и платформы для лечения бага с клавиатурой Android с помощью USER_AGENT (YT SUP-3)
        $userAgent = \xj\ua\UserAgent::model();

        /* @var \xj\ua\UserAgent $userAgent */
        $platform = $userAgent->platform;
        $browser = $userAgent->browser;
        $isAndroid = false;
        if (stristr($platform, 'android') OR stristr($browser, 'android')) {
            $isAndroid = true;
        }

        $searchModel = new iikoWaybillDataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $agentModel = iikoAgent::findOne(['uuid' => $model->agent_uuid, 'org_id' => $model->org]);
        $storeModel = iikoStore::findOne(['id' => $model->store_id]);

        $lic = TillypadService::getLicense();
        $view = $lic ? 'indexmap' : '/default/_nolic';
        $vatFilter = [];
        $vatFilter["vat"] = 1;
        $params = [
            'dataProvider'          => $dataProvider,
            'wmodel'                => $model,
            'agentName'             => isset($agentModel->denom) ? $agentModel->denom : 'Не указан',
            'storeName'             => isset($storeModel->denom) ? $storeModel->denom : 'Не указан',
            'isAndroid'             => $isAndroid,
            'searchModel'           => $searchModel,
            'vatData'               => $vatData,
            'parentBusinessId'      => $obConstModel->getPconstValue(),
            'iikoWaybillDataSearch' => $vatFilter,
        ];

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($view, $params);
        } else {
            return $this->render($view, $params);
        }
    }

    /**
     * @param $id
     * @param $page
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id, $page)
    {
        $model = $this->findModel($id);
        $lic = TillypadService::getLicense();
        $vi = $lic ? 'update' : '/default/_nolic';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $existingWaybill = iikoWaybill::find()->where(['order_id' => $model->order_id, 'store_id' => $model->store_id])->andWhere(['!=', 'id', $id])->one();
            if (!empty($existingWaybill)) {
                $model = iikoWaybill::moveContentToExistingWaybill($model, $existingWaybill);
            }

            if ($model->agent_uuid == '') {
                $model->agent_uuid = null;
            }
            if ($model->store_id == 0) {
                $model->store_id = null;
            }
            $kolvo_nesopost = iikoWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $model->id])->andWhere(['product_rid' => null])->count();
            if (($model->agent_uuid === null) or ($model->num_code === null) or ($model->text_code === null) or ($model->store_id === null)) {
                $shapka = 0;
            } else {
                $shapka = 1;
            }
            if ($kolvo_nesopost == 0) {
                if ($shapka == 1) {
                    $model->readytoexport = 1;
                    $model->status_id = 4;
                } else {
                    $model->readytoexport = 0;
                    $model->status_id = 1;
                }
            } else {
                if ($shapka == 1) {
                    $model->readytoexport = 0;
                    $model->status_id = 1;
                } else {
                    $model->readytoexport = 0;
                }
            }

            $model->doc_date = Yii::$app->formatter->asDate(Yii::$app->formatter->asDate($model->doc_date, 'php:Y-m-d') . ' 16:00:00', 'php:Y-m-d H:i:s');
            $model->payment_delay_date = Yii::$app->formatter->asDate(Yii::$app->formatter->asDate($model->payment_delay_date, 'php:Y-m-d') . ' 16:00:00', 'php:Y-m-d H:i:s');
            if (!$model->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную Tillypad не удалось.']));
            }
            return $this->redirect(['/clientintegr/tillypad/waybill/index', 'way' => $model->order_id, 'page' => $page]);

        } else {
            return $this->render($vi, [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param $order_id
     * @param $page
     * @return string|\yii\web\Response
     */
    public function actionCreate($order_id, $page)
    {
        $ord = \common\models\Order::findOne(['id' => $order_id]);

        if (!$ord) {
            throw new NotFoundHttpException(Yii::t('error', 'api.controllers.order.not.find', ['ru' => 'Заказа с таким номером не существует.']));
        }

        $model = new iikoWaybill();
        $model->setScenario('handMade');
        $model->order_id = $order_id;
        $model->status_id = 1;
        $model->org = $ord->client_id;
        $model->service_id = Registry::TILLYPAD_SERVICE_ID;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->doc_date = Yii::$app->formatter->asDate($model->doc_date . ' 16:00:00', 'php:Y-m-d H:i:s');
            $model->payment_delay_date = Yii::$app->formatter->asDate($model->payment_delay_date . ' 16:00:00', 'php:Y-m-d H:i:s');
            if (!$model->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную Tillypad не удалось.']));
            } else {
                $model->createWaybillData();
            }
            $kolvo_nesopost = iikoWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $model->id])->andWhere(['product_rid' => null])->count();
            if (($model->agent_uuid === null) or ($model->num_code === null) or ($model->text_code === null) or ($model->store_id === null)) {
                $shapka = 0;
            } else {
                $shapka = 1;
            }
            if ($kolvo_nesopost == 0) {
                if ($shapka == 1) {
                    $model->readytoexport = 1;
                    $model->status_id = 4;
                } else {
                    $model->readytoexport = 0;
                    $model->status_id = 1;
                }
            } else {
                if ($shapka == 1) {
                    $model->readytoexport = 0;
                    $model->status_id = 1;
                } else {
                    $model->readytoexport = 0;
                }
            }
            if (!$model->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную Tillypad не удалось.']));
            }
            return $this->redirect(['/clientintegr/tillypad/waybill/index', 'page' => $page, 'way' => $model->order_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Отправляем накладную
     *
     * @var $id int|null
     * @return array
     */
    public function actionSend($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $transaction = Yii::$app->db_api->beginTransaction();

        /**
         * header ("Content-Type:text/xml");
         * $id = Yii::$app->request->get('id');
         * $model = $this->findModel($id);
         * echo $model->getXmlDocument();
         * exit;
         */

        $api = TillypadApi::getInstance();
        try {
            if (!Yii::$app->request->isAjax) {
                throw new NotFoundHttpException(Yii::t('error', 'api.controllers.method.not.ajax', ['ru' => 'Способ отправки должен быть только AJAX.']));
            }

            if (is_null($id)) {
                $id = Yii::$app->request->post('id');
            }
            $model = $this->findModel($id);

            if (!$model) {
                throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.waybill.not.find', ['ru' => 'Приходной накладной Tillypad с таким номером не существует.']));
            }

            if ($api->auth()) {
                $response = $api->sendWaybill($model);
                if ($response !== true) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.waybill.not.send', ['ru' => 'Приходную накладную Tillypad не удалось выгрузить.']));
                }
                $model->status_id = 2;
                if (!$model->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную Tillypad не удалось.']));
                }
            } else {
                throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.not.auth', ['ru' => 'Не удалось авторизоваться на сервере Tillypad.']));
            }
            $transaction->commit();
            $api->logout();
            return ['success' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            $api->logout();
            \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     *  Отправка нескольких накладных
     */
    public function actionMultiSend()
    {
        $ids = Yii::$app->request->post('ids');
        $scsCount = 0;
        foreach ($ids as $id) {
            $transaction = Yii::$app->db_api->beginTransaction();
            try {
                $model = $this->findModel($id);
                //Выставляем статус отправляется
                $model->status_id = 3;
                if (!$model->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную Tillypad не удалось.']));
                }
                $res = $this->actionSend($id);
                if ($res['success'] === true) {
                    $scsCount++;
                } else {
                    throw new \Exception($res['error']);
                }
                $transaction->commit();
            } catch (\Throwable $e) {
                //Выставляем статус обратно
                $transaction->rollBack();
                \Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }
        if (count($ids) == $scsCount) {
            return ['success' => true, 'count' => $scsCount];
        }
        return ['success' => false, 'error' => 'Выгружено только ' . $scsCount . ' накладных'];
    }

    /**
     * Отправляем накладную
     */
    public function actionSendByButton()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $transaction = Yii::$app->db_api->beginTransaction();

        /**
         * header ("Content-Type:text/xml");
         * $id = Yii::$app->request->get('id');
         * $model = $this->findModel($id);
         * echo $model->getXmlDocument();
         * exit;
         */

        $api = TillypadApi::getInstance();
        try {
            if (!Yii::$app->request->isAjax) {
                throw new NotFoundHttpException(Yii::t('error', 'api.controllers.method.not.ajax', ['ru' => 'Способ отправки должен быть только AJAX.']));
            }

            $id = Yii::$app->request->post('id');
            $model = $this->findModel($id);

            if (!$model) {
                throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.waybill.not.find', ['ru' => 'Приходной накладной Tillypad с таким номером не существует.']));
            }

            if ($model->readytoexport == 0) {
                throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.waybill.not.ready', ['ru' => 'Приходная накладная Tillypad к выгрузке не готова.']));
            }

            if ($api->auth()) {
                $response = $api->sendWaybill($model);
                if ($response !== true) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.waybill.not.send', ['ru' => 'Приходную накладную Tillypad не удалось выгрузить.']));
                }
                $model->status_id = 2;
                if (!$model->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную Tillypad не удалось.']));
                }
            } else {
                throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.not.auth', ['ru' => 'Не удалось авторизоваться на сервере Tillypad.']));
            }
            $transaction->commit();
            $api->logout();
            Yii::$app->session->set("iiko_waybill", $model->order_id);
            return ['success' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            $api->logout();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function actionMakevat($waybill_id, $vat, $vatf, $sort, $page) //меняет ставку НДС для всех товаров в накладной, используя фильтр НДС
    {
        $page_size = 20;
        $model = $this->findModel($waybill_id);

        if ($vatf == 1) {
            $iiko_waybill_datas = iikoWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $waybill_id])->all();
        } else {
            $iiko_waybill_datas = iikoWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $waybill_id])->andWhere(['vat' => $vatf])->all();
        }

        if ($page != 'undefined') {
            $page_not_parsing = $page;
            $temp1 = explode('-', $page_not_parsing);
            $temp2 = explode('<', $temp1[1]);
            $last_row = $temp2[0];
            $ostatok = $last_row % $page_size;
            if ($ostatok == 0) {
                $page = $last_row / $page_size;
            } else {
                $page = intdiv($last_row, $page_size) + 1;
            }
        } else {
            $page = 1;
        }

        if (count($iiko_waybill_datas) > 0) {
            foreach ($iiko_waybill_datas as $iiko_waybill_data) {
                $product_id = $iiko_waybill_data->product_id;
                $product_rid = $iiko_waybill_data->product_rid;
                $org_id = $iiko_waybill_data->org;
                $koef = $iiko_waybill_data->koef;

                $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

                $existence = AllMaps::find()->where(['service_id' => Registry::TILLYPAD_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
                if ($existence == 0) {
                    $waybill = iikoWaybill::find()->where('id = :w_wid', [':w_wid' => $waybill_id])->one();
                    $store = $waybill->store_id;
                    $position = new AllMaps();
                    $position->service_id = Registry::TILLYPAD_SERVICE_ID;
                    $position->org_id = $org_id;
                    $position->product_id = $product_id;
                    $position->supp_id = $supp_id;
                    $position->serviceproduct_id = $product_rid;
                    $position->store_rid = $store;
                    $position->koef = $koef;
                    $position->vat = $vat;
                    $position->is_active = 1;
                    if (!(is_null($product_rid))) {
                        $position->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                    }
                } else {
                    $position = AllMaps::find()->where(['service_id' => Registry::TILLYPAD_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
                    $position->vat = $vat;
                }
                $position->unit_rid = null;
                if (!$position->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
                }
            }
        }

        return $this->redirect(['map', 'waybill_id' => $model->id, 'way' => 0, 'iikoWaybillDataSearch[vat]' => $vatf, 'sort' => $sort, 'page' => $page]);
    }

    public function actionChvat($id, $koef, $vatf, $sort = 'fproductnameProduct', $vat, $page, $way) //меняет ставку НДС для конкретного товара
    {
        $waybill_data = $this->findDataModel($id);

        $waybill_data->vat = $vat;
        if (!$waybill_data->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной Tillypad не удалось.']));
        }

        $product_id = $waybill_data->product_id;
        $product_rid = $waybill_data->product_rid;
        $org_id = $waybill_data->org;

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $existence = AllMaps::find()->where(['service_id' => Registry::TILLYPAD_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
        if ($existence == 0) {
            $waybill = iikoWaybill::find()->where(['id' => $waybill_data->waybill_id])->one();
            $store = $waybill->store_id;
            $position = new AllMaps();
            $position->service_id = Registry::TILLYPAD_SERVICE_ID;
            $position->org_id = $org_id;
            $position->product_id = $product_id;
            $position->supp_id = $supp_id;
            $position->serviceproduct_id = $product_rid;
            $position->store_rid = $store;
            $position->koef = $koef;
            $position->vat = $vat;
            $position->is_active = 1;
            if (!(is_null($product_rid))) {
                $position->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
            }
        } else {
            $position = AllMaps::find()->where(['service_id' => Registry::TILLYPAD_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
            $position->vat = $vat;
        }
        $position->unit_rid = null;
        if (!$position->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
        }
        return $this->redirect(['map', 'waybill_id' => $waybill_data->waybill->id, 'page' => $page, 'way' => $way, 'iikoWaybillDataSearch[vat]' => $vatf, 'sort' => $sort]);
    }

    public function actionEditGlobal()
    {
        $product_rid = Yii::$app->request->post('id');
        $number = Yii::$app->request->post('number');
        $org_id = User::findOne(Yii::$app->user->id)->organization_id;

        $product = iikoProduct::find()->where('id = :w_id', [':w_id' => $product_rid])->one();
        $munit = $product->unit;

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($number);

        $existence = AllMaps::fine()->where(['service_id' => Registry::TILLYPAD_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $number])->one();
        if (!$existence) {
            $position = new AllMaps();
            $position->service_id = Registry::TILLYPAD_SERVICE_ID;
            $position->org_id = $org_id;
            $position->product_id = $number;
            $position->supp_id = $supp_id;
            $position->serviceproduct_id = $product_rid;
            $position->store_rid = null;
            $position->koef = 1;
            $position->vat = null;
            $position->is_active = 1;
            $position->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        } else {
            $position = AllMaps::find()->where(['id' => $existence->id])->one();
            $position->serviceproduct_id = $product_rid;
            if ($existence->koef === null) {
                $existence->koef = 1.0000;
            }
            $position->koef = $existence->koef;
            $position->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        }
        $position->unit_rid = null;
        if (!$position->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
        }

        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']);
        $arChildsModels = iikoPconst::find()->select('org')->where(['const_id' => $obConstModel->id, 'value' => $org_id])->all();

        $idorgs[0] = $org_id;
        $i = 0;
        if ($arChildsModels) {
            foreach ($arChildsModels as $child) {
                $i++;
                $idorgs[$i] = $child->org;
            }
        }

        $orders = Order::find()->where(['vendor_id' => $supp_id, 'client_id' => $idorgs])->all();
        foreach ($orders as $order) {
            $waybills = iikoWaybill::find()->where(['order_id' => $order->id, 'status_id' => 1])->all();
            foreach ($waybills as $waybill) {
                $waybill_datas = iikoWaybillData::find()->where(['waybill_data' => $waybill->id, 'product_id' => $number, 'product_rid' => null])->all();
                foreach ($waybill_datas as $waybill_data) {
                    $waybill_data->product_rid = $product_rid;
                    $waybill_data->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:i:s');
                    if (!$waybill_data->save()) {
                        throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной Tillypad не удалось.']));
                    }
                }
            }
        }
        return $munit;
    }

    /**
     * @param null $term
     * @return mixed
     */
    public function actionAutoCompleteNew($term = null)
    {
        $term = Yii::$app->request->post('stroka');

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($term == '') {
            $term = null;
        }
        $out = [];
        if (!is_null($term)) {
            $orgId = User::findOne(Yii::$app->user->id)->organization_id;
            $constId = iikoDicconst::findOne(['denom' => 'main_org']);
            $parentId = iikoPconst::findOne(['const_id' => $constId->id, 'org' => $orgId]);

            $organizationID = (isset($parentId, $parentId->value) && strlen((int)$parentId->value) ==
                strlen($parentId->value) && $parentId->value > 0) ? $parentId->value : $orgId;

            $arr = ArrayHelper::map(iikoSelectedProduct::find()->where(['organization_id' => $orgId])->all(), 'id', 'product_id');

            $query2 = (new Query())
                ->select([
                    "id"    => "id",
                    "denom" => "denom",
                    "unit"  => "unit"
                ])
                ->from('iiko_product')
                ->where(['is_active' => 1])
                ->andWhere(['org_id' => $organizationID])
                ->andWhere("denom LIKE :term", [':term' => $term . '%'])
                ->orderBy(['denom' => SORT_ASC, "unit" => SORT_ASC])
                ->limit(15);

            $query3 = (new Query())
                ->select([
                    "id"    => "id",
                    "denom" => "denom",
                    "unit"  => "unit"
                ])
                ->from('iiko_product')
                ->where(['is_active' => 1])
                ->andWhere(['org_id' => $organizationID])
                ->andWhere("denom LIKE :term", [':term' => $term . '%'])
                ->orderBy(['denom' => SORT_ASC, "unit" => SORT_ASC])
                ->limit(15);

            $query3 = (new Query())
                ->select([
                    "id"    => "id",
                    "denom" => "denom",
                    "unit"  => "unit"
                ])
                ->from('iiko_product')
                ->where(['is_active' => 1])
                ->andWhere(['org_id' => $organizationID])
                ->andWhere("denom LIKE :term", [':term' => '%' . $term . '%'])
                ->orderBy(['denom' => SORT_ASC, "unit" => SORT_ASC])
                ->limit(10);

            $query1 = (new Query())
                ->select([
                    "id"    => "id",
                    "denom" => "denom",
                    "unit"  => "unit",
                ])
                ->from('iiko_product')
                ->union($query2)
                ->union($query3)
                ->where(['is_active' => 1])
                ->andWhere(['org_id' => $organizationID])
                ->andWhere(['denom' => ':term'], [':term' => $term])
                ->orderBy(['denom' => SORT_ASC, "unit" => SORT_ASC])
                ->limit(10);

            $query = (new Query())
                ->select([
                    "id"  => "id",
                    "txt" => "CONCAT(denom, ' (' ,unit, ')')",
                ])
                ->from("(" . $query1->createCommand()->getRawSql() . ") t");
            if (count($arr)) {
                $query->andWhere(['id' => $arr]);
            }
            $result = $query->all(\Yii::$app->get('db_api'));
            $out = array_values($result);

        } else {
            $orgId = User::findOne(Yii::$app->user->id)->organization_id;
            $constId = iikoDicconst::findOne(['denom' => 'main_org']);
            $parentId = iikoPconst::findOne(['const_id' => $constId->id, 'org' => $orgId]);
            $organizationID = !is_null($parentId) ? $parentId->value : $orgId;

            $arr = ArrayHelper::map(iikoSelectedProduct::find()->where(['organization_id' => $organizationID])->all(), 'id', 'product_id');

            $query = (new Query())
                ->select([
                    "id"  => "id",
                    "txt" => "CONCAT(denom, ' (' ,unit, ')')"
                ])
                ->from('iiko_product')
                ->where(['is_active' => 1])
                ->andWhere(['org_id' => $organizationID])
                ->orderBy(['txt' => SORT_ASC])
                ->limit(100);
            if (count($arr)) {
                $query->andWhere(['id' => $arr]);
            }
            $result = $query->all(\Yii::$app->get('db_api'));
            $out = array_values($result);
        }
        return $out;
    }
}
