<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Organization;

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
        //parent::beforeAction($action);
        $this->loadCurrentUser();
        switch ($this->currentUser->organization->type_id) {
            case Organization::TYPE_RESTAURANT: 
                $this->layout = 'main-client';
                break;
            case Organization::TYPE_SUPPLIER:
                $this->layout = 'main-vendor';
                break;
        }
        return parent::beforeAction($action);
   }
}
