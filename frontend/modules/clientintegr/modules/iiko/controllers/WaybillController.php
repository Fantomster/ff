<?php

namespace frontend\modules\clientintegr\modules\iiko\controllers;

use common\models\search\OrderSearch;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use yii\web\NotFoundHttpException;
use api\common\models\iiko\iikoProduct;
use api\common\models\iiko\iikoService;
use api\common\models\iiko\iikoWaybill;
use api\common\models\iiko\iikoWaybillData;
use yii\web\Response;


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
                'modelClass' => iikoWaybillData::className(),
                'outputValue' => function ($model, $attribute) {
                    $value = $model->$attribute;
                    if ($attribute === 'pdenom') {
                        if (is_numeric($model->pdenom)) {
                            $rkProd = iikoProduct::findOne(['id' => $value]);
                            $model->product_rid = $rkProd->id;
                            $model->munit = $rkProd->unit;
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
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->searchWaybill(Yii::$app->request->queryParams);
        $lic = iikoService::getLicense();
        $view = $lic ? 'index' : '/default/_nolic';
        $params = [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'lic' => $lic,
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
    public function actionMap($waybill_id)
    {
        $records = iikoWaybillData::find()
            ->select('iiko_waybill_data.*, iiko_product.denom as pdenom')
            ->leftJoin('iiko_product', 'iiko_product.id = product_rid')
            ->where(['waybill_id' => $waybill_id]);

        $model = iikoWaybill::findOne($waybill_id);
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

        $dataProvider = new ActiveDataProvider([
            'query' => $records,
            'sort' => [
                'defaultOrder' => [
                    'munit' => SORT_ASC
                ]
            ],
        ]);

        $lic = iikoService::getLicense();
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
        if (!is_null($term)) {
            $query = new \yii\db\Query;
            $query->select(['id' => 'id', 'text' => 'CONCAT(`denom`," (",unit,")")'])
                ->from('iiko_product')
                ->where('org_id = :acc', [':acc' => User::findOne(Yii::$app->user->id)->organization_id])
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
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }
            return $this->redirect(['index']);
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->getErrors()) {
                var_dump($model->getErrors());
                exit;
            }
            return $this->redirect(['index']);
        } else {
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

    /**
     * @param $id
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
}
