<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercService;
use common\models\User;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercLogger;
use yii\web\Controller;
use common\models\Journal;
use common\models\search\JournalSearch;
use yii\data\Sort;
use yii\web\NotFoundHttpException;
use common\components\AccessRule;
use yii\filters\AccessControl;
use common\models\Role;
use Yii;

class JournalController extends Controller
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

    public function actionIndex()
    {
        $user = User::findOne(\Yii::$app->user->getId());
        $searchModel = new JournalSearch();
        $searchModel->service_id = mercLogger::service_id;
        $searchModel->organizations = array_keys(\yii\helpers\ArrayHelper::map($user->getAllOrganization(null), 'id', 'name'));
        $searchModel->organization_id = (\Yii::$app->user->identity)->organization_id;
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $lic = mercService::getLicense(Yii::$app->user->identity->organization_id);

        $sort = new Sort();
        $sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->setSort($sort);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'user'         => $user,
            'lic'          => $lic,
        ]);
    }

    /**
     * Displays a single Journal model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Journal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Journal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Journal::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}