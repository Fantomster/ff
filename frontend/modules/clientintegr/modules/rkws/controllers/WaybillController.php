<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use api\common\models\RkAgent;
use api\common\models\RkDicconst;
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
use yii\web\Response;
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
            'edit'       => [// identifier for your editable action
                'class'         => EditableColumnAction::className(), // action class name
                'modelClass'    => RkWaybilldata::className(), // the update model class
                'outputValue'   => function ($model, $attribute, $key, $index) {
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
                'class'           => EditableColumnAction::className(), // action class name
                'modelClass'      => RkWaybilldata::className(), // the model for the record being edited
                //   'outputFormat' => ['decimal', 6],
                'outputValue'     => function ($model, $attribute, $key, $index) {
                    if ($attribute === 'vat') {
                        return $model->$attribute / 100;
                    } else {
                        return round($model->$attribute, 6);      // return any custom output value if desired
                    }
                    //       return $model->$attribute;
                },
                'outputMessage'   => function ($model, $attribute, $key, $index) {
                    return '';                                  // any custom error to return after model save
                },
                'showModelErrors' => true, // show model validation errors after save
                'errorOptions'    => ['header' => '']                // error summary HTML options
                // 'postOnly' => true,
                // 'ajaxOnly' => true,
                // 'findModel' => function($id, $action) {},
                // 'checkAccess' => function($action, $model) {}
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

        $sql = "SELECT waybill_id,product_id,org,vat,koef FROM rk_waybill_data WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $number])->queryAll();
        $waybill_id = $result[0]["waybill_id"];
        $product_id = $result[0]["product_id"];
        $org_id = $result[0]["org"];
        $vat = $result[0]["vat"];
        $koef = $result[0]["koef"];

        $sql = "SELECT unit_rid,unitname FROM rk_product WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $product_rid])->queryAll();
        $munit = $result[0]["unitname"];
        $munit_id = $result[0]["unit_rid"];

        $sql = "UPDATE rk_waybill_data SET product_rid = :w_prid, munit_rid = :w_munit, updated_at = NOW(), linked_at = NOW() WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_prid' => $product_rid, ':w_munit' => $munit_id, ':w_id' => $number])->execute();

        $sql = "SELECT COUNT(*) FROM rk_waybill_data WHERE waybill_id = :w_wid AND product_rid IS NULL";
        $kolvo_nesopost = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->queryScalar();

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $sql = "SELECT corr_rid,num_code,text_code,store_rid FROM rk_waybill WHERE id = :w_wid";
        $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->queryAll();
        $agent_uuid = $result[0]["corr_rid"];
        $num_code = $result[0]["num_code"];
        $text_code = $result[0]["text_code"];
        $store_id = $result[0]["store_rid"];
        if (($agent_uuid === null) or ($num_code === null) or ($text_code === null) or ($store_id === null)) {
            $shapka = 0;
        } else {
            $shapka = 1;
        }

        if ($kolvo_nesopost == 0) {
            if ($shapka == 1) {
                $sql = "UPDATE rk_waybill SET readytoexport = 1, status_id = 5, updated_at = NOW() WHERE id = :w_wid";
                $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->execute();
            } else {
                $sql = "UPDATE rk_waybill SET readytoexport = 0, status_id = 1, updated_at = NOW() WHERE id = :w_wid";
                $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->execute();
            }
        } else {
            if ($shapka == 1) {
                $sql = "UPDATE rk_waybill SET readytoexport = 0, status_id = 1, updated_at = NOW() WHERE id = :w_wid";
                $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->execute();
            } else {
                $sql = "UPDATE rk_waybill SET readytoexport = 0, updated_at = NOW() WHERE id = :w_wid";
                $result = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->execute();
            }
        }

        if ($button == 'forever') {
            $sql = "SELECT COUNT(*) FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
            $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::RK_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
            if ($existence == 0) {
                /*$sql = "SELECT store_rid,corr_rid FROM rk_waybill WHERE id = :w_wi";
                $res = Yii::$app->db_api->createCommand($sql, [':w_wi' => $waybill_id])->queryAll();
                $store = $res[0]["store_rid"];
                $cagent = $res[0]["corr_rid"];
                $sql = "SELECT COUNT(*) FROM rk_agent WHERE rid = :w_uuid AND acc = :w_org";
                $result = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
                if ($result == 0) {
                    $agent = null;
                } else {
                    $agent = $cagent;
                }*/
                $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                        VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), NOW(), NOW())";
                $result = Yii::$app->db_api->createCommand($sql, [
                    ':w_s'       => Registry::RK_SERVICE_ID,
                    ':w_org'     => $org_id,
                    ':w_product' => $product_id,
                    ':w_supp'    => $supp_id,
                    ':w_spid'    => $product_rid,
                    ':w_unitr'   => $munit_id,
                    ':w_store'   => $store_id,
                    ':w_koef'    => $koef,
                    ':w_vat'     => $vat,
                ])->execute();
            } else {
                $sql = "SELECT id FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
                $id_all_map = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::RK_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
                $sql = "UPDATE all_map SET serviceproduct_id = :w_spid, unit_rid = :w_unitr, linked_at = NOW(), updated_at = NOW() WHERE id = :w_id";
                $result = Yii::$app->db_api->createCommand($sql, [':w_spid' => $product_rid, ':w_unitr' => $munit_id, ':w_id' => $id_all_map])->execute();
            }
        }
        return $munit;
    }

    public function actionChangeCoefficientNew()
    {
        $page_size = 20;
        $est = 0;
        $i = 0;
        $massiv_post = Yii::$app->request->post('RkWaybilldata');
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

        $sql = "SELECT quant,koef,waybill_id,product_id,org,vat,product_rid FROM rk_waybill_data WHERE id = :w_id";
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

        if (is_null($product_rid)) {
            $product_rid = 0;
        }

        if ($product_rid != 0) {
            $sql = "SELECT unit_rid,unitname FROM rk_product WHERE id = :w_id";
            $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $product_rid])->queryAll();
            $munit = $result[0]["unitname"];
            $munit_id = $result[0]["unit_rid"];
        } else {
            $munit = null;
            $munit_id = null;
        }

        $sql = "UPDATE rk_waybill_data SET quant = :w_quant, koef = :w_koef WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_quant' => $quant_new, ':w_koef' => $koef, ':w_id' => $koef_id])->execute();
        if ($buttons == 'forever') {
            $sql = "SELECT COUNT(*) FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
            $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::RK_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
            if ($existence == 0) {
                $sql = "SELECT store_rid/*,corr_rid*/ FROM rk_waybill WHERE id = :w_wi";
                $res = Yii::$app->db_api->createCommand($sql, [':w_wi' => $waybill_id])->queryAll();
                $store = $res[0]["store_rid"];
                /*$cagent = $res[0]["corr_rid"];
                $sql = "SELECT COUNT(*) FROM rk_agent WHERE rid = :w_uuid AND acc = :w_org";
                $result = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
                if ($result == 0) {
                    $agent = null;
                } else {
                    $agent = $cagent;
                }*/
                $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                        VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), null, NOW())";
                $result = Yii::$app->db_api->createCommand($sql, [
                    ':w_s'       => Registry::RK_SERVICE_ID,
                    ':w_org'     => $org_id,
                    ':w_product' => $product_id,
                    ':w_supp'    => $supp_id,
                    ':w_spid'    => $product_rid,
                    ':w_unitr'   => $munit_id,
                    ':w_store'   => $store,
                    ':w_koef'    => $koef,
                    ':w_vat'     => $vat,
                ])->execute();
                if (!(is_null($product_rid))) {
                    $sql = "UPDATE all_map SET linked_at = NOW() WHERE org_id = :w_org AND product_id = :w_product AND service_id = :w_s";
                    $result = Yii::$app->db_api->createCommand($sql, [':w_org' => $org_id, ':w_product' => $product_id, ':w_s' => Registry::RK_SERVICE_ID])->execute();
                }
            } else {
                $sql = "SELECT id FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
                $id_all_map = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::RK_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
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
            return $this->redirect(['map', 'waybill_id' => $waybill_id, 'way' => $koef_id, 'sort' => $sort, 'RkWaybilldataSearch[vat]' => $vat_filter, 'page' => $page]);
        }
        return $koef;
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
        $search->getRestaurantIntegration(SearchOrdersComponent::INTEGRATION_TYPE_RKWS, $searchModel,
            $organization->id, $this->currentUser->organization_id, $wbStatuses, ['pageSize' => 20],
            ['defaultOrder' => ['id' => SORT_DESC]]);

        $lisences = $organization->getLicenseList();

        $view = ($lisences['rkws'] && $lisences['rkws_ucs']) ? 'index' : '/default/_nolic';
        $renderParams = [
            'searchModel'  => $searchModel,
            'affiliated'   => $search->affiliated,
            'dataProvider' => $search->dataProvider,
            'searchParams' => $search->searchParams,
            'businessType' => $search->businessType,
            'lic'          => $lisences['rkws'],
            'licucs'       => $lisences['rkws_ucs'],
            'visible'      => RkPconst::getSettingsColumn($organization->id),
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
        $vatFilter = [];
        $vatFilter["vat"] = 1;

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                'dataProvider'        => $dataProvider,
                'searchModel'         => $searchModel,
                'wmodel'              => $wmodel,
                'agentName'           => $agentModel['denom'],
                'storeName'           => $storeModel['denom'],
                'isAndroid'           => $isAndroid,
                'vatData'             => $vatData,
                'RkWaybilldataSearch' => $vatFilter,
            ]);
        } else {
            return $this->render($vi, [
                'searchModel'         => $searchModel,
                'dataProvider'        => $dataProvider,
                'wmodel'              => $wmodel,
                'agentName'           => $agentModel['denom'],
                'storeName'           => $storeModel['denom'],
                'isAndroid'           => $isAndroid,
                'vatData'             => $vatData,
                'RkWaybilldataSearch' => $vatFilter,
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

        $sql = "SELECT id FROM rk_waybill_data WHERE waybill_id = :w_wid" . $vat_add;
        $result0 = Yii::$app->db_api->createCommand($sql, [':w_wid' => $waybill_id])->queryAll();

        if (count($result0 > 0)) {
            foreach ($result0 as $resu) {
                $id = $resu["id"];
                $sql = "SELECT product_id,org,product_rid,koef FROM rk_waybill_data WHERE id = :w_id";
                $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $id])->queryAll();
                $product_id = $result[0]["product_id"];
                $product_rid = $result[0]["product_rid"];
                $org_id = $result[0]["org"];
                $koef = $result[0]["koef"];

                $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

                if (is_null($product_rid)) {
                    $product_rid = 0;
                }

                if ($product_rid != 0) {
                    $sql = "SELECT unit_rid,unitname FROM rk_product WHERE id = :w_id";
                    $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $product_rid])->queryAll();
                    $munit = $result[0]["unitname"];
                    $munit_id = $result[0]["unit_rid"];
                } else {
                    $munit = null;
                    $munit_id = null;
                }

                $sql = "SELECT COUNT(*) FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
                $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::RK_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
                if ($existence == 0) {
                    $sql = "SELECT store_rid/*,corr_rid*/ FROM rk_waybill WHERE id = :w_wi";
                    $res = Yii::$app->db_api->createCommand($sql, [':w_wi' => $waybill_id])->queryAll();
                    $store = $res[0]["store_rid"];
                    /*$cagent = $res[0]["corr_rid"];
                    $sql = "SELECT COUNT(*) FROM rk_agent WHERE rid = :w_uuid AND acc = :w_org";
                    $result = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
                    if ($result == 0) {
                        $agent = null;
                    } else {
                        $agent = $cagent;
                    }*/
                    $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                                VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), null, NOW())";
                    $result = Yii::$app->db_api->createCommand($sql, [
                        ':w_s'       => Registry::RK_SERVICE_ID,
                        ':w_org'     => $org_id,
                        ':w_product' => $product_id,
                        ':w_supp'    => $supp_id,
                        ':w_spid'    => $product_rid,
                        ':w_unitr'   => $munit_id,
                        ':w_store'   => $store,
                        ':w_koef'    => $koef,
                        ':w_vat'     => $vat,
                    ])->execute();
                    if (!(is_null($product_rid))) {
                        $sql = "UPDATE all_map SET linked_at = NOW() WHERE org_id = :w_org AND product_id = :w_product AND service_id = :w_s";
                        $result = Yii::$app->db_api->createCommand($sql, [':w_org' => $org_id, ':w_product' => $product_id, ':w_s' => Registry::RK_SERVICE_ID])->execute();
                    }
                } else {
                    $sql = "SELECT id FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
                    $id_all_map = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::RK_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
                    $sql = "UPDATE all_map SET vat = :w_vat, updated_at = NOW() WHERE id = :w_id";
                    $result = Yii::$app->db_api->createCommand($sql, [':w_vat' => $vat, ':w_id' => $id_all_map])->execute();
                }
            }
        }

        $sql = 'UPDATE rk_waybill_data SET vat = :vat, updated_at = now() WHERE waybill_id = :id' . $vat_add;
        $rress = Yii::$app->db_api
            ->createCommand($sql, [':vat' => $vat, ':id' => $waybill_id])->execute();

        return $this->redirect(['map', 'waybill_id' => $model->id, 'way' => 0, 'RkWaybilldataSearch[vat]' => $vatf, 'sort' => $sort, 'page' => $page]);
    }

    public function actionChvat($id, $koef, $vatf, $sort = 'fproductnameProduct', $vat, $page, $way)
    {
        $model = $this->findDataModel($id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE rk_waybill_data SET vat = :vat, updated_at = now() WHERE id = :id', [':vat' => $vat, ':id' => $id])->execute();

        $sql = "SELECT waybill_id,product_id,org,product_rid FROM rk_waybill_data WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $id])->queryAll();
        $waybill_id = $result[0]["waybill_id"];
        $product_id = $result[0]["product_id"];
        $product_rid = $result[0]["product_rid"];
        $org_id = $result[0]["org"];

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        if (is_null($product_rid)) {
            $product_rid = 0;
        }

        if ($product_rid != 0) {
            $sql = "SELECT unit_rid,unitname FROM rk_product WHERE id = :w_id";
            $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $product_rid])->queryAll();
            $munit = $result[0]["unitname"];
            $munit_id = $result[0]["unit_rid"];
        } else {
            $munit = null;
            $munit_id = null;
        }

        $sql = "SELECT COUNT(*) FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
        $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::RK_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
        if ($existence == 0) {
            $sql = "SELECT store_rid/*,corr_rid*/ FROM rk_waybill WHERE id = :w_wi";
            $res = Yii::$app->db_api->createCommand($sql, [':w_wi' => $waybill_id])->queryAll();
            $store = $res[0]["store_rid"];
            /*$cagent = $res[0]["corr_rid"];
            $sql = "SELECT COUNT(*) FROM rk_agent WHERE rid = :w_uuid AND acc = :w_org";
            $result = Yii::$app->db_api->createCommand($sql, [':w_uuid' => $cagent, ':w_org' => $org_id])->queryScalar();
            if ($result == 0) {
                $agent = null;
            } else {
                $agent = $cagent;
            }*/
            $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                        VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), null, NOW())";
            $result = Yii::$app->db_api->createCommand($sql, [
                ':w_s'       => Registry::RK_SERVICE_ID,
                ':w_org'     => $org_id,
                ':w_product' => $product_id,
                ':w_supp'    => $supp_id,
                ':w_spid'    => $product_rid,
                ':w_unitr'   => $munit_id,
                ':w_store'   => $store,
                ':w_koef'    => $koef,
                ':w_vat'     => $vat,
            ])->execute();
            if (!(is_null($product_rid))) {
                $sql = "UPDATE all_map SET linked_at = NOW() WHERE org_id = :w_org AND product_id = :w_product AND service_id = :w_s";
                $result = Yii::$app->db_api->createCommand($sql, [':w_org' => $org_id, ':w_product' => $product_id, ':w_s' => Registry::RK_SERVICE_ID])->execute();
            }
        } else {
            $sql = "SELECT id FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product";
            $id_all_map = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::RK_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $product_id])->queryScalar();
            $sql = "UPDATE all_map SET vat = :w_vat, updated_at = NOW() WHERE id = :w_id";
            $result = Yii::$app->db_api->createCommand($sql, [':w_vat' => $vat, ':w_id' => $id_all_map])->execute();
        }
        return $this->redirect(['map', 'waybill_id' => $model->waybill->id, 'page' => $page, 'way' => $way, 'RkWaybilldataSearch[vat]' => $vatf, 'sort' => $sort]);
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

            $sql = "( select id, CONCAT(denom, ' (' ,unitname, ')') as txt from rk_product where acc = " . $organization_id . " and denom = '" . $term . "' )" .
                "union ( select id, CONCAT(denom, ' (' ,unitname, ')') as txt from rk_product  where acc = " . $organization_id . " and denom like '" . $term . "%' limit 15 )" .
                "union ( select id, CONCAT(denom, ' (' ,unitname, ')') as txt from rk_product where  acc = " . $organization_id . " and denom like '%" . $term . "%' limit 10 )" .
                "order by case when length(trim(txt)) = length('" . $term . "') then 1 else 2 end, txt; ";

            $data = Yii::$app->get('db_api')->createCommand($sql)->queryAll();
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
            $orgId = User::findOne(Yii::$app->user->id)->organization_id;
            //$constId = RkDicconst::findOne(['denom' => 'main_org']);
            //$parentId = RkPconst::findOne(['const_id' => $constId->id, 'org' => $orgId]);
            //$organizationID = !is_null($parentId) ? $parentId->value : $orgId;
            //$andWhere = '';
            //$arr = ArrayHelper::map(RkSelectedProduct::find()->where(['organization_id' => $organizationID])->all(), 'id', 'product_id');
            //if (count($arr)) {
            //    $andWhere = 'AND id in (' . implode(',', $arr) . ')';
            //}

            $sql = <<<SQL
            SELECT id, CONCAT(denom, ' (' ,unitname, ')') as txt FROM (
                  (SELECT id, denom, unitname FROM rk_product WHERE acc = :org_id AND denom = :term)
                    UNION
                  (SELECT id, denom, unitname FROM rk_product WHERE acc = :org_id AND denom LIKE :term_ LIMIT 15)
                    UNION
                  (SELECT id, denom, unitname FROM rk_product WHERE acc = :org_id AND denom LIKE :_term_ LIMIT 10)
                  ORDER BY CASE WHEN CHAR_LENGTH(trim(denom)) = CHAR_LENGTH(:term) 
                     THEN 1
                     ELSE 2
                  END
            ) as t
