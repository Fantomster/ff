<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace api\common\controllers;

use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use api\common\models\User;
use yii\web\Controller;

/**
 * Custom user controller
 * 
 * @inheritdoc
 */
class UserController extends \amnah\yii2\user\controllers\DefaultController {
//   class UserController extends Controller {

       
    /**
     * @inheritdoc
     */
  /*     
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'actions' => ['confirm', 'resend', 'logout'],
                'allow' => true,
                'roles' => ['?', '@'],
            ],
            [
                'actions' => ['login', 'register', 'forgot', 'reset', 'login-email', 'login-callback', 'accept-restaurants-invite', 'ajax-register'],
                'allow' => true,
                'roles' => ['?'],
            ],
            [
                'actions' => ['index', 'profile', 'account', 'cancel', 'resend-change'],
                'allow' => true,
            ],
            [
                'actions' => ['ajax-invite-friend'],
                'allow' => true,
                'roles' => ['@'],
            ],
            [
                'actions' => ['test'],
                'allow' => true,
                'roles' => ['?'],
            ]
        ];

        $behaviors['access']['denyCallback'] = function($rule, $action) {
            $this->redirect(['/site/index']);
        };
        return $behaviors;
    }    
       
    
    
    public function actionTest() {
        
        echo "test";
}

    public function actionIndex() {
        
        echo "test";
}
*/
}

