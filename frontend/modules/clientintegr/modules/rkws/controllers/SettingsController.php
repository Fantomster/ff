<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use api\common\models\RkCategory;
use api\common\models\RkPconst;
use Yii;
use yii\web\Controller;
use api\common\models\RkWaybill;
use api\common\models\RkAgentSearch;
use frontend\modules\clientintegr\modules\rkws\components\ApiHelper;
use api\common\models\RkWaybilldata;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use common\models\Organization;


class SettingsController extends \frontend\modules\clientintegr\controllers\DefaultController {

    public function actionIndex() {

        $searchModel = new \api\common\models\RkDicconstSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $organization = Yii::$app->user->identity->orgazanition;
        $lic0 = $organization->getLicenseList();
        //$lic = $this->checkLic();
        $lic = $lic0['rkws'];
        $licucs = $lic0['rkws_ucs'];
        $vi = (($lic) && ($licucs)) ? 'index' : '/default/_nolic';

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial($vi,[
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'lic' => $lic,
                'licucs' => $licucs,
            ]);
        } else {
            return $this->render($vi,[
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'lic' => $lic,
                'licucs' => $licucs,
            ]);
        }
    }


    public function actionChangeconst($id)
    {
        $org = Yii::$app->user->identity->organization_id;

        $pConst = \api\common\models\RkPconst::findOne(['const_id' => $id, 'org' => $org]);

        if (!$pConst) {

            $pConst = new RkPconst();
            $pConst->org = $org;
            $pConst->const_id = $id;

            if (!$pConst->save()) {
                echo "Can't create P Const model (";
                die();
            }

        }

        $lic = $this->checkLic();
        $vi = $lic ? 'update' : '/default/_nolic';

        if ($pConst->load(Yii::$app->request->post()) && $pConst->save()) {
            //  return $this->redirect(['view', 'id' => $model->id]);

            if ($pConst->getErrors()) {
                var_dump($pConst->getErrors());
                exit;
            }

           if (Yii::$app->request->post('isTree')) // Update tree

            Yii::$app->db_api->createCommand()
                   ->update('rk_category', ['selected' => 0], ['acc' => $pConst->org])
                   ->execute();

            Yii::$app->db_api->createCommand()
                ->update('rk_category', ['selected' => 1], 'id in ('.$pConst->value.')')
                ->execute();

            return $this->redirect(['index']);
        } else {
            return $this->render($vi, [
                'model' => $pConst,
            ]);
        }

    }


    protected function checkLic() {

        $lic = \api\common\models\RkServicedata::find()->andWhere('org = :org',['org' => Yii::$app->user->identity->organization_id])->one();
    $t = strtotime(date('Y-m-d H:i:s',time()));
    
    if ($lic) {
       /*if ($t >= strtotime($lic->fd) && $t<= strtotime($lic->td) && $lic->status_id === 2 ) {*/
       $res = $lic; 
    /*} else {
       $res = 0; 
    }*/
    } else 
       $res = 0; 
    
    
    return $res ? $res : null;
        
    }


}
