<?php

namespace franchise\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use common\models\Role;

/**
 * Description of AppController
 *
 * @author sharaf
 */
class AppController extends DefaultController {

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
                'only' => ['index', 'settings', 'promotion', 'users'],
                'rules' => [
                    [
                        'actions' => ['index', 'settings', 'promotion', 'users'],
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
     * Displays desktop.
     *
     * @return mixed
     */
    public function actionIndex() {
        return $this->render('/site/under-construction');
    }
    
    /**
     * Displays general settings
     * 
     * @return mixed
     */
    public function actionSettings() {
        return $this->render('/site/under-construction');
    }
    
    /**
     * Displays franchise users list
     * 
     * @return mixed
     */
    public function actionUsers() {
        return $this->render('/site/under-construction');
    }
    
    /**
     * Displays promotion
     * 
     * @return mixed
     */
    public function actionPromotion() {
        return $this->render('/site/under-construction');
    }
}
