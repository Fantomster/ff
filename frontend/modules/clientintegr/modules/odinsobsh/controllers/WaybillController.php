<?php

namespace frontend\modules\clientintegr\modules\odinsobsh\controllers;

use api\common\models\one_s\OneSContragent;
use api\common\models\one_s\OneSDicconst;
use api\common\models\one_s\OneSGood;
use api\common\models\one_s\OneSPconst;
use api\common\models\one_s\OneSStore;
use api\common\models\OneSWaybillDataSearch;
use api\common\models\VatData;
use common\models\Organization;
use common\models\search\OrderSearch;
use Yii;
use common\models\User;
use yii\db\Connection;
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
use common\helpers\DBNameHelper;

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
                'class'           => EditableColumnAction::className(),
                'modelClass'      => OneSWaybillData::className(),
                'outputValue'     => function ($model, $attribute) {
                    if ($attribute === 'vat') {
                        return $model->$attribute / 100;
                    } else {
                        //$model->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
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

        $sql = "SELECT waybill_id,product_id,org,vat,koef FROM one_s_waybill_data WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $number])->queryAll();
        $waybill_id = $result[0]["waybill_id"];
        $product_id = $result[0]["product_id"];
        $org_id = $result[0]["org"];
        $vat = $result[0]["vat"];
        $koef = $result[0]["koef"];

        $sql = "SELECT measure FROM one_s_good WHERE id = :w_id";
        $munit = Yii::$app->db_api->createCommand($sql, [':w_id' => $product_rid])->queryScalar();

        $sql = "UPDATE one_s_waybill_data SET product_rid = :w_prid, munit = :w_munit, updated_at = NOW() WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_prid' => $product_rid, ':w_munit' => $munit, ':w_id' => $number])->execute();

        $sql = "SELECT COUNT(*) FROM one_s_waybill_data WHERE waybill_id = :w_wid AND product_rid IS NULL";
        $kolvo_nesopost = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->queryScalar();

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $sql = "SELECT agent_uuid,num_code,store_id FROM one_s_waybill WHERE id = :w_wid";
        $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->queryAll();
        $agent_uuid = $result[0]["agent_uuid"];
        $num_code = $result[0]["num_code"];
        $store_id = $result[0]["store_id"];
        if (($agent_uuid === null) or ($num_code === null) or ($store_id === null)) {
            $shapka = 0;
        } else {
            $shapka = 1;
        }

        if ($kolvo_nesopost == 0) {
            if ($shapka == 1) {
                $sql = "UPDATE one_s_waybill SET readytoexport = 1, status_id = 3, updated_at = NOW() WHERE id = :w_wid";
                $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->execute();
            } else {
                $sql = "UPDATE one_s_waybill SET readytoexport = 0, status_id = 1, updated_at = NOW() WHERE id = :w_wid";
                $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->execute();
            }
        } else {
            if ($shapka == 1) {
                $sql = "UPDATE one_s_waybill SET readytoexport = 0, status_id = 1, updated_at = NOW() WHERE id = :w_wid";
                $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->execute();
            } else {
                $sql = "UPDATE one_s_waybill SET readytoexport = 0, updated_at = NOW() WHERE id = :w_wid";
                $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->execute();
            }
        }

        if ($button == 'forever') {
            $sql = "SELECT COUNT(*) FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
            $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
            if ($existence == 0) {
                /*$sql = "SELECT store_id,agent_uuid FROM one_s_waybill WHERE id = :w_wi";
                $res = Yii::$app->db_api->createCommand($sql, [':w_wi' => $waybill_id])->queryAll();
                $store = $res[0]["store_id"];
                $cagent = $res[0]["agent_uuid"];
                $sql = "SELECT COUNT(*) FROM one_s_contragent WHERE cid = :w_uuid AND org_id = :w_org";
                $result = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
                if ($result == 0) {
                    $agent = null;
                } else {
                    $sql = "SELECT id FROM one_s_contragent WHERE cid = :w_uuid AND org_id = :w_org";
                    $agent = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
                }*/
                $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                        VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), NOW(), NOW())";
                $result = Yii::$app->db_api->createCommand($sql, [
                    ':w_s'       => Registry::ONE_S_CLIENT_SERVICE_ID,
                    ':w_org'     => $org_id,
                    ':w_product' => $product_id,
                    ':w_supp'    => $supp_id,
                    ':w_spid'    => $product_rid,
                    ':w_unitr'   => null,
                    ':w_store'   => $store_id,
                    ':w_koef'    => $koef,
                    ':w_vat'     => $vat,
                ])->execute();
            } else {
                $sql = "SELECT id FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
                $id_all_map = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
                $sql = "UPDATE all_map SET serviceproduct_id = :w_spid, unit_rid = :w_unitr, linked_at = NOW(), updated_at = NOW() WHERE id = :w_id";
                $result = Yii::$app->db_api->createCommand($sql, [':w_spid' => $product_rid, ':w_unitr' => null, ':w_id' => $id_all_map])->execute();
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

        $sql = "SELECT quant,koef,waybill_id,product_id,org,vat,product_rid FROM one_s_waybill_data WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $koef_id])->queryAll();
        $quant_old = $result[0]["quant"];
        $koef_old = $result[0]["koef"];
        $waybill_id = $result[0]["waybill_id"];
        $product_id = $result[0]["product_id"];
        $product_rid = $result[0]["product_rid"];
        $org_id = $result[0]["org"];
        $vat = $result[0]["vat"];
        if ($koef == 0) {
            $koef = $koef_old;
        }
        $quant_new = $quant_old * ($koef / $koef_old);
        $quant_new = round($quant_new, 4);

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $sql = "UPDATE one_s_waybill_data SET quant = :w_quant, koef = :w_koef WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_quant' => $quant_new, ':w_koef' => $koef, ':w_id' => $koef_id])->execute();
        if ($buttons == 'forever') {
            $sql = "SELECT COUNT(*) FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
            $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
            if ($existence == 0) {
                $sql = "SELECT store_id/*,agent_uuid*/ FROM one_s_waybill WHERE id = :w_wi";
                $res = Yii::$app->db_api->createCommand($sql, [':w_wi' => $waybill_id])->queryAll();
                $store = $res[0]["store_id"];
                /*$cagent = $res[0]["agent_uuid"];
                $sql = "SELECT COUNT(*) FROM one_s_contragent WHERE cid = :w_uuid AND org_id = :w_org";
                $result = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
                if ($result == 0) {
                    $agent = null;
                } else {
                    $sql = "SELECT id FROM one_s_contragent WHERE cid = :w_uuid AND org_id = :w_org";
                    $agent = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
                }*/
                $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                        VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), null, NOW())";
                $result = Yii::$app->db_api->createCommand($sql, [
                    ':w_s'       => Registry::ONE_S_CLIENT_SERVICE_ID,
                    ':w_org'     => $org_id,
                    ':w_product' => $product_id,
                    ':w_supp'    => $supp_id,
                    ':w_spid'    => $product_rid,
                    ':w_unitr'   => null,
                    ':w_store'   => $store,
                    ':w_koef'    => $koef,
                    ':w_vat'     => $vat,
                ])->execute();
                if (!(is_null($product_rid))) {
                    $sql = "UPDATE all_map SET linked_at = NOW() WHERE org_id = :w_org AND product_id = :w_product AND service_id = :w_s";
                    $result = Yii::$app->db_api->createCommand($sql, [':w_org' => $org_id, ':w_product' => $product_id, ':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID])->execute();
                }
            } else {
                $sql = "SELECT id FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
                $id_all_map = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
                $sql = "UPDATE all_map SET koef = :w_koef, vat = :w_vat, updated_at = NOW() WHERE id = :w_id";
                $result = Yii::$app->db_api->createCommand($sql, [':w_koef' => $koef, ':w_vat' => $vat, ':w_id' => $id_all_map])->execute();
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
            //$ssilka = '/clientintegr/iiko/waybill/map?waybill_id='.$waybill_id.'&way='.$koef_id.'&sort='.$sort.'&iikoWaybillDataSearch[vat]='.$vat_filter.'&page='.$page;
            //return $this->redirect($ssilka);
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
        // $lisences = OneSService::getLicense();
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
            //'visible' =>OneSPconst::getSettingsColumn(Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id)->id),
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
            die("Cant find wmodel in map controller");
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

        /*if (!$model->save()) {
            var_dump($model->getErrors());
            exit;
        }*/

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
            $sql = "( select id, CONCAT(`name`, ' (' ,measure, ')') as `text` from one_s_good where org_id = " . User::findOne(Yii::$app->user->id)->organization_id . " and name = '" . $term . "' )" .
                " union ( select id, CONCAT(`name`, ' (' ,measure, ')') as `text` from one_s_good  where org_id = " . User::findOne(Yii::$app->user->id)->organization_id . " and name like '" . $term . "%' limit 15 )" .
                "union ( select id, CONCAT(`name`, ' (' ,measure, ')') as `text` from one_s_good where  org_id = " . User::findOne(Yii::$app->user->id)->organization_id . " and name like '%" . $term . "%' limit 10 )" .
                "order by case when length(trim(`text`)) = length('" . $term . "') then 1 else 2 end, `text`; ";

            $db = Yii::$app->db_api;
            $data = $db->createCommand($sql)->queryAll();
            $out['results'] = array_values($data);
        }
        return $out;
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
            $sql = "( select `id`, CONCAT(`name`, ' (' ,measure, ')') as `text` from `one_s_good` where org_id = " . User::findOne(Yii::$app->user->id)->organization_id . " and `name` = '" . $term . "' )" .
                " union ( select `id`, CONCAT(`name`, ' (' ,measure, ')') as `text` from `one_s_good`  where org_id = " . User::findOne(Yii::$app->user->id)->organization_id . " and `name` like '" . $term . "%' limit 15 )" .
                "union ( select id, CONCAT(`name`, ' (' ,measure, ')') as `text` from one_s_good where  org_id = " . User::findOne(Yii::$app->user->id)->organization_id . " and name like '%" . $term . "%' limit 10 )" .
                "order by case when length(trim(`name`)) = length('" . $term . "') then 1 else 2 end, `name`; ";

            $db = Yii::$app->db_api;
            $data = $db->createCommand($sql)->queryAll();
            $out = array_values($data);
        } else {
            $orgId = User::findOne(Yii::$app->user->id)->organization_id;
            //$constId = OneSDicconst::findOne(['denom' => 'main_org']);
            //$parentId = OneSPconst::findOne(['const_id' => $constId->id, 'org' => $orgId]);
            //$organizationID = !is_null($parentId) ? $parentId->value : $orgId;
            $sql = "SELECT id, CONCAT(`name`, ' (' ,measure, ')') as `text` FROM one_s_good WHERE org_id = " . $orgId . ' ORDER BY name LIMIT 100';

            /**
             * @var $db Connection
             */
            $db = Yii::$app->db_api;
            $data = $db->createCommand($sql)->queryAll();
            $out = array_values($data);
        }
        return $out;
    }

    /**
     * @param null $term
     * @param      $org
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
            /*if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }*/
            $sql = "SELECT COUNT(*) FROM one_s_waybill_data WHERE waybill_id = :w_wid AND product_rid IS NULL";
            $kolvo_nesopost = Yii::$app->db_api->createCommand($sql, [':w_wid' => $model->id])->queryScalar();
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
            $model->save();
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
            /*if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }*/
            return $this->redirect([$this->getLastUrl() . 'way=' . $model->order_id]);
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
         * header ("Content-Type:text/xml");
         * $id = Yii::$app->request->get('id');
         * $model = $this->findModel($id);
         * echo $model->getXmlDocument();
         * exit;
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
                if (!$api->sendWaybill($model)) {
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

    public function actionMakevat($waybill_id, $vat, $vatf, $sort, $page)
    {
        $page_size = 20;
        $model = $this->findModel($waybill_id);

        if ($vatf == 1) {
            $vat_add = '';
        } else {
            $vat_add = ' AND vat = ' . $vatf;
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

        $sql = "SELECT id FROM one_s_waybill_data WHERE waybill_id = :w_wid" . $vat_add;
        $result0 = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->queryAll();

        if (count($result0 > 0)) {
            foreach ($result0 as $resu) {
                $id = $resu["id"];
                $sql = "SELECT product_id,org,product_rid,koef FROM one_s_waybill_data WHERE id = :w_id";
                $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $id])->queryAll();
                $product_id = $result[0]["product_id"];
                $product_rid = $result[0]["product_rid"];
                $org_id = $result[0]["org"];
                $koef = $result[0]["koef"];

                $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

                $sql = "SELECT COUNT(*) FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
                $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
                if ($existence == 0) {
                    $sql = "SELECT store_id/*,agent_uuid*/ FROM one_s_waybill WHERE id = :w_wi";
                    $res = Yii::$app->db_api->createCommand($sql, [':w_wi' => $waybill_id])->queryAll();
                    $store = $res[0]["store_id"];
                    /*$cagent = $res[0]["agent_uuid"];
                    $sql = "SELECT COUNT(*) FROM one_s_contragent WHERE cid = :w_uuid AND org_id = :w_org";
                    $result = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
                    if ($result == 0) {
                        $agent = null;
                    } else {
                        $sql = "SELECT id FROM one_s_contragent WHERE cid = :w_uuid AND org_id = :w_org";
                        $agent = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
                    }*/
                    $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                                VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), null, NOW())";
                    $result = Yii::$app->db_api->createCommand($sql, [
                        ':w_s'       => Registry::ONE_S_CLIENT_SERVICE_ID,
                        ':w_org'     => $org_id,
                        ':w_product' => $product_id,
                        ':w_supp'    => $supp_id,
                        ':w_spid'    => $product_rid,
                        ':w_unitr'   => null,
                        ':w_store'   => $store,
                        ':w_koef'    => $koef,
                        ':w_vat'     => $vat,
                    ])->execute();
                    if (!(is_null($product_rid))) {
                        $sql = "UPDATE all_map SET linked_at = NOW() WHERE org_id = :w_org AND product_id = :w_product AND service_id = :w_s";
                        $result = Yii::$app->db_api->createCommand($sql, [':w_org' => $org_id, ':w_product' => $product_id, ':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID])->execute();
                    }
                } else {
                    $sql = "SELECT id FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
                    $id_all_map = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
                    $sql = "UPDATE all_map SET vat = :w_vat, updated_at = NOW() WHERE id = :w_id";
                    $result = Yii::$app->db_api->createCommand($sql, [':w_vat' => $vat, ':w_id' => $id_all_map])->execute();
                }
            }
        }

        $sql = 'UPDATE one_s_waybill_data SET vat = :vat, updated_at = now() WHERE waybill_id = :id' . $vat_add;
        $rress = Yii::$app->db_api
            ->createCommand($sql, [':vat' => $vat, ':id' => $waybill_id])->execute();

        return $this->redirect(['map', 'waybill_id' => $model->id, 'way' => 0, 'OneSWaybillDataSearch[vat]' => $vatf, 'sort' => $sort, 'page' => $page]);
    }

    public function actionChvat($id, $koef, $vatf, $sort = 'fproductnameProduct', $vat, $page, $way)
    {

        $model = $this->findDataModel($id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE one_s_waybill_data SET vat = :vat, updated_at = now() WHERE id = :id', [':vat' => $vat, ':id' => $id])->execute();

        $sql = "SELECT waybill_id,product_id,org,product_rid FROM one_s_waybill_data WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $id])->queryAll();
        $waybill_id = $result[0]["waybill_id"];
        $product_id = $result[0]["product_id"];
        $product_rid = $result[0]["product_rid"];
        $org_id = $result[0]["org"];

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $sql = "SELECT COUNT(*) FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
        $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
        if ($existence == 0) {
            $sql = "SELECT store_id/*,agent_uuid*/ FROM one_s_waybill WHERE id = :w_wi";
            $res = Yii::$app->db_api->createCommand($sql, [':w_wi' => $waybill_id])->queryAll();
            $store = $res[0]["store_id"];
            /*$cagent = $res[0]["agent_uuid"];
            $sql = "SELECT COUNT(*) FROM one_s_contragent WHERE cid = :w_uuid AND org_id = :w_org";
            $result = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
            if ($result == 0) {
                $agent = null;
            } else {
                $sql = "SELECT id FROM one_s_contragent WHERE cid = :w_uuid AND org_id = :w_org";
                $agent = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
            }*/
            $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                        VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), null, NOW())";
            $result = Yii::$app->db_api->createCommand($sql, [
                ':w_s'       => Registry::ONE_S_CLIENT_SERVICE_ID,
                ':w_org'     => $org_id,
                ':w_product' => $product_id,
                ':w_supp'    => $supp_id,
                ':w_spid'    => $product_rid,
                ':w_unitr'   => null,
                ':w_store'   => $store,
                ':w_koef'    => $koef,
                ':w_vat'     => $vat,
            ])->execute();
            if (!(is_null($product_rid))) {
                $sql = "UPDATE all_map SET linked_at = NOW() WHERE org_id = :w_org AND product_id = :w_product AND service_id = :w_s";
                $result = Yii::$app->db_api->createCommand($sql, [':w_org' => $org_id, ':w_product' => $product_id, ':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID])->execute();
            }
        } else {
            $sql = "SELECT id FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
            $id_all_map = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
            $sql = "UPDATE all_map SET vat = :w_vat, updated_at = NOW() WHERE id = :w_id";
            $result = Yii::$app->db_api->createCommand($sql, [':w_vat' => $vat, ':w_id' => $id_all_map])->execute();
        }
        return $this->redirect(['map', 'waybill_id' => $model->waybill->id, 'page' => $page, 'way' => $way, 'OneSWaybillDataSearch[vat]' => $vatf, 'sort' => $sort]);

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

        $sql = "SELECT measure FROM one_s_good WHERE id = :w_id";
        $munit = Yii::$app->db_api->createCommand($sql, [':w_id' => $product_rid])->queryScalar();

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($number);

        $sql = "SELECT id, koef FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product LIMIT 1";
        $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::ONE_S_CLIENT_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $number])->queryAll();
        if (!$existence) {
            $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                        VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), NOW(), NOW())";
            $result = Yii::$app->db_api->createCommand($sql, [
                ':w_s'       => Registry::ONE_S_CLIENT_SERVICE_ID,
                ':w_org'     => $org_id,
                ':w_product' => $number,
                ':w_supp'    => $supp_id,
                ':w_spid'    => $product_rid,
                ':w_unitr'   => null,
                ':w_store'   => null,
                ':w_koef'    => 1,
                ':w_vat'     => null,
            ])->execute();
        } else {
            $id_all_map = $existence[0]['id'];
            $koef_all_map = $existence[0]['koef'];
            if ($koef_all_map === null) {
                $koef = 1.0000;
            }
            $sql = "UPDATE all_map SET serviceproduct_id = :w_spid, koef = :w_koef, linked_at = NOW(), updated_at = NOW() WHERE id = :w_id";
            $result = Yii::$app->db_api->createCommand($sql, [':w_spid' => $product_rid, ':w_koef' => $koef_all_map, ':w_id' => $id_all_map])->execute();
        }
        $dbName = DBNameHelper::getMainName();
        $sql = "SELECT wd.id FROM `one_s_waybill_data` `wd` LEFT JOIN `one_s_waybill` `w` ON wd.waybill_id = w.id 
                LEFT JOIN " . $dbName . ".`order` `o` ON w.order_id = o.id  
                WHERE w.status_id = 1 AND o.vendor_id = :w_supp AND o.client_id = :w_org AND wd.product_id = :w_pid AND wd.product_rid IS NULL";
        $massivs = Yii::$app->db_api->createCommand($sql, [':w_pid' => $number, ':w_supp' => $supp_id, ':w_org' => $org_id])->queryAll();
        $ids = '';
        foreach ($massivs as $massiv) {
            $ids .= $massiv['id'] . ',';
        }
        $ids = rtrim($ids, ',');
        if ($ids) {
            $sql = "UPDATE `one_s_waybill_data` SET `product_rid` = :w_spid, `munit` = :w_munit, updated_at = NOW() WHERE id in (" . $ids . ")";
            $result = Yii::$app->db_api->createCommand($sql, [':w_spid' => $product_rid, ':w_munit' => $munit])->execute();
        }


        return $munit;
    }
}
