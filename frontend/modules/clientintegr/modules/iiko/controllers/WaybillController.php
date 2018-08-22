<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use api\common\models\iiko\iikoAgent;
use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use api\common\models\iiko\iikoSelectedProduct;
use api\common\models\VatData;
use api\common\models\iiko\iikoStore;
use common\models\Organization;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use Yii;
use common\models\User;
use yii\db\Connection;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use yii\web\NotFoundHttpException;
use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoService;
use api\common\models\iiko\iikoWaybill;
use api\common\models\iiko\iikoWaybillData;
use yii\web\Response;
use yii\helpers\Url;
use api\common\models\iikoWaybillDataSearch;
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
            'edit' => [
                'class' => EditableColumnAction::className(),
                'modelClass' => iikoWaybillData::className(),
                'outputValue' => function ($model, $attribute) {
                    $value = $model->$attribute;
                    if ($attribute === 'pdenom') {
                        if (is_numeric($model->pdenom)) {
                            $rkProd = iikoProduct::findOne(['id' => $value]);
                            $model->product_rid = $rkProd->id;
                            $model->munit = $rkProd->unit;
                            $model->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                            $model->save(false);
                            return $rkProd->denom;
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
                'modelClass' => iikoWaybillData::className(),
                'outputValue' => function ($model, $attribute) {
                    if ($attribute === 'vat') {
                        return $model->$attribute / 100;
                    } else {
                        $model->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                        //$model->save(false);
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
            throw new BadRequestHttpException('Access denied');
        }
        $search = new SearchOrdersComponent();
        $search->getRestaurantIntegration(SearchOrdersComponent::INTEGRATION_TYPE_IIKO, $searchModel,
            $organization->id, $this->currentUser->organization_id, $wbStatuses, ['pageSize' => 20],
            ['defaultOrder' => ['id' => SORT_DESC]]);
        $lisences = $organization->getLicenseList();
        // $lisences = iikoService::getLicense();
        if (isset($lisences['iiko']) && $lisences['iiko']) {
            $lisences = $lisences['iiko'];
            $view = 'index';
        } else {
            $view = '/default/_nolic';
            $lisences = NULL;
        }


        $renderParams = [
            'searchModel' => $searchModel,
            'affiliated' => $search->affiliated,
            'dataProvider' => $search->dataProvider,
            'searchParams' => $search->searchParams,
            'businessType' => $search->businessType,
            'lic' => $lisences,
            'visible' => iikoPconst::getSettingsColumn($organization->id),
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
    public function actionMap()
    {
        $model = iikoWaybill::findOne(Yii::$app->request->get('waybill_id'));
        $vatData = VatData::getVatList();
        if (!$model) {
            die("Cant find wmodel in map controller");
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

        $lic = iikoService::getLicense();
        $view = $lic ? 'indexmap' : '/default/_nolic';
        $params = [
            'dataProvider' => $dataProvider,
            'wmodel' => $model,
            'agentName' => $agentModel->denom,
            'storeName' => $storeModel->denom,
            'isAndroid' => $isAndroid,
            'searchModel' => $searchModel,
            'vatData' => $vatData,
            'parentBusinessId' => $obConstModel->getPconstValue(),
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

        $wmodel = iikoWaybill::findOne($wbill_id);

        if (!$wmodel) {
            die('Waybill model is not found');
        }

        if ($is_checked) { // Добавляем НДС
            $sql = "UPDATE iiko_waybill_data SET sum=round(sum/(vat/10000+1),2) WHERE waybill_id = :w_id";
            $vat = 1;
        } else { // Убираем НДС
            $sql = "UPDATE iiko_waybill_data SET sum=defsum WHERE waybill_id = :w_id";
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

        $wayModel = iikoWaybill::findOne($model->waybill_id);
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
        $out = [];
        if (!is_null($term)) {
            $orgId = User::findOne(Yii::$app->user->id)->organization_id;
            $constId = iikoDicconst::findOne(['denom' => 'main_org']);
            $parentId = iikoPconst::findOne(['const_id' => $constId->id, 'org' => $orgId]);
            $organizationID = !is_null($parentId) ? $parentId->value : $orgId;
            $andWhere = '';
            $arr = ArrayHelper::map(iikoSelectedProduct::find()->where(['organization_id' => $organizationID])->all(), 'id', 'product_id');
            if (count($arr)) {
                $andWhere = 'AND id in (' . implode(',', $arr) . ')';
            }

            $sql = <<<SQL
            SELECT id, denom as text FROM (
                  (SELECT id, denom FROM iiko_product WHERE is_active = 1 AND org_id = :org_id AND denom = :term  $andWhere)
                    UNION
                  (SELECT id, denom FROM iiko_product WHERE is_active = 1 AND org_id = :org_id AND denom LIKE :term_ $andWhere LIMIT 15)
                    UNION
                  (SELECT id, denom FROM iiko_product WHERE is_active = 1 AND org_id = :org_id AND denom LIKE :_term_ $andWhere LIMIT 10)
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
                    'term' => $term,
                    'term_' => $term . '%',
                    '_term_' => '%' . $term . '%',
                    'org_id' => $organizationID
                ])
                ->queryAll();
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
            $query->select(['id' => 'uuid', 'text' => 'denom'])
                ->from('iiko_agent')
                ->where('org_id = :acc', [':acc' => $org])
                ->andWhere(['is_active' => 1])
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
        $lic = iikoService::getLicense();
        $vi = $lic ? 'update' : '/default/_nolic';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->doc_date = Yii::$app->formatter->asDate($model->doc_date . ' 16:00:00', 'php:Y-m-d H:i:s');
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
        $ord = \common\models\Order::findOne(['id' => $order_id]);

        if (!$ord) {
            echo "Can't find order";
            die();
        }

        $model = new iikoWaybill();
        $model->order_id = $order_id;
        $model->status_id = 1;
        $model->org = $ord->client_id;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->doc_date = Yii::$app->formatter->asDate($model->doc_date . ' 16:00:00', 'php:Y-m-d H:i:s');//date('d.m.Y', strtotime($model->doc_date));
            $model->save();
            return $this->redirect([$this->getLastUrl() . 'way=' . $model->order_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Отправляем накладную
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

        $api = iikoApi::getInstance();
        try {
            if (!Yii::$app->request->isAjax) {
                throw new \Exception('Only ajax method');
            }

            if (is_null($id)) {
                $id = Yii::$app->request->post('id');
            }
            $model = $this->findModel($id);

            if (!$model) {
                throw new \Exception('Не удалось найти накладную');
            }

            if ($api->auth()) {
                $response = $api->sendWaybill($model);
                if ($response !== true) {
                    throw new \Exception('Ошибка при отправке. ' . $response);
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
                $model->save();
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
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }
        if (count($ids) == $scsCount) {
            return ['success' => true, 'count' => $scsCount];
        }
        return ['success' => false, 'error' => 'Выгруженно только ' . $scsCount . ' накладных'];
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

        $api = iikoApi::getInstance();
        try {
            if (!Yii::$app->request->isAjax) {
                throw new \Exception('Only ajax method');
            }

            $id = Yii::$app->request->post('id');
            $model = $this->findModel($id);

            if (!$model) {
                throw new \Exception('Не удалось найти накладную');
            }

            if ($model->readytoexport == 0) {
                throw new \Exception('Не все товары сопоставлены!');
            }

            if ($api->auth()) {
                $response = $api->sendWaybill($model);
                if ($response !== true) {
                    throw new \Exception('Ошибка при отправке. ' . $response);
                }
                $model->status_id = 2;
                $model->save();
            } else {
                throw new \Exception('Не удалось авторизоваться');
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

    public function actionMakevat($waybill_id, $vat)
    {

        $model = $this->findModel($waybill_id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE iiko_waybill_data SET vat = :vat, linked_at = now() WHERE waybill_id = :id', [':vat' => $vat, ':id' => $waybill_id])->execute();

        return $this->redirect(['map', 'waybill_id' => $model->id]);
    }


    public function actionChvat($id, $vat)
    {

        $model = $this->findDataModel($id);

        $rress = Yii::$app->db_api
            ->createCommand('UPDATE iiko_waybill_data SET vat = :vat, linked_at = now() WHERE id = :id', [':vat' => $vat, ':id' => $id])->execute();

        return $this->redirect(['map', 'waybill_id' => $model->waybill->id]);

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
     * @param $id ИД накладной
     * @return iikoWaybill
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = iikoWaybill::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return iikoWaybillData
     * @throws NotFoundHttpException
     */
    protected function findDataModel($id)
    {
        $model = iikoWaybillData::findOne($id);
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

        $model = iikoWaybillData::findOne($id);
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
}
