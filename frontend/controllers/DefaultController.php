<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Organization;
use common\models\RelationSuppRest;
use common\models\CatalogBaseGoods;

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
        $organization = $this->currentUser->organization;
        switch ($organization->type_id) {
            case Organization::TYPE_RESTAURANT: 
                $this->layout = 'main-client';
                //проверка, имеет ли кабак поставщиков, если нет, то направляем на страницу добавления поставщиков
                $suppliers = RelationSuppRest::findOne(['rest_org_id' => $organization->id]);
                $isTargetPage = ($this->id == 'client') && ($this->action->id == 'suppliers');
                if (!isset($suppliers) && !$isTargetPage) {
                    return $this->redirect(['client/suppliers']);
                }
                break;
            case Organization::TYPE_SUPPLIER:
                $this->layout = 'main-vendor';
                //проверка, имеет ли крестьянин базовый каталог, если нет, то направляем создавать
                $baseCatalogs = CatalogBaseGoods::findOne(['supp_org_id' => $organization->id]);
                $isTargetPage = ($this->id == 'vendor') && ($this->action->id == 'catalogs');
                if (!isset($suppliers) && !$isTargetPage) {
                    return $this->redirect(['vendor/catalogs']);
                }
                break;
        }
        if (!parent::beforeAction($action)) {
            return false;
        }
//        $test = !(($this->action->id == 'index') && ($this->id == 'order'));
//        if (($this->currentUser->id == 2) && !(($this->action->id == 'index') && ($this->id == 'order'))) {
//            return $this->redirect(['order/index']);
//        }
        return true;
   }
}
