<?php

namespace franchise\controllers;

use Yii;
use yii\web\Controller;

/**
 * Description of DefaultController
 *
 */
class DefaultController extends Controller {

    protected $currentUser;
    protected $currentFranchisee;

    /*
     *  Load current user
     */

    protected function loadCurrentUser() {
        $this->currentUser = Yii::$app->user->identity;
        $this->currentFranchisee = \common\models\FranchiseeUser::findOne(['user_id' => $this->currentUser->id])->franchisee;
    }
    
    public function beforeAction($action) {
        if (!Yii::$app->user->isGuest) {
            $this->loadCurrentUser();
        }
        if (!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }
}
