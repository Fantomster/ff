<?php

namespace frontend\modules\clientintegr\modules\odinsobsh\controllers;

use api\common\models\one_s\OneSDicconst;
use api\common\models\one_s\OneSPconst;
use api\common\models\one_s\OneSService;
use api\common\models\one_s\search\OneSDicconstSearch;
use common\models\Organization;
use Yii;
use common\models\User;

class SettingsController extends \frontend\modules\clientintegr\controllers\DefaultController
{

    public $enableCsrfValidation = false;
    protected $authenticated = false;
    public $organisation_id;

    public function beforeAction($action)
    {
        $this->organisation_id = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id);

        if (empty($this->organisation_id)) {
            return false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OneSDicconstSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $lic = OneSService::getLicense(Yii::$app->user->identity->organization_id);
        $vi = $lic ? 'index' : '/default/_nolic';
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
                'lic'          => $lic,
            ]);
        } else {
            return $this->render($vi, [
                'searchModel'  => $searchModel,
                'dataProvider' => $dataProvider,
                'lic'          => $lic,
            ]);
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionChangeConst($id)
    {
        $this->organisation_id = User::findOne(Yii::$app->user->id)->organization_id;
        $pConst = OneSPconst::findOne(['const_id' => $id, 'org' => $this->organisation_id]);

        if (empty($pConst)) {
            $pConst = new  OneSPconst();
            $pConst->org = $this->organisation_id;
            $pConst->const_id = $id;
            if (!$pConst->save()) {
                echo "Can't create P Const model (";
                die();
            }
        }

        $lic = OneSService::getLicense(Yii::$app->user->identity->organization_id);
        $vi = $lic ? 'update' : '/default/_nolic';

        if ($pConst->load(Yii::$app->request->post()) && $pConst->save()) {
            if ($pConst->getErrors()) {
                var_dump($pConst->getErrors());
                exit;
            }
            return $this->redirect(['index']);
        } else {
            $dicConst = OneSDicconst::findOne(['id' => $pConst->const_id]);
            return $this->render($vi, [
                'model'    => $pConst,
                'dicConst' => $dicConst,
            ]);
        }

    }
}
