<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercPconst;
use api\common\models\merc\mercService;
use api\common\models\merc\search\mercDicconstSearch;
use frontend\modules\clientintegr\modules\merc\helpers\mercApi;
use Yii;

class SettingsController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new mercDicconstSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $lic = mercService::getLicense();
        $vi = $lic ? 'index' : '/default/_nolic';
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi, [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'lic' => $lic,
            ]);
        } else {
            return $this->render($vi, [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'lic' => $lic,
            ]);
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionChangeConst($id)
    {
        $org = Yii::$app->user->identity->organization_id;
        $pConst = mercPconst::findOne(['const_id' => $id, 'org' => $org]);

        if (empty($pConst)) {
            $pConst = new mercPconst();
            $pConst->org = $org;
            $pConst->const_id = $id;
            if (!$pConst->save()) {
                echo "Can't create P Const model (";
                die();
            }
        }

        $lic = mercService::getLicense();
        if(Yii::$app->request->isAjax)
            $vi = $lic ? '_ajaxForm' : '/default/_nolic';
        else
            $vi = $lic ? 'update' : '/default/_nolic';

        if ($pConst->load(Yii::$app->request->post()) && $pConst->save()) {
            if ($pConst->getErrors()) {
                var_dump($pConst->getErrors());
                exit;
            }
            if(Yii::$app->request->isAjax)
                return true;
            return $this->redirect(['index']);
        } else {
            $dicConst = mercDicconst::findOne(['id' => $pConst->const_id]);

            $org = [];
            if($dicConst->denom == 'enterprise_guid')
            {
                $list = mercApi::getInstance()->getActivityLocationList();

                foreach ($list->soapenvBody->v2getActivityLocationListResponse->dtactivityLocationList->dtlocation as $item)
                {
                    $org[$item->dtenterprise->bsguid->__toString()] = $item->dtenterprise->dtname->__toString(). ' ('.$item->dtenterprise->dtaddress->dtaddressView->__toString().')';
                }

            }
            if(Yii::$app->request->isAjax)
                return $this->renderAjax($vi, [
                    'model' => $pConst,
                    'dicConst' => $dicConst,
                    'org_list' => $org,
                ]);

            return $this->render($vi, [
                'model' => $pConst,
                'dicConst' => $dicConst,
                'org_list' => $org,
            ]);
        }

    }
}
