<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Organization;
use common\models\Role;
use common\models\User;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/**
 * Description of DefaultController
 *
 */
class DefaultController extends Controller {

    /**
     *
     * @var \common\models\User
     */
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
        if (Yii::$app->request->get("token")) {
            $token = Yii::$app->request->get("token");
            $order_id = Yii::$app->request->get("id");
            $order = \common\models\Order::findOne(['id' => $order_id]);
            
            $jwtToken = Yii::$app->jwt->getParser()->parse((string) $token);
            $data = Yii::$app->jwt->getValidationData(); // It will use the current time to validate (iat, nbf and exp)
            $data->setIssuer('mixcart.ru');
            $signer = new Sha256();
            
//            $user = \common\models\User::findOne(['access_token' => $token]);
//            $organization = (isset($order) && isset($user)) ? $order->getOrganizationByUser($user) : null;
//            if ($user && isset($order) && isset($organization)) {
            if ($jwtToken->validate($data) && $jwtToken->verify($signer, 'ololo') && isset($order)) {
                $user = \common\models\User::findOne(['access_token' => $jwtToken->getClaim('access_token')]);
                $organization = (isset($order) && isset($user)) ? $order->getOrganizationByUser($user) : null;
                Yii::$app->user->logout();
                Yii::$app->user->login($user, 0);
                (new \api_web\classes\UserWebApi())->setOrganization(['organization_id' => $organization->id ?? null]);
                Yii::$app->user->identity->refresh();
                $this->loadCurrentUser();
                $this->setLayout($organization->type_id);
            }
        } elseif (!Yii::$app->user->isGuest) {
            $this->loadCurrentUser();
            $this->currentUser->update();
            $organization = $this->currentUser->organization;
            if(!$organization){
                throw new \yii\web\HttpException(403, Yii::t('error', 'frontend.controllers.def.access_denied', ['ru'=>'Доступ запрещен']));
            }
            if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                $this->view->params['orders'] = $organization->getCart();
            }
            $this->setLayout($organization->type_id);
            $isAdmin = in_array($this->currentUser->role_id, [Role::ROLE_ADMIN, Role::ROLE_FKEEPER_MANAGER]);
//            if (($organization->step == Organization::STEP_SET_INFO) && ($this->currentUser->status == \common\models\User::STATUS_ACTIVE) && !$isAdmin) {
//                $this->redirectIfNotHome($organization);
//            }
            if (($this->currentUser->status === \common\models\User::STATUS_UNCONFIRMED_EMAIL) && (Yii::$app->controller->id != 'order')) {
                throw new \yii\web\HttpException(403, Yii::t('error', 'frontend.controllers.def.access_denied_two', ['ru'=>'Доступ запрещен']));
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
