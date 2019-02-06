<?php

namespace frontend\modules\clientintegr\modules\odinsobsh\controllers;

use api\common\models\one_s\OneSContragent;
use api\common\models\one_s\OneSGood;
use api\common\models\one_s\OneSPconst;
use api\common\models\one_s\OneSStore;
use api\common\models\OneSWaybillDataSearch;
use api\common\models\VatData;
use common\models\Order;
use common\models\Organization;
use Yii;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use yii\web\NotFoundHttpException;
use api\common\models\one_s\OneSService;
use api\common\models\one_s\OneSWaybill;
use api\common\models\one_s\OneSWaybillData;
use yii\web\Response;
use yii\helpers\Url;
use common\models\search\OrderSearch2;
use yii\web\BadRequestHttpException;
use common\components\SearchOrdersComponent;
use api_web\components\Registry;
use api\common\models\AllMaps;
use yii\db\Query;

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
            'edit'               => [
                'class'         => EditableColumnAction::className(),
                'modelClass'    => OneSWaybillData::className(),
                'outputValue'   => function ($model, $attribute) {
                    $value = $model->$attribute;
                    if ($attribute === 'pdenom') {
                        if (is_numeric($model->pdenom)) {
                            $rkProd = OneSGood::findOne(['id' => $value]);
                            $model->product_rid = $rkProd->id;
                            $model->munit = $rkProd->measure;
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
                'class'           => EditableColumnAction::className(),
                'modelClass'      => OneSWaybillData::className(),
                'outputValue'     => function ($model, $attribute) {
                    if ($attribute === 'vat') {
                        return $model->$attribute / 100;
                    } else {
                        $model->save(false);
                        return round($model->$attribute, 6);
                    }
                },
                'outputMessage'   => function () {
                    return '';
                },
                'showModelErrors' => true,
                'errorOptions'    => ['header' => '']
            ]
        ]);
    }

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

        $waybill_data = OneSWaybillData::find()->where('id = :w_id', [':w_id' => $number])->one();
        $waybill_id = $waybill_data->waybill_id;
        $product_id = $waybill_data->product_id;
        $org_id = $waybill_data->org;
        $vat = $waybill_data->vat;
        $koef = $waybill_data->koef;

        $product = OneSGood::find()->where('id = :w_id', [':w_id' => $product_rid])->one();
        $munit = $product->measure;

        $waybill_data->product_rid = $product_rid;
        $waybill_data->munit = $munit;
        if (!$waybill_data->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной 1С не удалось.']));
        }

        $kolvo_nesopost = OneSWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $waybill_id])->andWhere(['product_rid' => null])->count();
        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $waybill = OneSWaybill::find()->where('id = :w_wid', [':w_wid' => $waybill_id])->one();
        $agent_uuid = $waybill->agent_uuid;
        $num_code = $waybill->num_code;
        $store_id = $waybill->store_id;
        if (($agent_uuid === null) or ($num_code === null) or ($store_id === null)) {
            $shapka = 0;
        } else {
            $shapka = 1;
        }

        if ($kolvo_nesopost == 0) {
            if ($shapka == 1) {
                $waybill->readytoexport = 1;
                $waybill->status_id = 3;
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
            throw new NotFoundHttpException(Yii::t('error', 'api.tillypad.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную 1С не удалось.']));
        }

        if ($button == 'forever') {
            $existence = AllMaps::find()->where(['service_id' => Registry::ONE_S_CLIENT_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
            if ($existence == 0) {
                $position = new AllMaps();
                $position->service_id = Registry::ONE_S_CLIENT_SERVICE_ID;
                $position->org_id = $org_id;
                $position->product_id = $product_id;
                $position->supp_id = $supp_id;
                $position->serviceproduct_id = $product_rid;
                $position->store_rid = $store_id;
                $position->koef = $koef;
                $position->vat = $vat;
                $position->is_active = 1;
            } else {
                $position = AllMaps::find()->where(['service_id' => Registry::ONE_S_CLIENT_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
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
        $massiv_post = Yii::$app->request->post('OneSWaybillData');
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

        $waybill_data = OneSWaybillData::find()->where('id = :w_id', [':w_id' => $koef_id])->one();
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
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной 1С не удалось.']));
        }
        if ($buttons == 'forever') {
            $existence = AllMaps::find()->where(['service_id' => Registry::ONE_S_CLIENT_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
            if ($existence == 0) {
                $waybill = OneSWaybill::find()->where('id = :w_wid', [':w_wid' => $waybill_id])->one();
                $store = $waybill->store_id;
                $position = new AllMaps();
                $position->service_id = Registry::ONE_S_CLIENT_SERVICE_ID;
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
                $position = AllMaps::find()->where(['service_id' => Registry::ONE_S_CLIENT_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
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
            return $this->redirect(['map', 'waybill_id' => $waybill_id, 'way' => $koef_id, 'sort' => $sort, 'OneSWaybillDataSearch[vat]' => $vat_filter, 'page' => $page]);
        }
        return $koef;
    }

    /**
     * Default web-site url `.../clientintegr/odinsobsh/waybill` action
     *
     * @throws BadRequestHttpException
     * @return string
     */
    public function actionIndex(): string
    {

        $organization = $this->currentUser->organization;

        Url::remember();

        //  $page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : 0;
        //  $perPage = Yii::$app->request->get('per-page') ? Yii::$app->request->get('per-page') : 0;
        //  $dataProvider->pagination->pageSize=3;

        /** @var array $wbStatuses Статусы заказов в соответствии со статусами привязанных к ним накладных!
         * Статусы накладных в таблице one_s_waybill_status */
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
        $search->getRestaurantIntegration(SearchOrdersComponent::INTEGRATION_TYPE_ONES, $searchModel,
            $organization->id, $this->currentUser->organization_id, $wbStatuses, ['pageSize' => 20],
            ['defaultOrder' => ['id' => SORT_DESC]]);
        $lisences = $organization->getLicenseList();
        if (isset($lisences['odinsobsh']) && $lisences['odinsobsh']) {
            $lisences = $lisences['odinsobsh'];
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
            'visible'      => false,
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
        $model = OneSWaybill::findOne(Yii::$app->request->get('waybill_id'));
        $vatData = VatData::getVatList();
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.waybill.not.find', ['ru' => 'Приходной накладной 1С с таким номером не существует.']));
        }

        //$obConstModel = OneSDicconst::findOne(['denom' => 'main_org']);

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

        $agentModel = OneSContragent::findOne(['id' => $model->agent_uuid]);
        $storeModel = OneSStore::findOne(['id' => $model->store_id]);

        $lic = OneSService::getLicense();
        $view = $lic ? 'indexmap' : '/default/_nolic';
        $vatFilter = [];
        $vatFilter["vat"] = 1;
        $params = [
            'dataProvider'          => $dataProvider,
            'wmodel'                => $model,
            'agentName'             => $agentModel->name,
            'storeName'             => $storeModel->name,
            'isAndroid'             => $isAndroid,
            'searchModel'           => $searchModel,
            'vatData'               => $vatData,
            //'parentBusinessId' => $obConstModel->getPconstValue(),
            'OneSWaybillDataSearch' => $vatFilter,
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
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.waybill.not.find', ['ru' => 'Приходной накладной 1С с таким номером не существует.']));
        }

        $waybill_datas = OneSWaybillData::find()->where(['waybill_id' => $wbill_id])->all();
        foreach ($waybill_datas as $waybill_data) {
            $sum_old = $waybill_data->sum;
            $vat = $waybill_data->vat;
            $defsum = $waybill_data->defsum;
            if ($is_checked) { // Добавляем НДС
                $waybill_data->sum = round($sum_old / ($vat / 10000 + 1), 2);
                $vat = 1;
            } else { // Убираем НДС
                $waybill_data->sum = $defsum;
                $vat = 0;
            }
            if (!$waybill_data->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной 1С не удалось.']));
            }
        }
        $wmodel->vat_included = $vat;
        if (!$wmodel->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную 1С не удалось.']));
        }
        return true;
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
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.waybill.not.find', ['ru' => 'Приходной накладной 1С с таким номером не существует.']));
        }

        if ($wayModel->vat_included) {
            $model->sum = round($model->defsum / (1 + $model->vat / 10000), 2);
        } else {
            $model->sum = $model->defsum;
        }

        if (!$model->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную 1С не удалось.']));
        }

        return $this->redirect(['map', 'waybill_id' => $wayModel->id]);
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

            $query2 = (new Query())
                ->select([
                    "id"    => "id",
                    "name" => "name",
                    "measure"  => "measure"
                ])
                ->from('one_s_good')
                ->where(['is_active' => 1])
                ->andWhere(['org_id' => $orgId])
                ->andWhere("name LIKE :term", [':term' => $term . '%'])
                ->orderBy(['name' => SORT_ASC, "measure" => SORT_ASC])
                ->limit(15);

            $query3 = (new Query())
                ->select([
                    "id"    => "id",
                    "name" => "name",
                    "measure"  => "measure"
                ])
                ->from('one_s_good')
                ->where(['is_active' => 1])
                ->andWhere(['org_id' => $orgId])
                ->andWhere("name LIKE :term", [':term' => '%' . $term . '%'])
                ->orderBy(['name' => SORT_ASC, "measure" => SORT_ASC])
                ->limit(10);

            $query1 = (new Query())
                ->select([
                    "id"    => "id",
                    "name" => "name",
                    "measure"  => "measure",
                ])
                ->from('one_s_good')
                ->union($query2)
                ->union($query3)
                ->where(['is_active' => 1])
                ->andWhere(['org_id' => $orgId])
                ->andWhere(['name' => ':term'], [':term' => $term])
                ->orderBy(['name' => SORT_ASC, "measure" => SORT_ASC])
                ->limit(10);

            $query = (new Query())
                ->select([
                    "id"  => "id",
                    "txt" => "CONCAT(name, ' (' ,measure, ')')",
                ])
                ->from("(" . $query1->createCommand()->getRawSql() . ") t");
            $result = $query->all(\Yii::$app->get('db_api'));
            $out = array_values($result);

        } else {
            $orgId = User::findOne(Yii::$app->user->id)->organization_id;

            $query = (new Query())
                ->select([
                    "id"  => "id",
                    "txt" => "CONCAT(name, ' (' ,measure, ')')"
                ])
                ->from('one_s_good')
                ->where(['is_active' => 1])
                ->andWhere(['org_id' => $orgId])
                ->orderBy(['txt' => SORT_ASC])
                ->limit(100);
            $result = $query->all(\Yii::$app->get('db_api'));
            $out = array_values($result);
        }
        return $out;
    }

    /**
     * @param $id
     * @param $page
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id, $page)
    {
        $model = $this->findModel($id);
        $lic = OneSService::getLicense();
        $vi = $lic ? 'update' : '/default/_nolic';
        if ($model->load(Yii::$app->request->post())) {
            if ($model->agent_uuid == '') {
                $model->agent_uuid = null;
            }
            if ($model->store_id == 0) {
                $model->store_id = null;
            }
            $kolvo_nesopost = OneSWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $model->id])->andWhere(['product_rid' => null])->count();
            if (($model->agent_uuid === null) or ($model->num_code === null) or ($model->store_id === null)) {
                $shapka = 0;
            } else {
                $shapka = 1;
            }
            if ($kolvo_nesopost == 0) {
                if ($shapka == 1) {
                    $model->readytoexport = 1;
                    $model->status_id = 3;
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
                throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную 1С не удалось.']));
            }
            return $this->redirect(['/clientintegr/odinsobsh/waybill/index', 'page' => $page, 'way' => $model->order_id]);
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
        $user = $this->currentUser;
        $ord = \common\models\Order::findOne(['id' => $order_id]);

        if (!$ord) {
            throw new NotFoundHttpException(Yii::t('error', 'api.controllers.order.not.find', ['ru' => 'Заказа с таким номером не существует.']));
        }

        //$waybillModeIiko = OneSDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();

        $model = new OneSWaybill();
        $model->order_id = $order_id;
        $model->status_id = 1;
        $model->org = $ord->client_id;
        $model->discount = $ord->discount;
        $model->discount_type = $ord->discount_type;
        $is_invoice = OneSPconst::getSettingsColumn($user->organization_id);
        $model->is_invoice = $is_invoice;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/clientintegr/odinsobsh/waybill/index', 'page' => $page, 'way' => $model->order_id]);
        } else {
            $model->num_code = $ord->waybill_number ?? $ord->id;
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
         * header ("Content-Type:text/xml");
         * $id = Yii::$app->request->get('id');
         * $model = $this->findModel($id);
         * echo $model->getXmlDocument();
         * exit;
         */

        $api = OneSApi::getInstance();
        try {
            if (!Yii::$app->request->isAjax) {
                throw new NotFoundHttpException(Yii::t('error', 'api.controllers.method.not.ajax', ['ru' => 'Способ отправки должен быть только AJAX.']));
            }

            $id = Yii::$app->request->post('id');
            $model = $this->findModel($id);

            if (!$model) {
                throw new NotFoundHttpException(Yii::t('error', 'api.one.s.waybill.not.find', ['ru' => 'Приходной накладной 1С с таким номером не существует.']));
            }

            if ($api->auth()) {
                if (!$api->sendWaybill($model)) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.one.s.waybill.not.send', ['ru' => 'Приходную накладную 1С не удалось выгрузить.']));
                }
                $model->status_id = 2;
                if (!$model->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную 1С не удалось.']));
                }
            } else {
                throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.not.auth', ['ru' => 'Не удалось авторизоваться на сервере 1С.']));
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

    public function actionMakevat($waybill_id, $vat, $vatf, $sort, $page)
    {
        $page_size = 20;
        $model = $this->findModel($waybill_id);

        if ($vatf == 1) {
            $waybill_datas = OneSWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $waybill_id])->all();
        } else {
            $waybill_datas = OneSWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $waybill_id])->andWhere(['vat' => $vatf])->all();
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

        if (count($waybill_datas) > 0) {
            foreach ($waybill_datas as $waybill_data) {
                $product_id = $waybill_data->product_id;
                $product_rid = $waybill_data->product_rid;
                $org_id = $waybill_data->org;
                $koef = $waybill_data->koef;

                $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

                $existence = AllMaps::find()->where(['service_id' => Registry::ONE_S_CLIENT_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
                if ($existence == 0) {
                    $waybill = OneSWaybill::find()->where('id = :w_wid', [':w_wid' => $waybill_id])->one();
                    $store = $waybill->store_id;
                    $position = new AllMaps();
                    $position->service_id = Registry::ONE_S_CLIENT_SERVICE_ID;
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
                    $position = AllMaps::find()->where(['service_id' => Registry::ONE_S_CLIENT_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
                    $position->vat = $vat;
                }
                $position->unit_rid = null;
                if (!$position->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
                }
                $waybill_data->vat = $vat;
                if (!$waybill_data->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной 1С не удалось.']));
                }
            }
        }
        return $this->redirect(['map', 'waybill_id' => $model->id, 'way' => 0, 'OneSWaybillDataSearch[vat]' => $vatf, 'sort' => $sort, 'page' => $page]);
    }

    public function actionChvat($id, $koef, $vatf, $sort = 'fproductnameProduct', $vat, $page, $way)
    {

        $waybill_data = $this->findDataModel($id);

        $waybill_data->vat = $vat;
        if (!$waybill_data->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной 1С не удалось.']));
        }

        $product_id = $waybill_data->product_id;
        $product_rid = $waybill_data->product_rid;
        $org_id = $waybill_data->org;

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $existence = AllMaps::find()->where(['service_id' => Registry::ONE_S_CLIENT_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
        if ($existence == 0) {
            $waybill = OneSWaybill::find()->where(['id' => $waybill_data->waybill_id])->one();
            $store = $waybill->store_id;
            $position = new AllMaps();
            $position->service_id = Registry::ONE_S_CLIENT_SERVICE_ID;
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
            $position = AllMaps::find()->where(['service_id' => Registry::ONE_S_CLIENT_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
            $position->vat = $vat;
        }
        $position->unit_rid = null;
        if (!$position->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
        }
        return $this->redirect(['map', 'waybill_id' => $waybill_data->waybill->id, 'page' => $page, 'way' => $way, 'OneSWaybillDataSearch[vat]' => $vatf, 'sort' => $sort]);

    }

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

    public function deleteGET($url, $name, $amp = true)
    {
        $url = str_replace("&amp;", "&", $url); // Заменяем сущности на амперсанд, если требуется
        list($url_part, $qs_part) = array_pad(explode("?", $url), 2, ""); // Разбиваем URL на 2 части: до знака ? и после
        parse_str($qs_part, $qs_vars); // Разбиваем строку с запросом на массив с параметрами и их значениями
        unset($qs_vars[$name]); // Удаляем необходимый параметр
        if (count($qs_vars) > 0) { // Если есть параметры
            $url = $url_part . "?" . http_build_query($qs_vars); // Собираем URL обратно
            if ($amp) $url = str_replace("&", "&amp;", $url); // Заменяем амперсанды обратно на сущности, если требуется
        } else $url = $url_part; // Если параметров не осталось, то просто берём всё, что идёт до знака ?
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
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.waybill.not.find', ['ru' => 'Приходной накладной 1С с таким номером не существует.']));
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
            throw new NotFoundHttpException(Yii::t('error', 'api.one.s.waybill.data.not.find', ['ru' => 'Товара с таким номером в приходной накладной 1С не существует.']));
        }
    }

    /**
     * Make unload_status -> 0 or unload_status -> 1
     */
    public function actionMapTriggerWaybillDataStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $transaction = Yii::$app->db_api->beginTransaction();
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $action = Yii::$app->request->post('action');

        $model = OneSWaybillData::findOne($id);
        try {
            $model->unload_status = $status;
            $model->save();
            $transaction->commit();
        } catch (\Throwable $t) {
            $transaction->rollback();
            Yii::debug($t->getMessage());
            return false;
        }

        return ['success' => true, 'action' => $action];
    }

    public function actionVatFilter()
    {
        $hr = Yii::$app->request->post('hr');
        $vatf = Yii::$app->request->post('vatf');
        $sort = Yii::$app->request->post('sort');

        $temp0 = explode('?', $hr);
        $temp1 = explode('&', $temp0[1]);
        $arr = [];
        foreach ($temp1 as $para) {
            $temp2 = explode('=', $para);
            $arr[$temp2[0]] = $temp2[1];
        }
        $waybill_id = $arr["waybill_id"];

        return $this->redirect(['map', 'waybill_id' => $waybill_id, 'way' => 0, 'sort' => $sort, 'OneSWaybillDataSearch[vat]' => $vatf, 'page' => 1]);
    }

    public function actionEditGlobal()
    {
        $product_rid = Yii::$app->request->post('id');
        $number = Yii::$app->request->post('number');

        $org_id = User::findOne(Yii::$app->user->id)->organization_id;

        $product = OneSGood::find()->where('id = :w_id', [':w_id' => $product_rid])->one();
        $munit = $product->measure;

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($number);

        $existence = AllMaps::find()->where(['service_id' => Registry::ONE_S_CLIENT_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $number])->one();
        if (!$existence) {
            $position = new AllMaps();
            $position->service_id = Registry::ONE_S_CLIENT_SERVICE_ID;
            $position->org_id = $org_id;
            $position->product_id = $number;
            $position->supp_id = $supp_id;
            $position->serviceproduct_id = $product_rid;
            $position->unit_rid = null;
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

        $orders = Order::find()->where(['vendor_id' => $supp_id, 'client_id' => $org_id])->all();
        foreach ($orders as $order) {
            $waybills = OneSWaybill::find()->where(['order_id' => $order->id, 'status_id' => 1])->all();
            foreach ($waybills as $waybill) {
                $waybill_datas = OneSWaybillData::find()->where(['waybill_id' => $waybill->id, 'product_id' => $number, 'product_rid' => null])->all();
                foreach ($waybill_datas as $waybill_data) {
                    $waybill_data->product_rid = $product_rid;
                    if (!$waybill_data->save()) {
                        throw new NotFoundHttpException(Yii::t('error', 'api.one.s.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной 1С не удалось.']));
                    }
                }
            }
        }
        return $munit;
    }
}
