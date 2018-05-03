<?php

namespace franchise\controllers;

use common\models\Currency;
use common\models\ManagerAssociate;
use common\models\Order;
use common\models\RelationSuppRest;
use common\models\UserToken;
use Yii;
use yii\helpers\VarDumper;
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
use yii\web\HttpException;

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
                'only' => ['index', 'clients', 'delete', 'vendors', 'ajax-show-client', 'ajax-show-vendor', 'create-client', 'create-vendor', 'agent', 'ajax-update-currency'],
                'rules' => [
                    [
                        'actions' => ['index', 'clients', 'delete', 'vendors', 'ajax-show-client', 'ajax-show-vendor', 'create-client', 'create-vendor', 'update-users-organization', 'ajax-update-currency'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_FRANCHISEE_OWNER,
                            Role::ROLE_FRANCHISEE_OPERATOR,
                            Role::ROLE_FRANCHISEE_ACCOUNTANT,
                            Role::ROLE_FRANCHISEE_LEADER,
                            Role::ROLE_FRANCHISEE_MANAGER,
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
              throw new HttpException(404 ,Yii::t('app', 'Нет здесь ничего такого, проходите, гражданин'));
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

        if(\Yii::$app->request->get('searchString')){
            $searchModel['searchString'] = "%" . trim(\Yii::$app->request->get('searchString')) . "%";
        }
        if(\Yii::$app->request->get('date_from')){
            $searchModel['date_from'] = $searchModel->date_from = trim(\Yii::$app->request->get('date_from'));
        }
        if(\Yii::$app->request->get('date_to')){
            $searchModel['date_to'] = $searchModel->date_to = trim(\Yii::$app->request->get('date_to'));
        }
        $currencyData = Currency::getCurrencyData(\Yii::$app->request->get('filter_currency'), $this->currentFranchisee->id, 'client_id', $searchModel->date_from, $searchModel->date_to);
        if(count($currencyData['currency_list'])){
            $searchModel['filter_currency'] = key($currencyData['currency_list']);
        }

        if(\Yii::$app->request->get('filter_currency')){
            $searchModel['filter_currency'] = $searchModel->filter_currency = trim(\Yii::$app->request->get('filter_currency'));
        }

        if (Yii::$app->request->post("ClientSearch")) {
            $params['ClientSearch'] = Yii::$app->request->post("ClientSearch");
        }

        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);
        $exportFilename = 'clients_' . date("Y-m-d_H-m-s");
        $exportColumns = (new Organization())->getClientsExportColumns();

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('clients', compact('dataProvider', 'searchModel', 'exportFilename', 'exportColumns', 'currencyData'));
        } else {
            return $this->render('clients', compact('dataProvider', 'searchModel', 'exportFilename', 'exportColumns', 'currencyData'));
        }
    }


    public function actionAjaxUpdateCurrency()
    {
        $count = 0;
        $currencyList = [];
        if (Yii::$app->request->isPjax) {
            $currentUser = User::findIdentity(Yii::$app->user->id);
            $filter_from_date = \Yii::$app->request->get('filter_from_date') ? trim(\Yii::$app->request->get('filter_from_date')) : date("d-m-Y", strtotime(" -2 months"));
            $filter_to_date = \Yii::$app->request->get('filter_to_date') ? trim(\Yii::$app->request->get('filter_to_date')) : date("d-m-Y");
            $currency_list = Currency::getCurrencyData($currentUser->organization_id, $this->currentFranchisee->id, 'client_id', $filter_from_date, $filter_to_date);
            $currencyList = $currency_list['currency_list'];
            $count = count($currencyList);
        }
        return $this->renderPartial('currency', compact('currencyList', 'count'));
    }


    public function actionAjaxShowClient($id) {
        $client = Organization::find()
                ->joinWith("franchiseeAssociate")
                ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id, 'organization.type_id' => Organization::TYPE_RESTAURANT])
                ->one();
        $showEditButton = true;
        if (empty($client)) {
            $client = Organization::find()
                ->where(['organization.id' => $id, 'organization.type_id' => Organization::TYPE_RESTAURANT])
                ->one();
            $showEditButton = false;
        }

        if (empty($client->buisinessInfo)) {
            $buisinessInfo = new BuisinessInfo();
            $buisinessInfo->setOrganization($client);
            $client->refresh();
        }

        if(!$client->phone || !$client->email || !$client->contact_name){
            $user = User::findOne(['organization_id' => $id]);
            if ($user) {
                if (!$client->contact_name) {
                    $client->contact_name = $user->profile->full_name;
                }

                if (!$client->phone) {
                    $client->phone = $user->profile->phone;
                }

                if (!$client->email) {
                    $client->email = $user->email;
                }
            }
        }

        return $this->renderAjax("_ajax-show-client", compact('client', 'showEditButton'));
    }


    /**
     * Show one restaurant
     */
    public function actionShowClient($id) {
        return $this->getOrganizationData($id, Yii::$app->params['client_type_string']);
    }


    /**
     * Add new restaurant
     */
    public function actionCreateClient() {
        $managersArray = $this->currentFranchisee->getFranchiseeEmployees(true);
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
                        // send email
                        $model = new Organization();
                        $model->sendGenerationPasswordEmail($user);

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

        return $this->render('create-client', compact('client', 'user', 'profile', 'buisinessInfo', 'managersArray'));
    }

    /**
     * Update restaurant
     */
    public function actionUpdateClient($id) {
        $managersArray = $this->currentFranchisee->getFranchiseeEmployees(true);
        $client = Organization::find()
                ->joinWith("franchiseeAssociate")
                ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id, 'organization.type_id' => Organization::TYPE_RESTAURANT])
                ->one();
        if (empty($client)) {
            throw new HttpException(404, Yii::t('app', 'franchise.controllers.get_out_seven', ['ru'=>'Нет здесь ничего такого, проходите, гражданин']));
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

        return $this->render('update-client', compact('client', 'buisinessInfo', 'managersArray'));
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


        if(\Yii::$app->request->get('searchString')){
            $searchModel['searchString'] = "%" . trim(\Yii::$app->request->get('searchString')) . "%";
        }
        if(\Yii::$app->request->get('date_from')){
            $searchModel['date_from'] = $searchModel->date_from = trim(\Yii::$app->request->get('date_from'));
        }
        if(\Yii::$app->request->get('date_to')){
            $searchModel['date_to'] = $searchModel->date_to = trim(\Yii::$app->request->get('date_to'));
        }
        if (Yii::$app->request->post("VendorSearch")) {
            $params['VendorSearch'] = Yii::$app->request->post("VendorSearch");
        }

        $currencyData = Currency::getCurrencyData(\Yii::$app->request->get('filter_currency'), $this->currentFranchisee->id, 'vendor_id', $searchModel->date_from, $searchModel->date_to);
        if(count($currencyData['currency_list'])){
            $searchModel['filter_currency'] = key($currencyData['currency_list']);
        }

        if(\Yii::$app->request->get('filter_currency')){
            $searchModel['filter_currency'] = $searchModel->filter_currency = trim(\Yii::$app->request->get('filter_currency'));
        }


        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);

        $exportFilename = 'vendors_' . date("Y-m-d_H-m-s");
        $exportColumns = (new Organization())->getVendorsExportColumns();

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('vendors', compact('dataProvider', 'searchModel', 'exportFilename', 'exportColumns', 'currencyData'));
        } else {
            return $this->render('vendors', compact('dataProvider', 'searchModel', 'exportFilename', 'exportColumns', 'currencyData'));
        }
    }

    /**
     * Displays vendors list
     *
     * @return mixed
     */
    public function actionAgent() {
        $searchModel = new \common\models\OrganizationSearch();
        $params = Yii::$app->request->getQueryParams();
        if(\Yii::$app->request->get('searchString')){
            $searchModel['searchString'] = "%" . trim(\Yii::$app->request->get('searchString')) . "%";
        }

        $today = new \DateTime();

        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = Yii::$app->formatter->asTime($this->currentFranchisee->getFirstOrganizationDate(), "php:d.m.Y");

        if (Yii::$app->request->post("ClientSearch")) {
            $params['ClientSearch'] = Yii::$app->request->post("ClientSearch");
        }
        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id);

        $exportFilename = 'clients_' . date("Y-m-d_H-m-s");
        $exportColumns = (new Organization())->getClientsExportColumns();

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('agent', compact('dataProvider', 'searchModel', 'exportFilename', 'exportColumns'));
        } else {
            return $this->render('agent', compact('dataProvider', 'searchModel', 'exportFilename', 'exportColumns'));
        }
    }


    public function actionAjaxShowVendor($id) {
        $vendor = Organization::find()
                ->joinWith("franchiseeAssociate")
                ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id, 'organization.type_id' => Organization::TYPE_SUPPLIER])
                ->one();
        $showEditButton = true;
        if (empty($vendor)) {
            $vendor = Organization::find()
                ->where(['organization.id' => $id, 'organization.type_id' => Organization::TYPE_SUPPLIER])
                ->one();
            $showEditButton = false;
        }

        if($vendor->allow_editing == 0) {
            $showEditButton = false;
        }

        if (empty($vendor->buisinessInfo)) {
            $buisinessInfo = new BuisinessInfo();
            $buisinessInfo->setOrganization($vendor);
            $vendor->refresh();
        }

        if(!$vendor->phone || !$vendor->email || !$vendor->contact_name){
            $user = User::findOne(['organization_id'=>$id]);

            if(!$vendor->contact_name){
                $vendor->contact_name = $user->profile->full_name ?? '';
            }

            if(!$vendor->phone){
                $vendor->phone = $user->profile->phone ?? '';
            }

            if(!$vendor->email){
                $vendor->email = $user->email ?? '';
            }
        }

        return $this->renderAjax("_ajax-show-vendor", compact('vendor', 'showEditButton'));
    }


    /**
     * Show one vendor
     */
    public function actionShowVendor($id) {
        return $this->getOrganizationData($id);
    }

    /**
     * Add new supplier
     */
    public function actionCreateVendor() {
        $managersArray = $this->currentFranchisee->getFranchiseeEmployees(true);
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
                        // send email
                        $model = new Organization();
                        $model->sendGenerationPasswordEmail($user);
                        $catalog->name = Yii::t('app', \common\models\Catalog::CATALOG_BASE_NAME);
                        $catalog->status = 1;
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

        return $this->render('create-vendor', compact('vendor', 'user', 'profile', 'buisinessInfo', 'managersArray'));
    }

    /**
     * Update vendor
     */
    public function actionUpdateVendor($id) {
        $managersArray = $this->currentFranchisee->getFranchiseeEmployees(true);
        $vendor = Organization::find()
                ->joinWith("franchiseeAssociate")
                ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id, 'organization.type_id' => Organization::TYPE_SUPPLIER])
                ->one();
        if (empty($vendor)) {
            throw new HttpException(404, Yii::t('app', 'franchise.controllers.get_out_eight', ['ru'=>'Нет здесь ничего такого, проходите, гражданин']));
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
        return $this->render('update-vendor', compact('vendor', 'buisinessInfo', 'managersArray'));
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
                $associate->franchisee_id = Yii::$app->params['franchisee_id'];
                $associate->save();
            }
        }
        return 'success';
    }


    private function getOrganizationData($id, $type='vendor') {
        $organization = Organization::find()
            ->joinWith("franchiseeAssociate")
            ->where(['organization.id' => $id, 'organization.type_id' => ($type=='vendor') ? Organization::TYPE_SUPPLIER : Organization::TYPE_RESTAURANT])
            ->one();
        $showButton = false;
        if (empty($organization->buisinessInfo)) {
            $buisinessInfo = new BuisinessInfo();
            $buisinessInfo->setOrganization($organization);
            $organization->refresh();
        }

        $searchModel = ($type=='vendor') ? new \franchise\models\ClientSearch() : new \franchise\models\VendorSearch();
        $params = Yii::$app->request->getQueryParams();
        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = Yii::$app->formatter->asTime($this->currentFranchisee->getFirstOrganizationDate(), "php:d.m.Y");

        $currencyData = Currency::getCurrencyData(\Yii::$app->request->get('filter_currency'), $this->currentFranchisee->id, $type.'_id', $searchModel->date_from, $searchModel->date_to);
        if(count($currencyData['currency_list'])){
            $searchModel['filter_currency'] = key($currencyData['currency_list']);
        }

        if(\Yii::$app->request->get('filter_currency')){
            $searchModel['filter_currency'] = $searchModel->filter_currency = trim(\Yii::$app->request->get('filter_currency'));
        }

        $dataProvider = $searchModel->search($params, $this->currentFranchisee->id, $id);
        $model = Organization::get_value($id);
        $managersDataProvider = $model->getOrganizationManagersDataProvider();

        if(isset($organization->franchiseeAssociate->franchisee_id) && $organization->franchiseeAssociate->franchisee_id == $this->currentFranchisee->id){
            $showButton = true;
            $catalog = \common\models\Catalog::find()->where(['supp_org_id' => $organization->id, 'type' => \common\models\Catalog::BASE_CATALOG])->one();
        }
        return $this->render("show-".$type, compact('organization','dataProvider', 'searchModel', 'managersDataProvider', 'catalog', 'showButton', 'currencyData'));
    }


    public function actionUpdateUsersOrganization($organization_id){
        $organization = Organization::find()
            ->joinWith("franchiseeAssociate")
            ->where(['organization.id' => $organization_id, 'franchisee_associate.franchisee_id' => $this->currentFranchisee->id])
            ->one();
        if(!$organization || !$organization->is_allowed_for_franchisee){
            throw new HttpException(403, Yii::t('app', 'franchise.controllers.no_access', ['ru'=>'Организация закрыла доступ к своему кабинету']));
        }
        $user_id = $this->currentUser->id;
        $user = User::findOne($user_id);
        $user->organization_id = $organization_id;
        $user->save();

        ManagerAssociate::deleteAll(['manager_id'=>$user_id]);

        $restaurants = RelationSuppRest::findAll(['supp_org_id' => $organization_id]);
        foreach ($restaurants as $restaurant){
            $rest_id = $restaurant->rest_org_id;
            $ma = new ManagerAssociate();
            $ma->manager_id = $this->currentUser->id;
            $ma->organization_id = $rest_id;
            $ma->save();
        }

        $redirectURL = Yii::$app->params['staticUrl'][Yii::$app->language]['home'] . "user/login";
        return $this->redirect($redirectURL);
    }
}
