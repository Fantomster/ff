<?php

namespace franchise\controllers;

use common\models\Franchisee;
use Yii;
use yii\web\Controller;

/**
 * Description of DefaultController
 *
 */
class DefaultController extends Controller {

    protected $currentUser;

    /**
     * Description
     *
     * @var Franchisee
     */
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
            if ($this->currentUser->role_id === \common\models\Role::ROLE_FRANCHISEE_AGENT && (Yii::$app->controller->id != 'agent-request') && (Yii::$app->controller->id != 'organization')) {
                return $this->redirect(['agent-request/index']);
            }
            if ($this->currentUser->role_id === \common\models\Role::ROLE_FRANCHISEE_ACCOUNTANT && (Yii::$app->controller->id != 'finance')) {
                return $this->redirect(['finance/index']);
            }
        }
        if (!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }
}
