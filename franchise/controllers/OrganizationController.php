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
     * Displays clients list
     * 
     * @return mixed
     */
    public function actionClients() {
        return $this->render("/site/under-construction");
    }

    /**
     * Add new restaurant
     */
    public function actionCreateClient() {
        $client = new Organization();
        $client->type_id = Organization::TYPE_RESTAURANT;
        $user = new User();
        $user->password = uniqid();
        $profile = new Profile();
        $buisinessInfo = new BuisinessInfo();

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            if ($user->load($post) && $profile->load($post) && $client->load($post) && $buisinessInfo->load($post)) {

                if ($user->validate() && $profile->validate() && $client->validate() && $buisinessInfo->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $user->setRegisterAttributes(Role::ROLE_RESTAURANT_MANAGER, User::STATUS_ACTIVE)->save();
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
    
    public function actionAjaxShowVendor($id) {
        $vendor = Organization::find()
                ->joinWith("franchiseeAssociate")
                ->where(['franchisee_associate.franchisee_id' => $this->currentFranchisee->id, 'organization.id' => $id])
                ->one();
        return $this->renderAjax("_ajax-show-vendor", compact('vendor'));
    }

    /**
     * Add new supplier
     */
    public function actionCreateVendor() {
        $vendor = new Organization();
        $vendor->type_id = Organization::TYPE_RESTAURANT;
        $user = new User();
        $user->password = uniqid();
        $profile = new Profile();
        $buisinessInfo = new BuisinessInfo();

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            if ($user->load($post) && $profile->load($post) && $vendor->load($post) && $buisinessInfo->load($post)) {

                if ($user->validate() && $profile->validate() && $vendor->validate() && $buisinessInfo->validate()) {

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $user->setRegisterAttributes(Role::ROLE_RESTAURANT_MANAGER, User::STATUS_ACTIVE)->save();
                        $profile->setUser($user->id)->save();
                        $vendor->save();
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
     * Adds organization to franchisee
     * 
     * @return boolean
     */
    private function addOrganization($organization) {
        $associate = new FranchiseeAssociate();
        $associate->organization_id = $organization->id;
        $associate->franchisee_id = $this->currentFranchisee->id;
        return $associate->save();
    }

}
