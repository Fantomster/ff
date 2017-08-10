<?php

namespace franchise\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use common\models\User;
use common\models\Profile;
use common\models\Role;
use common\models\Organization;
use common\models\BuisinessInfo;
use common\models\FranchiseeAssociate;

/**
 * Description of OrganizationController
 *
 * @author sharaf
 */
class OrganizationController extends DefaultController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only' => ['index', 'clients', 'delete', 'vendors', 'ajax-show-client', 'ajax-show-vendor', 'create-client', 'create-vendor', 'agent'],
                'rules' => [
                    [
                        'actions' => ['index', 'clients', 'delete', 'vendors', 'ajax-show-client', 'ajax-show-vendor', 'create-client', 'create-vendor'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                    [
                        'actions' => ['agent'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_FRANCHISEE_AGENT,
                        ],
                    ],
                ],
            /* 'denyCallback' => function($rule, $action) {
              throw new HttpException(404 ,'Нет здесь ничего такого, проходите, гражданин');
              } */
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Displays clients list
     *
     * @return mixed
     */
    public function actionClients() {
        $searchModel = new \franchise\models\ClientSearch();
        $params = Yii::$app->request->getQueryParams();

        $today = new \DateTime();

        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = Yii::$app->formatter->asTime($this->currentFranchisee->getFirstOrganizationDate(), "php:d.m.Y");

        if (Yii::$app->request->post("ClientSearch")) {
            $params['ClientSearch'] = Yii::$app->request->post("ClientSearch");
        }
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('clients', compact('dataProvider', 'searchModel'));
        } else {
            return $this->render('clients', compact('dataProvider', 'searchModel'));
        }
    }

    public function actionAjaxShowClient($id) {
        $client = Organization::find()
                ->joinWith("franchiseeAssociate")
                ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id, 'organization.type_id' => Organization::TYPE_RESTAURANT])
                ->one();
        if (empty($client->buisinessInfo)) {
            $buisinessInfo = new BuisinessInfo();
            $buisinessInfo->setOrganization($client);
            $client->refresh();
        }
        return $this->renderAjax("_ajax-show-client", compact('client'));
    }

    /**
     * Add new restaurant
     */
    public function actionCreateClient() {
        $client = new Organization();
        $client->type_id = Organization::TYPE_RESTAURANT;
        $user = new User();
        $user->scenario = 'admin';
        $user->password = uniqid();
        $user->setRegisterAttributes(Role::ROLE_RESTAURANT_MANAGER, User::STATUS_ACTIVE);
        $profile = new Profile();
        $buisinessInfo = new BuisinessInfo();

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            if ($user->load($post) && $profile->load($post) && $client->load($post) && $buisinessInfo->load($post)) {

                if ($user->validate() && $profile->validate() && $client->validate() && $buisinessInfo->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        //$user->setRegisterAttributes(Role::ROLE_RESTAURANT_MANAGER, User::STATUS_ACTIVE)->save();
                        $user->save();
                        $profile->setUser($user->id)->save();
                        $client->save();
                        $user->setOrganization($client);
                        $this->addOrganization($client);
                        $buisinessInfo->setOrganization($client);
                        $transaction->commit();
                        return $this->redirect(['organization/clients']);
                    } catch (Exception $e) {
                        $transaction->rollback();
                    }
                }
            }
        }

        return $this->render('create-client', compact('client', 'user', 'profile', 'buisinessInfo'));
    }

    /**
     * Update restaurant
     */
    public function actionUpdateClient($id) {
        $client = Organization::find()
                ->joinWith("franchiseeAssociate")
                ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id, 'organization.type_id' => Organization::TYPE_RESTAURANT])
                ->one();
        if (empty($client)) {
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }
        if (empty($client->buisinessInfo)) {
            $buisinessInfo = new BuisinessInfo();
            $buisinessInfo->setOrganization($client);
        } else {
            $buisinessInfo = $client->buisinessInfo;
        }

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            if ($client->load($post) && $buisinessInfo->load($post)) {

                if ($client->validate() && $buisinessInfo->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $client->save();
                        $buisinessInfo->save();
                        $transaction->commit();
                        return $this->redirect(['organization/clients']);
                    } catch (Exception $e) {
                        $transaction->rollback();
                    }
                }
            }
        }

        return $this->render('update-client', compact('client', 'buisinessInfo'));
    }

    /**
     * Displays vendors list
     *
     * @return mixed
     */
    public function actionVendors() {
        $searchModel = new \franchise\models\VendorSearch();
        $params = Yii::$app->request->getQueryParams();

        $today = new \DateTime();

        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = Yii::$app->formatter->asTime($this->currentFranchisee->getFirstOrganizationDate(), "php:d.m.Y");

        if (Yii::$app->request->post("VendorSearch")) {
            $params['VendorSearch'] = Yii::$app->request->post("VendorSearch");
        }
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('vendors', compact('dataProvider', 'searchModel'));
        } else {
            return $this->render('vendors', compact('dataProvider', 'searchModel'));
        }
    }

    /**
     * Displays vendors list
     *
     * @return mixed
     */
    public function actionAgent() {

        return $this->render('agent', compact('dataProvider'));
    }

    public function actionAjaxShowVendor($id) {
        $vendor = Organization::find()
                ->joinWith("franchiseeAssociate")
                ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id, 'organization.type_id' => Organization::TYPE_SUPPLIER])
                ->one();
        if (empty($vendor->buisinessInfo)) {
            $buisinessInfo = new BuisinessInfo();
            $buisinessInfo->setOrganization($vendor);
            $vendor->refresh();
        }
        $catalog = \common\models\Catalog::find()->where(['supp_org_id' => $vendor->id, 'type' => \common\models\Catalog::BASE_CATALOG])->one();
        return $this->renderAjax("_ajax-show-vendor", compact('vendor', 'catalog'));
    }

    /**
     * Add new supplier
     */
    public function actionCreateVendor() {
        $vendor = new Organization();
        $catalog = new \common\models\Catalog();
        $vendor->type_id = Organization::TYPE_SUPPLIER;
        $user = new User();
        $user->scenario = 'admin';
        $user->password = uniqid();
        $user->setRegisterAttributes(Role::ROLE_SUPPLIER_MANAGER, User::STATUS_ACTIVE);
        $profile = new Profile();
        $buisinessInfo = new BuisinessInfo();

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            if ($user->load($post) && $profile->load($post) && $vendor->load($post) && $buisinessInfo->load($post)) {

                if ($user->validate() && $profile->validate() && $vendor->validate() && $buisinessInfo->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $user->save();
                        $profile->setUser($user->id)->save();
                        $vendor->save();
                        $catalog->name = \common\models\Catalog::CATALOG_BASE_NAME;
                        $catalog->type = \common\models\Catalog::BASE_CATALOG;
                        $catalog->supp_org_id = $vendor->id;
                        $catalog->save();
                        $user->setOrganization($vendor);
                        $this->addOrganization($vendor);
                        $buisinessInfo->setOrganization($vendor);
                        $transaction->commit();
                        return $this->redirect(['organization/vendors']);
                    } catch (Exception $e) {
                        $transaction->rollback();
                    }
                }
            }
        }

        return $this->render('create-vendor', compact('vendor', 'user', 'profile', 'buisinessInfo'));
    }

    /**
     * Update vendor
     */
    public function actionUpdateVendor($id) {
        $vendor = Organization::find()
                ->joinWith("franchiseeAssociate")
                ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id, 'organization.type_id' => Organization::TYPE_SUPPLIER])
                ->one();
        if (empty($vendor)) {
            throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
        }
        if (empty($vendor->buisinessInfo)) {
            $buisinessInfo = new BuisinessInfo();
            $buisinessInfo->setOrganization($vendor);
        } else {
            $buisinessInfo = $vendor->buisinessInfo;
        }

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            if ($vendor->load($post) && $buisinessInfo->load($post)) {

                if ($vendor->validate() && $buisinessInfo->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $vendor->save();
                        $buisinessInfo->save();
                        $transaction->commit();
                        return $this->redirect(['organization/vendors']);
                    } catch (Exception $e) {
                        $transaction->rollback();
                    }
                }
            }
        }

        return $this->render('update-vendor', compact('vendor', 'buisinessInfo'));
    }

    /**
     * Adds organization to franchisee
     *
     * @return bool
     */
    private function addOrganization($organization) {
        $associate = new FranchiseeAssociate();
        $associate->organization_id = $organization->id;
        $associate->franchisee_id = $this->currentFranchisee->id;
        return $associate->save();
    }


    /**
     * Updates franchisee_id to 1 for vendor or deletes association if exists
     *
     * @return bool
     */
    public function actionDelete($id) {
        $associate = FranchiseeAssociate::findOne($id);
        if(!empty($associate)){
            $organizationsCount = FranchiseeAssociate::find()->where(['organization_id'=>$associate->organization_id, 'franchisee_id'=>1])->count();
            if($organizationsCount){
                $associate->delete();
            }else{
                $associate->franchisee_id = 1;
                $associate->save();
            }
            if(Yii::$app->request->isPjax){
                return 'success';
            }else{
                return $this->actionVendors();
            }
        }
        return $this->actionVendors();
    }

}
