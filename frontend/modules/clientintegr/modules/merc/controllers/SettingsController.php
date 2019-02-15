<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercPconst;
use api\common\models\merc\mercService;
use api\common\models\merc\search\mercDicconstSearch;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\models\ActivityLocationList;
use Yii;
use common\components\AccessRule;
use yii\filters\AccessControl;
use common\models\Role;

class SettingsController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'allow' => false,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_BUYER,
                            Role::ROLE_RESTAURANT_JUNIOR_BUYER,
                            Role::ROLE_RESTAURANT_ORDER_INITIATOR,
                        ],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new mercDicconstSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $lic = mercService::getLicense(Yii::$app->user->identity->organization_id);
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

        $lic = mercService::getLicense(Yii::$app->user->identity->organization_id);
        if (Yii::$app->request->isAjax)
            $vi = $lic ? '_ajaxForm' : '/default/_nolic';
        else
            $vi = $lic ? 'update' : '/default/_nolic';

        if ($pConst->load(Yii::$app->request->post()) && $pConst->save()) {
            if ($pConst->getErrors()) {
                var_dump($pConst->getErrors());
                exit;
            }
            if (Yii::$app->request->isAjax)
                return true;
            return $this->redirect(['index']);
        } else {
            $dicConst = mercDicconst::findOne(['id' => $pConst->const_id]);

            $org = [];
            if ($dicConst->denom == 'enterprise_guid') {
                $list = cerberApi::getInstance()->getActivityLocationList();
                foreach ($list as $item) {
                    $org[] = [
                        'value' => $item->guid,
                        'label' => $item->name .
                            ' (' . $item->addressView . ')'];
                }
                if (count($org) == 0)
                    Yii::$app->session->setFlash('error', 'Не удалось выгрузить связанные с данным ХС предприятия, проверьте наличие связей предприятия с ХС или добавьте GUD предприятия вручную');
            }
            if (Yii::$app->request->isAjax)
                return $this->renderAjax($vi, [
                    'model'    => $pConst,
                    'dicConst' => $dicConst,
                    'org_list' => $org,
                ]);

            return $this->render($vi, [
                'model'    => $pConst,
                'dicConst' => $dicConst,
                'org_list' => $org,
            ]);
        }

    }
}