SQL;

            /**
             * @var $db Connection
             */
            $db = Yii::$app->db_api;
            $data = $db->createCommand($sql)
                ->bindValues([
                    'term'   => $term,
                    'term_'  => $term . '%',
                    '_term_' => '%' . $term . '%',
                    'org_id' => $orgId
                ])
                ->queryAll();
            $out = array_values($data);
        } else {
            $orgId = User::findOne(Yii::$app->user->id)->organization_id;
            //$constId = RkDicconst::findOne(['denom' => 'main_org']);
            //$parentId = RkPconst::findOne(['const_id' => $constId->id, 'org' => $orgId]);
            //$organizationID = !is_null($parentId) ? $parentId->value : $orgId;
            $sql = "SELECT id, CONCAT(denom, ' (' ,unitname, ')') as txt FROM rk_product WHERE acc = " . $orgId . ' ORDER BY denom LIMIT 100';

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
     * @param $page
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id, $page)
    {
        $model = $this->findModel($id);
        $lic = $this->checkLic();
        $vi = $lic ? 'update' : '/default/_nolic';

        if ($model->load(Yii::$app->request->post())) {

            $existingWaybill = RkWaybill::find()->where(['order_id' => $model->order_id, 'store_rid' => $model->store_rid])->andWhere(['!=', 'id', $id])->one();
            if (!empty($existingWaybill)) {
                $model = RkWaybill::moveContentToExistingWaybill($model, $existingWaybill);
            }

            if ($model->corr_rid == 0) {
                $model->corr_rid = null;
            }
            if ($model->store_rid == 0) {
                $model->store_rid = null;
            }
            $sql = "SELECT COUNT(*) FROM rk_waybill_data WHERE waybill_id = :w_wid AND product_rid IS NULL";
            $kolvo_nesopost = Yii::$app->db_api->createCommand($sql, [':w_wid' => $model->id])->queryScalar();
            if (($model->corr_rid === null) or ($model->num_code === null) or ($model->text_code === null) or ($model->store_rid === null)) {
                $shapka = 0;
            } else {
                $shapka = 1;
            }
            if ($kolvo_nesopost == 0) {
                if ($shapka == 1) {
                    $model->readytoexport = 1;
                    $model->status_id = 5;
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
            return $this->redirect(['/clientintegr/rkws/waybill/index', 'way' => $model->order_id, 'page' => $page]);
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
            echo "Can't find order";
            die();
        }

        $const = RkDicconst::findOne(['denom' => 'auto_unload_invoice'])->getPconstValue();

        if ($const !== '0') {
            RkWaybill::createWaybill($order_id);
            return $this->redirect(['/clientintegr/rkws/waybill/index', 'page' => $page, 'way' => $order_id]);
        } else {
            $model = new RkWaybill();
            $model->order_id = $order_id;
            $model->status_id = 1;
            $model->org = $ord->client_id;

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['/clientintegr/rkws/waybill/index', 'page' => $page, 'way' => $model->order_id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
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
     * @param      $url
     * @param      $name
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
        /** @var RkWaybill $model */
        $model = $this->findModel($id);
        $error = '';

        if (!$model) {
            $error .= 'Не удалось найти накладную. ';
        }

        if ($model->readytoexport == 0) {
            $error .= 'Накладная к выгрузке не готова! ';
        }

        if ($error == '') {
            $res = new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper();
            if ($res->sendWaybill($id)) {
                $model->refresh();
                if ($model->status_id != 2) {
                    $error .= 'Ошибка при отправке. ';
                }
            } else {
                $error .= 'Выгрузка не удалась. ';
            }
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

        $model = RkWaybilldata::findOne($id);
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

        return $this->redirect(['map', 'waybill_id' => $waybill_id, 'way' => 0, 'sort' => $sort, 'RkWaybilldataSearch[vat]' => $vatf, 'page' => 1]);
    }

    public function actionEditGlobal()
    {
        $product_rid = Yii::$app->request->post('id');
        $number = Yii::$app->request->post('number');

        $org_id = User::findOne(Yii::$app->user->id)->organization_id;

        $sql = "SELECT unit_rid,unitname FROM rk_product WHERE id = :w_id";
        $result = Yii::$app->db_api->createCommand($sql, [':w_id' => $product_rid])->queryAll();
        $munit = $result[0]["unitname"];
        $munit_id = $result[0]["unit_rid"];

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($number);

        $sql = "SELECT id, koef FROM all_map WHERE service_id = :w_s AND org_id = :w_org AND product_id = :w_product LIMIT 1";
        $existence = Yii::$app->db_api->createCommand($sql, [':w_s' => Registry::RK_SERVICE_ID, ':w_org' => $org_id, ':w_product' => $number])->queryAll();
        if (!$existence) {
            $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                        VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), NOW(), NOW())";
            $result = Yii::$app->db_api->createCommand($sql, [
                ':w_s'       => Registry::RK_SERVICE_ID,
                ':w_org'     => $org_id,
                ':w_product' => $number,
                ':w_supp'    => $supp_id,
                ':w_spid'    => $product_rid,
                ':w_unitr'   => $munit_id,
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
        $sql = "SELECT wd.id FROM rk_waybill_data wd LEFT JOIN rk_waybill w ON wd.waybill_id = w.id 
                LEFT JOIN " . $dbName . "." . Order::tableName() . " o ON w.order_id = o.id 
                WHERE w.status_id = 1 AND o.vendor_id = :w_supp AND o.client_id = :w_org AND wd.product_id = :w_pid AND wd.product_rid IS NULL";
        $massivs = Yii::$app->db_api->createCommand($sql, [':w_pid' => $number, ':w_supp' => $supp_id, ':w_org' => $org_id])->queryAll();
        $ids = '';
        foreach ($massivs as $massiv) {
            $ids .= $massiv['id'] . ',';
        }
        $ids = rtrim($ids, ',');
        if ($ids) {
            $sql = "UPDATE rk_waybill_data SET product_rid = :w_spid, munit_rid = :w_munit, linked_at = NOW(), updated_at = NOW() WHERE id in (" . $ids . ")";
            $result = Yii::$app->db_api->createCommand($sql, [':w_spid' => $product_rid, ':w_munit' => $munit_id])->execute();
        }

        return $munit;
    }
}
