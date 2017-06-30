<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Organization;
use common\models\RelationSuppRest;
use common\models\Catalog;

/**
 * Description of DefaultController
 *
 */
class DefaultController extends Controller {

    protected $currentUser;

    /*
     *  Load current user
     */

    protected function loadCurrentUser() {
        $this->currentUser = Yii::$app->user->identity;
    }
    
    protected function setLayout($orgType) {
        switch ($orgType) {
                case Organization::TYPE_RESTAURANT:
                    $this->layout = 'main-client';
                    break;
                case Organization::TYPE_SUPPLIER:
                    $this->layout = 'main-vendor';
                    break;
            }
    }

    public function beforeAction($action) {
        if (!Yii::$app->user->isGuest) {
            $this->loadCurrentUser();
            $organization = $this->currentUser->organization;
            if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                $this->view->params['orders'] = $orders = $organization->getCart();
            }
            $this->setLayout($organization->type_id);
            if (($organization->step == Organization::STEP_SET_INFO) && ($this->currentUser->status == \common\models\User::STATUS_ACTIVE)) {
                $this->redirectIfNotHome($organization);
            }
            if (($this->currentUser->status === \common\models\User::STATUS_UNCONFIRMED_EMAIL) && (Yii::$app->controller->id != 'order')) {
                throw new \yii\web\HttpException(403, 'Хуй тебе, Челиос!');
            }
        } elseif (Yii::$app->request->get("token")) {
            $token = Yii::$app->request->get("token");
            $user = \common\models\User::findOne(['access_token' => $token]);
            if ($user) {
                Yii::$app->user->login($user, 0);
                $this->loadCurrentUser();
                $organization = $this->currentUser->organization;
                $this->setLayout($organization->type_id);
            }
        }
        if (!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }
    
    private function redirectIfNotHome($organization) { 
        $organizationHome = ($organization->type_id == Organization::TYPE_RESTAURANT) ? 'client' : 'vendor';
        if (Yii::$app->controller->id != $organizationHome || Yii::$app->controller->action->id != 'index') {
            return $this->redirect(['/'.$organizationHome.'/index']);
        }
    }
}
