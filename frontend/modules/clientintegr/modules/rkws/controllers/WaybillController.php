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
use yii\db\Query;
use api\common\models\AllMaps;
use api\common\models\RkProduct;

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

        $waybill_data = RkWaybillData::find()->where('id = :w_id', [':w_id' => $number])->one();
        $waybill_id = $waybill_data->waybill_id;
        $product_id = $waybill_data->product_id;
        $org_id = $waybill_data->org;
        $vat = $waybill_data->vat;
        $koef = $waybill_data->koef;

        $product = RkProduct::find()->where('id = :w_id', [':w_id' => $product_rid])->one();
        $munit = $product->unitname;
        $munit_id = $product->unit_rid;

        $waybill_data->product_rid = $product_rid;
        $waybill_data->munit_rid = $munit_id;
        $waybill_data->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
        if (!$waybill_data->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной R-Keeper не удалось.']));
        }

        $kolvo_nesopost = RkWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $waybill_id])->andWhere(['product_rid' => null])->count();
        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        $waybill = RkWaybill::find()->where('id = :w_wid', [':w_wid' => $waybill_id])->one();
        $agent_uuid = $waybill->corr_rid;
        $num_code = $waybill->num_code;
        $text_code = $waybill->text_code;
        $store_id = $waybill->store_rid;
        if (($agent_uuid === null) or ($num_code === null) or ($text_code === null) or ($store_id === null)) {
            $shapka = 0;
        } else {
            $shapka = 1;
        }

        if ($kolvo_nesopost == 0) {
            if ($shapka == 1) {
                $waybill->readytoexport = 1;
                $waybill->status_id = 5;
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
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную R-Keeper не удалось.']));
        }

        if ($button == 'forever') {
            $existence = AllMaps::find()->where(['service_id' => Registry::RK_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
            if ($existence == 0) {
                $position = new AllMaps();
                $position->service_id = Registry::RK_SERVICE_ID;
                $position->org_id = $org_id;
                $position->product_id = $product_id;
                $position->supp_id = $supp_id;
                $position->serviceproduct_id = $product_rid;
                $position->store_rid = $store_id;
                $position->koef = $koef;
                $position->vat = $vat;
                $position->is_active = 1;
                $sql = "INSERT INTO all_map (service_id, org_id, product_id, supp_id, serviceproduct_id, unit_rid, store_rid, koef, vat, is_active, created_at, linked_at, updated_at)
                        VALUES (:w_s, :w_org, :w_product, :w_supp, :w_spid, :w_unitr, :w_store, :w_koef , :w_vat, 1, NOW(), NOW(), NOW())";
            } else {
                $position = AllMaps::find()->where(['service_id' => Registry::RK_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
                $position->serviceproduct_id = $product_rid;
            }
            $position->unit_rid = $munit_id;
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

        $waybill_data = RkWaybillData::find()->where('id = :w_id', [':w_id' => $koef_id])->one();
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

        if (is_null($product_rid)) {
            $product_rid = 0;
        }

        if ($product_rid != 0) {
            $product = RkProduct::find()->where('id = :w_id', [':w_id' => $product_rid])->one();
            $munit = $product->unitname;
            $munit_id = $product->unit_rid;
        } else {
            $munit = null;
            $munit_id = null;
        }

        $waybill_data->quant = $quant_new;
        $waybill_data->koef = $koef;
        if (!$waybill_data->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной R-Keeper не удалось.']));
        }
        if ($buttons == 'forever') {
            $existence = AllMaps::find()->where(['service_id' => Registry::RK_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
            if ($existence == 0) {
                $waybill = RkWaybill::find()->where('id = :w_wid', [':w_wid' => $waybill_id])->one();
                $store = $waybill->store_rid;
                $position = new AllMaps();
                $position->service_id = Registry::RK_SERVICE_ID;
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
                $position = AllMaps::find()->where(['service_id' => Registry::RK_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
                $position->koef = $koef;
                $position->vat = $vat;
            }
            $position->unit_rid = $munit_id;
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
        $model = RkWaybill::find()->andWhere('id= :id', [':id' => $waybill_id])->one();
        $vatData = VatData::getVatList();
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.waybill.not.find', ['ru' => 'Приходной накладной R-Keeper с таким номером не существует.']));
        }

        // Используем определение браузера и платформы для лечения бага с клавиатурой Android с помощью USER_AGENT (YT SUP-3)

        $userAgent = \xj\ua\UserAgent::model();
        /* @var \xj\ua\UserAgent $userAgent */

        $platform = $userAgent->platform;
        $browser = $userAgent->browser;

        if (stristr($platform, 'android') OR stristr($browser, 'android')) {
            $isAndroid = true;
        } else $isAndroid = false;

        $searchModel = new RkWaybilldataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $agentModel = RkAgent::findOne(['rid' => $model->corr_rid, 'acc' => $model->org]);
        $storeModel = RkStore::findOne(['rid' => $model->store_rid]);

        $lic = $this->checkLic();
        $vi = $lic ? 'indexmap' : '/default/_nolic';
        $vatFilter = [];
        $vatFilter["vat"] = 1;

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                'dataProvider'        => $dataProvider,
                'searchModel'         => $searchModel,
                'wmodel'              => $model,
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
                'wmodel'              => $model,
                'agentName'           => $agentModel['denom'],
                'storeName'           => $storeModel['denom'],
                'isAndroid'           => $isAndroid,
                'vatData'             => $vatData,
                'RkWaybilldataSearch' => $vatFilter,
            ]);
        }
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
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.waybill.not.find', ['ru' => 'Приходной накладной R-Keeper с таким номером не существует.']));
        }

        $waybill_datas = RkWaybillData::find()->where(['waybill_id' => $wbill_id])->all();
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
                throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной R-Keeper не удалось.']));
            }
        }
        $wmodel->vat_included = $vat;
        if (!$wmodel->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную R-Keeper не удалось.']));
        }
        return true;
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
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.waybill.not.find', ['ru' => 'Приходной накладной R-Keeper с таким номером не существует.']));
        }

        if ($wmodel->vat_included) {
            $model->sum = round($model->defsum / (1 + $model->vat / 10000), 2);
        } else {
            $model->sum = $model->defsum;
        }

        if (!$model->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную R-Keeper не удалось.']));
        }

        return $this->redirect(['map', 'waybill_id' => $model->waybill->id]);
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
                    "id"       => "id",
                    "denom"    => "denom",
                    "unitname" => "unitname"
                ])
                ->from('rk_product')
                ->andWhere(['acc' => $orgId])
                ->andWhere("denom LIKE :term", [':term' => $term . '%'])
                ->orderBy(['denom' => SORT_ASC, "unitname" => SORT_ASC])
                ->limit(15);

            $query3 = (new Query())
                ->select([
                    "id"       => "id",
                    "denom"    => "denom",
                    "unitname" => "unitname"
                ])
                ->from('rk_product')
                ->andWhere(['acc' => $orgId])
                ->andWhere("denom LIKE :term", [':term' => '%' . $term . '%'])
                ->orderBy(['denom' => SORT_ASC, "unitname" => SORT_ASC])
                ->limit(10);

            $query1 = (new Query())
                ->select([
                    "id"       => "id",
                    "denom"    => "denom",
                    "unitname" => "unitname",
                ])
                ->from('rk_product')
                ->union($query2)
                ->union($query3)
                ->andWhere(['acc' => $orgId])
                ->andWhere(['denom' => ':term'], [':term' => $term])
                ->orderBy(['denom' => SORT_ASC, "unitname" => SORT_ASC])
                ->limit(10);

            $query = (new Query())
                ->select([
                    "id"  => "id",
                    "txt" => "CONCAT(denom, ' (' ,unitname, ')')",
                ])
                ->from("(" . $query1->createCommand()->getRawSql() . ") t");
            $result = $query->all(\Yii::$app->get('db_api'));
            $out = array_values($result);

        } else {
            $orgId = User::findOne(Yii::$app->user->id)->organization_id;

            $query = (new Query())
                ->select([
                    "id"  => "id",
                    "txt" => "CONCAT(denom, ' (' ,unitname, ')')"
                ])
                ->from('rk_product')
                ->andWhere(['acc' => $orgId])
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
        $lic = $this->checkLic();
        $vi = $lic ? 'update' : '/default/_nolic';

        if ($model->load(Yii::$app->request->post())) {

            $existingWaybill = RkWaybill::find()->where(['order_id' => $model->order_id, 'store_rid' => $model->store_rid])->andWhere(['!=', 'id', $id])->one();
            if (!empty($existingWaybill)) {
                $model = RkWaybill::moveContentToExistingWaybill($model, $existingWaybill);
            }

            if ($model->corr_rid == '') {
                $model->corr_rid = null;
            }
            if ($model->store_rid == 0) {
                $model->store_rid = null;
            }
            $kolvo_nesopost = RkWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $model->id])->andWhere(['product_rid' => null])->count();
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
            if (!$model->save()) {
                throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную R-Keeper не удалось.']));
            }
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
            throw new NotFoundHttpException(Yii::t('error', 'api.controllers.order.not.find', ['ru' => 'Заказа с таким номером не существует.']));
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

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if (!$model->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную R-Keeper не удалось.']));
                } else {
                    $model->createWaybillData();
                }
                $kolvo_nesopost = RkWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $model->id])->andWhere(['product_rid' => null])->count();
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
                if (!$model->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.not.save', ['ru' => 'Сохранить приходную накладную R-Keeper не удалось.']));
                }
                return $this->redirect(['/clientintegr/rkws/waybill/index', 'page' => $page, 'way' => $model->order_id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
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

        if (!$model) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.waybill.not.find', ['ru' => 'Приходной накладной R-Keeper с таким номером не существует.']));
        }

        if ($model->readytoexport == 0) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.waybill.not.ready', ['ru' => 'Приходная накладная R-Keeper к выгрузке не готова.']));
        }

        $res = new \frontend\modules\clientintegr\modules\rkws\components\WaybillHelper();
        if ($res->sendWaybill($id)) {
            $model->refresh();
            if ($model->status_id != 2) {
                throw new NotFoundHttpException(Yii::t('error', 'api.rkws.waybill.not.send', ['ru' => 'Приходную накладную R-Keeper не удалось выгрузить.']));
            }
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.waybill.not.send', ['ru' => 'Приходную накладную R-Keeper не удалось выгрузить.']));
        }

        Yii::$app->session->set("rkws_waybill", $model->order_id);
        return 'true';
    }

    public function actionMakevat($waybill_id, $vat, $vatf, $sort, $page)
    {
        $page_size = 20;
        $model = $this->findModel($waybill_id);

        if ($vatf == 1) {
            $waybill_datas = RkWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $waybill_id])->all();
        } else {
            $waybill_datas = RkWaybillData::find()->where('waybill_id = :w_wid', [':w_wid' => $waybill_id])->andWhere(['vat' => $vatf])->all();
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
                $unit = $waybill_data->munit_rid;

                $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

                $existence = AllMaps::find()->where(['service_id' => Registry::RK_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
                if ($existence == 0) {
                    $waybill = RkWaybill::find()->where('id = :w_wid', [':w_wid' => $waybill_id])->one();
                    $store = $waybill->store_rid;
                    $position = new AllMaps();
                    $position->service_id = Registry::RK_SERVICE_ID;
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
                    $position = AllMaps::find()->where(['service_id' => Registry::RK_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
                    $position->vat = $vat;
                }
                $position->unit_rid = $unit;
                if (!$position->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
                }
                $waybill_data->vat = $vat;
                if (!$waybill_data->save()) {
                    throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной R-Keeper не удалось.']));
                }
            }
        }
        return $this->redirect(['map', 'waybill_id' => $model->id, 'way' => 0, 'RkWaybilldataSearch[vat]' => $vatf, 'sort' => $sort, 'page' => $page]);
    }

    public function actionChvat($id, $koef, $vatf, $sort = 'fproductnameProduct', $vat, $page, $way)
    {
        $waybill_data = $this->findDataModel($id);

        $waybill_data->vat = $vat;
        if (!$waybill_data->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной R-Keeper не удалось.']));
        }

        $product_id = $waybill_data->product_id;
        $product_rid = $waybill_data->product_rid;
        $org_id = $waybill_data->org;

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($product_id);

        if (is_null($product_rid)) {
            $product_rid = 0;
        }

        if ($product_rid != 0) {
            $product = RkProduct::find()->where('id = :w_id', [':w_id' => $product_rid])->one();
            $munit = $product->unitname;
            $munit_id = $product->unit_rid;
        } else {
            $munit = null;
            $munit_id = null;
        }

        $existence = AllMaps::find()->where(['service_id' => Registry::RK_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->count();
        if ($existence == 0) {
            $waybill = RkWaybill::find()->where(['id' => $waybill_data->waybill_id])->one();
            $store = $waybill->store_rid;
            $position = new AllMaps();
            $position->service_id = Registry::RK_SERVICE_ID;
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
            $position = AllMaps::find()->where(['service_id' => Registry::RK_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $product_id])->one();
            $position->vat = $vat;
        }
        $position->unit_rid = null;
        if (!$position->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
        }
        return $this->redirect(['map', 'waybill_id' => $waybill_data->waybill->id, 'page' => $page, 'way' => $way, 'RkWaybilldataSearch[vat]' => $vatf, 'sort' => $sort]);
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
            throw new NotFoundHttpException(Yii::t('error', 'api.rkws.waybill.not.find', ['ru' => 'Приходной накладной R-Keeper с таким номером не существует.']));
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

        $product = RkProduct::find()->where('id = :w_id', [':w_id' => $product_rid])->one();
        $munit = $product->unitname;
        $munit_id = $product->unit_rid;

        $supp_id = \common\models\CatalogBaseGoods::getSuppById($number);

        $existence = AllMaps::find()->where(['service_id' => Registry::RK_SERVICE_ID, 'org_id' => $org_id, 'product_id' => $number])->one();
        if (!$existence) {
            $position = new AllMaps();
            $position->service_id = Registry::RK_SERVICE_ID;
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
        $position->unit_rid = $munit_id;
        if (!$position->save()) {
            throw new NotFoundHttpException(Yii::t('error', 'api.allmaps.position.not.save', ['ru' => 'Сохранить позицию в глобальном сопоставлении не удалось.']));
        }

        $orders = Order::find()->where(['vendor_id' => $supp_id, 'client_id' => $org_id])->all();
        foreach ($orders as $order) {
            $waybills = RkWaybill::find()->where(['order_id' => $order->id, 'status_id' => 1])->all();
            foreach ($waybills as $waybill) {
                $waybill_datas = RkWaybillData::find()->where(['waybill_id' => $waybill->id, 'product_id' => $number, 'product_rid' => null])->all();
                foreach ($waybill_datas as $waybill_data) {
                    $waybill_data->product_rid = $product_rid;
                    $waybill_data->munit_rid = $munit_id;
                    $waybill_data->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:i:s');
                    if (!$waybill_data->save()) {
                        throw new NotFoundHttpException(Yii::t('error', 'api.rkws.controllers.waybill.data.not.save', ['ru' => 'Сохранить позицию в приходной накладной R-Keeper не удалось.']));
                    }
                }
            }
        }

        return $munit;
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
}
