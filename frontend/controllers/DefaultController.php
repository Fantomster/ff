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

    public function beforeAction($action) {
        if (!Yii::$app->user->isGuest) {
            $this->loadCurrentUser();
            $organization = $this->currentUser->organization;
            switch ($organization->type_id) {
                case Organization::TYPE_RESTAURANT:
                    $this->layout = 'main-client';
                    if ($organization->step == Organization::STEP_SET_INFO) {
                        return $this->redirect(['/site/complete-registration']);
                    }
                    break;
                case Organization::TYPE_SUPPLIER:
                    $this->layout = 'main-vendor';
                    if ($organization->step != Organization::STEP_OK) {
                        return $this->redirect(Yii::$app->params['demoUrl']);
                    }
                    break;
            }
        }
        if (!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }

}
