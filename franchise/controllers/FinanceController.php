<?php

namespace franchise\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use common\models\Role;

/**
 * Description of FinanceController
 *
 * @author sharaf
 */
class FinanceController extends DefaultController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            /* 'denyCallback' => function($rule, $action) {
              throw new HttpException(404 ,'Нет здесь ничего такого, проходите, гражданин');
              } */
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
    
    /**
     * Displays finance index
     * 
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new \franchise\models\VendorFinanceSearch();
        $params = Yii::$app->request->getQueryParams();

        if (Yii::$app->request->post("VendorFinanceSearch")) {
            $params['VendorFinanceSearch'] = Yii::$app->request->post("VendorFinanceSearch");
        }
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);
        
        $vendorsStats = $this->currentFranchisee->getMyVendorsStats();
        $franchiseeType = $this->currentFranchisee->type;

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', compact('dataProvider', 'searchModel', 'vendorsStats', 'franchiseeType'));
        } else {
            return $this->render('index', compact('dataProvider', 'searchModel', 'vendorsStats', 'franchiseeType'));
        }
    }
}
