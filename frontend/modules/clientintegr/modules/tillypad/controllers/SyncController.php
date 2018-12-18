<?php

namespace frontend\modules\clientintegr\modules\tillypad\controllers;

use api_web\modules\integration\modules\tillypad\models\TillypadSync;
use common\models\search\TillypadAgentSearch;
use Yii;
use common\models\User;
use common\models\RelationSuppRest;
use api\common\models\iiko\iikoAgent;
use common\models\Organization;

class SyncController extends \frontend\modules\clientintegr\modules\iiko\controllers\SyncController
{
    /**
     * Синхронизация всего, по типам
     *
     * @return array
     */
    public function actionRun()
    {
        $id = \Yii::$app->request->post('id');
        try {
            return (new TillypadSync())->run($id);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'line' => $e->getLine(), 'file' => $e->getFile(), 'trace' => $e->getTraceAsString()];
        }
    }

    /**
     * @return string
     */
    public function actionAgentView()
    {
        $searchModel = new TillypadAgentSearch;
        $params = Yii::$app->request->getQueryParams();
        $organization = User::findOne(Yii::$app->user->id)->organization_id;
        $searchModel->load(Yii::$app->request->get());
        $dataProvider = $searchModel->search($params, $organization);
        return $this->render('agent-view', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * Формирование списка поставщиков по введённым символам
     *
     * @return array
     */
    public function actionAgentAutocomplete()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $term = Yii::$app->request->post('stroka');
        $user = User::findOne(\Yii::$app->user->id);
        $organisation_id = $user->organization_id;
        $out['results'] = [];

        if (!is_null($term)) {
            $vendors = RelationSuppRest::find()->select('supp_org_id')->where(['rest_org_id' => $organisation_id, 'deleted' => 0])->column();
            $data = Organization::find()->select('id,name')->
            where(['type_id' => 2])->
            andWhere(['in', 'id', $vendors])->
            andWhere(['like', 'name', ':term', [':term' => $term]])->
            orderBy(['name' => SORT_ASC])->all();
        } else {
            $vendors = RelationSuppRest::find()->select('supp_org_id')->where(['rest_org_id' => $organisation_id, 'deleted' => 0])->column();
            $data = Organization::find()->select('id,name')->
            where(['type_id' => 2])->
            andWhere(['in', 'id', $vendors])->
            orderBy(['name' => SORT_ASC])->all();
        }
        $out['results'] = array_values($data);

        return $out;
    }

    /**
     * Редактирование идентификатора поставщика у агента
     *
     * @return boolean
     */
    public function actionEditVendor()
    {
        $vendor_id = Yii::$app->request->post('id');
        $id = Yii::$app->request->post('number');
        $agent = iikoAgent::findOne($id);
        $agent->vendor_id = $vendor_id;
        return $agent->save();
    }

    /**
     * Редактирование комментария у агента
     *
     * @return boolean
     */
    public function actionEditComment()
    {
        $comment = Yii::$app->request->post('comm');
        $id = Yii::$app->request->post('number');
        $agent = iikoAgent::findOne($id);
        $agent->comment = $comment;
        return $agent->save();
    }

    /**
     * Редактирование статуса активности у агента
     *
     * @return boolean
     */
    public function actionEditActive()
    {
        $activ = Yii::$app->request->post('activ');
        $id = Yii::$app->request->post('number');
        $agent = iikoAgent::findOne($id);
        $agent->is_active = $activ;
        return $agent->save();
    }
}
