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
        $frUser = \common\models\FranchiseeUser::findOne(['user_id' => $this->currentUser->id]);
        $this->currentFranchisee = empty($frUser) ? null : $frUser->franchisee;
    }
    
    public function beforeAction($action) {
        if (!Yii::$app->user->isGuest) {
            $this->loadCurrentUser();
            if ($this->currentUser->role_id === \common\models\Role::ROLE_FRANCHISEE_AGENT) {
                return $this->redirect(['agent-request/index']);
            }
        }
        if (!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }
}
