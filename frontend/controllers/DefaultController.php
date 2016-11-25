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
        //parent::beforeAction($action);
        if (!Yii::$app->user->isGuest) {
            $this->loadCurrentUser();
            $organization = $this->currentUser->organization;
            switch ($organization->type_id) {
                case Organization::TYPE_RESTAURANT:
                    $this->layout = 'main-client';
//                    //проверка, имеет ли кабак поставщиков, если нет, то направляем на страницу добавления поставщиков
//                    $suppliers = RelationSuppRest::findOne(['rest_org_id' => $organization->id]);
//                    $isIndex = ($this->id === 'client') && ($this->action->id === 'index');
//                    if (!isset($suppliers) && $isIndex) {
//                        return $this->redirect(['client/suppliers']);
//                    }
                    $isSettings = ($this->id === 'client') && ($this->action->id === 'settings');
                    $isTutorial = ($this->id === 'client') && ($this->action->id === 'tutorial');
                    $isSuppliers = ($this->id === 'client') && (($this->action->id === 'suppliers') || ($this->action->id === 'create') || ($this->action->id === 'invite') || ($this->action->id === 'chkmail'));
                    if (($organization->step == Organization::STEP_SET_INFO) && !$isSettings && !$isTutorial) {
                        return $this->redirect(['client/settings']);
                    }
                    if (($organization->step == Organization::STEP_ADD_VENDOR) && !$isSuppliers && !$isTutorial) {
                        return $this->redirect(['client/suppliers']);
                    }
                    
                    break;
                case Organization::TYPE_SUPPLIER:
                    $this->layout = 'main-vendor';
//                    //проверка, имеет ли крестьянин базовый каталог, если нет, то направляем создавать
//                    $baseCatalogs = Catalog::findOne(['supp_org_id' => $organization->id]);
//                    $isIndex = ($this->id === 'vendor') && ($this->action->id === 'index');
//                    if (!isset($baseCatalogs) && $isIndex) {
//                        return $this->redirect(['vendor/catalogs']);
//                    }
                    $isSettings = ($this->id === 'vendor') && ($this->action->id === 'settings');
                    $isTutorial = ($this->id === 'vendor') && ($this->action->id === 'tutorial');
                    $isCatalogs = ($this->id === 'vendor') && (($this->action->id === 'catalogs') || ($this->action->id === 'supplier-start-catalog-create') || ($this->action->id === 'import-base-catalog-from-xls'));
                    if (($organization->step == Organization::STEP_SET_INFO) && !$isSettings && !$isTutorial) {
                        return $this->redirect(['vendor/settings']);
                    }
                    if (($organization->step == Organization::STEP_ADD_CATALOG) && !$isCatalogs && !$isTutorial) {
                        return $this->redirect(['vendor/catalogs']);
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
