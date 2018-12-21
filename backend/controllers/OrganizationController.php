<?php

namespace backend\controllers;

use api_web\components\WebApi;
use backend\models\TestVendorsSearch;
use common\models\AllService;
use common\models\edi\EdiOrganization;
use common\models\edi\EdiProvider;
use common\models\Franchisee;
use common\models\FranchiseeAssociate;
use common\models\IntegrationSetting;
use common\models\IntegrationSettingValue;
use common\models\licenses\License;
use common\models\licenses\LicenseOrganization;
use common\models\RelationSuppRest;
use common\models\edi\EdiRoamingMap;
use common\models\TestVendors;
use common\models\User;
use Yii;
use common\models\Organization;
use common\models\Role;
use backend\models\OrganizationSearch;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * OrganizationController implements the CRUD actions for Organization model.
 */
class OrganizationController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'test-vendors',
                            'create-test-vendor',
                            'update-test-vendor',
                            'start-test-vendors-updating',
                            'notifications',
                            'ajax-update-status',
                            'ajax-update-vendor-is-work',
                            'ajax-update-edi-list',
                            'ajax-update-license-organization',
                            'list-organizations-for-licenses',
                            'add-license',
                            'edi-settings',
                            'update-edi-settings',
                            'create-edi-settings',
                            'integration-settings',
                            'update-integration-settings'
                        ],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                    [
                        'actions' => ['update'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Organization models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrganizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all TestVendors models.
     *
     * @return mixed
     */
    public function actionTestVendors()
    {
        $searchModel = new TestVendorsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('test-vendors', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Organization model.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new TestVendors model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreateTestVendor()
    {
        $model = new TestVendors();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['test-vendors']);
        } else {
            return $this->render('create-test-vendor', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates TestVendors model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionUpdateTestVendor($id)
    {
        $model = TestVendors::findOne(['id' => $id]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['test-vendors']);
        } else {
            return $this->render('update-test-vendor', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates TestVendors.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionStartTestVendorsUpdating()
    {
        $clients = Organization::findAll(['type_id' => Organization::TYPE_RESTAURANT]);
        foreach ($clients as $client) {
            TestVendors::setGuides($client);
        }
        return $this->redirect(['test-vendors']);
    }

    /**
     * Updates an existing Organization model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $franchiseeModel = $this->findFranchiseeAssociateModel($id);
        $franchiseeList = ArrayHelper::map(Franchisee::find()->all(), 'id', 'legal_entity');
        if ($model->load(Yii::$app->request->post()) && $model->save() && $franchiseeModel->load(Yii::$app->request->post()) && $franchiseeModel->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', compact('model', 'franchiseeModel', 'franchiseeList'));
        }
    }

//    /**
//     * Deletes an existing Organization model.
//     * If deletion is successful, the browser will be redirected to the 'index' page.
//     * @param integer $id
//     * @return mixed
//     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

    /**
     * Finds the Organization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Organization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Organization::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.organization_page_error', ['ru' => 'The requested page does not exist.']));
        }
    }

    /**
     * Finds the Organization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Organization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findFranchiseeAssociateModel($id)
    {
        if (($model = FranchiseeAssociate::findOne(['organization_id' => $id])) == null) {
            $model = new FranchiseeAssociate();
        }
        return $model;
    }

    /**
     * Edit notifications.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionNotifications(int $id)
    {
        Yii::$app->language = 'ru';
        $users = User::find()->leftJoin('relation_user_organization', 'relation_user_organization.user_id = user.id')->where('relation_user_organization.organization_id=' . $id)->all();
        if (count(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();
            $emails = $post['Email'];
            foreach ($emails as $userId => $fields) {
                $user = User::findOne(['id' => $userId]);
                if (isset($post['User'][$userId]['subscribe'])) {
                    $user->subscribe = $post['User'][$userId]['subscribe'];
                    $user->save();
                }
                $emailNotification = $user->getEmailNotification($id);
                foreach ($fields as $key => $value) {
                    $emailNotification->$key = $value;
                }
                $emailNotification->save();
                unset($user);
            }
            $sms = $post['Sms'];
            foreach ($sms as $userId => $fields) {
                $user = User::findOne(['id' => $userId]);
                $smsNotification = $user->getSmsNotification($id);
                foreach ($fields as $key => $value) {
                    $smsNotification->$key = $value;
                }
                $smsNotification->save();
                unset($user);
            }
            return $this->redirect(['view', 'id' => $id]);
        }
        return $this->render('notifications', compact('users', 'id'));
    }

    public function actionAjaxUpdateStatus()
    {
        if (Yii::$app->request->isAjax) {
            $status = Yii::$app->request->post('value');
            $organizationId = str_replace('blacklisted_', '', Yii::$app->request->post('id'));
            $organization = Organization::findOne(['id' => $organizationId]);
            $organization->blacklisted = $status;
            $organization->save();
            return true;
        } else {
            return false;
        }
    }

    public function actionAjaxUpdateVendorIsWork()
    {
        if (Yii::$app->request->isAjax) {
            $status = Yii::$app->request->post('value');
            $organizationId = str_replace('vendor_is_work_', '', Yii::$app->request->post('id'));
            $organization = Organization::findOne(['id' => $organizationId]);
            $organization->vendor_is_work = $status;
            $organization->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Lists all Organization models.
     *
     * @return mixed
     */
    public function actionListOrganizationsForLicenses()
    {
        $searchModel = new OrganizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, true);
        $db = Yii::$app->get('db_api');
        $dbNameArr = explode(';dbname=', $db->dsn);
        $dbName = "`" . $dbNameArr[1] . "`";
        $date = new \DateTime('+10 day');
        $tenDaysAfter = $date->format('Y-m-d H:i:s');

        return $this->render('list-organizations-for-licenses', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'dbName'       => $dbName,
            'tenDaysAfter' => $tenDaysAfter
        ]);
    }

    /**
     * Lists all Organization models.
     *
     * @return mixed
     */
    public function actionAddLicense(int $id)
    {
        $organizationObject = Organization::findOne(['id' => $id]);
        if (!$organizationObject) {
            throw new BadRequestHttpException();
        }

        $organizations = ArrayHelper::map([$organizationObject->toArray()], 'id', 'name');
        if (!$organizations) {
            throw new BadRequestHttpException();
        }

        $parentOrganizationObject = Organization::findOne(['id' => $organizationObject->parent_id]);
        if ($parentOrganizationObject) {
            $parentOrganization = ArrayHelper::map([$parentOrganizationObject->toArray()], 'id', 'name');
            $organizations = ArrayHelper::merge($organizations, $parentOrganization);
        }

        $childOrganizations = ArrayHelper::map(Organization::findAll(['parent_id' => $id]), 'id', 'name');
        $organizations = ArrayHelper::merge($organizations, $childOrganizations);
        $licenses = ArrayHelper::map(License::find()->where(['is_active' => true])->orderBy('sort_index')->all(), 'id', 'name');
        if (Yii::$app->request->isPost && !empty(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();
            foreach ($post['organizations'] as $organizationID) {
                foreach ($post['licenses'] as $licenseID) {
                    $license = License::findOne(['id' => $licenseID]);
                    $licenseOrganization = new LicenseOrganization();
                    $licenseOrganization->license_id = $license->id;
                    $licenseOrganization->org_id = $organizationID;
                    $licenseOrganization->status_id = LicenseOrganization::STATUS_ACTIVE;
                    $licenseOrganization->fd = new Expression('NOW()');
                    $licenseOrganization->td = date("Y-m-d H:i:s", strtotime($post['td'][$licenseID]));
                    $licenseOrganization->save();
                }
            }
            Yii::$app->session->setFlash('licenses-added', 'Лицензии добавлены');
            return $this->redirect('/organization/list-organizations-for-licenses');
        }

        $date = new \DateTime('+10 day');
        $tenDaysAfter = $date->format('Y-m-d H:i:s');
        $date2 = new \DateTime();
        $nowDate = $date2->format('Y-m-d H:i:s');

        return $this->render('add-license', ['licenses' => $licenses, 'organizations' => $organizations, 'tenDaysAfter' => $tenDaysAfter, 'nowDate' => $nowDate, 'organizationID' => $id]);
    }

    public function actionAjaxUpdateLicenseOrganization()
    {
        if (Yii::$app->request->isAjax) {
            $licenseOrgId = Yii::$app->request->post('licenseOrgId');
            $priceInputValue = Yii::$app->request->post('priceInputValue');
            $isDeletedValue = Yii::$app->request->post('isDeletedValue');
            $licenseOrganization = LicenseOrganization::findOne(['id' => $licenseOrgId]);
            if ($licenseOrganization) {
                $licenseOrganization->price = (float)$priceInputValue;
                $licenseOrganization->is_deleted = ($isDeletedValue == 'true') ? 1 : 0;
                $licenseOrganization->save();
                return 'success';
            }
            return 'error';
        } else {
            return false;
        }
    }

    /**
     * Lists all EdiSettings models.
     *
     * @return mixed
     */
    public function actionEdiSettings(int $id)
    {
        $organization = Organization::findOne(['id' => $id]);
        $ediOrganizations = EdiOrganization::find()->with('ediProvider');
        $dataProvider = new ActiveDataProvider([
            'query'      => $ediOrganizations,
            'sort'       => [
                'attributes' => [
                    'provider_priority',
                ]
            ],
            'pagination' => ['pageSize' => 20]
        ]);
        $ediOrganizations->andFilterWhere([
            'organization_id' => $id
        ]);

        return $this->render('edi-settings', [
            'dataProvider' => $dataProvider,
            'organization' => $organization
        ]);
    }

    /**
     * Edit EdiSettings.
     *
     * @return mixed
     */
    public function actionUpdateEdiSettings(int $id)
    {
        $model = EdiOrganization::findOne(['id' => $id]);
        $post = Yii::$app->request->post();
        return $this->handleEdiSettings($model, $id, $post, false);
    }

    /**
     * Create EdiSettings.
     *
     * @return mixed
     */
    public function actionCreateEdiSettings(int $id)
    {
        $model = new EdiOrganization();
        $post = Yii::$app->request->post();
        return $this->handleEdiSettings($model, $id, $post, true);
    }

    public function actionAjaxUpdateEdiList()
    {
        if (Yii::$app->request->isAjax) {
            $providerID = Yii::$app->request->post('value');
            $orgID = Yii::$app->request->post('org_id');
            $organization = Organization::findOne(['id' => $orgID]);
            $checkedOrganizations = [];
            $ediOrganizations = EdiOrganization::find();
            if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                $ediOrganizations->with('organization')->rightJoin('relation_supp_rest', 'edi_organization.organization_id = relation_supp_rest.supp_org_id')->where("provider_id = " . $providerID);
                $ediOrganizations = ArrayHelper::map($ediOrganizations->asArray()->all(), 'id', 'organization.name');
            } else {
                $ediOrganizations = [];
            }
            return $this->renderPartial('list-organizations-for-edi', [
                'ediOrganizations'     => $ediOrganizations,
                'checkedOrganizations' => $checkedOrganizations ?? null
            ]);
        } else {
            return false;
        }
    }

    private function handleEdiSettings($model, $id, $post, $isCreate = true)
    {
        if ($isCreate) {
            $model->organization_id = $id;
        }
        if ($model->load($post) && $model->validate() && $model->save()) {
            if (isset($post['organizations'])) {
                foreach ($post['organizations'] as $organizationID) {
                    $roamingMap = new  EdiRoamingMap();
                    $roamingMap->sender_edi_organization_id = $model->id;
                    $roamingMap->recipient_edi_organization_id = $organizationID;
                    $roamingMap->created_by_id = Yii::$app->user->id;
                    $roamingMap->save();
                }
            }
            if ($isCreate) {
                return $this->redirect(Url::to(['organization/edi-settings', 'id' => $id]));
            } else {
                return $this->redirect(Url::to(['organization/edi-settings', 'id' => $model->organization_id]));
            }

        }
        $providers = ArrayHelper::map(EdiProvider::find()->asArray()->all(), 'id', 'name');

        $organization = Organization::findOne(['id' => $id]);
        $checkedOrganizations = [];
        $ediOrganizations = EdiOrganization::find();

        if ($isCreate) {
            $defaultProvider = EdiProvider::find()->one();
            $providerID = $defaultProvider->id;
            $action = 'create-edi-settings';
        } else {
            $providerID = $model->provider_id;
            $action = 'update-edi-settings';
        }
        if ($organization->type_id == Organization::TYPE_RESTAURANT) {
            $ediOrganizations->with('organization')->innerJoin('relation_supp_rest', 'edi_organization.organization_id = relation_supp_rest.supp_org_id')->where("provider_id = $providerID")->andWhere('relation_supp_rest.rest_org_id = ' . $id);
            $ediOrganizations = ArrayHelper::map($ediOrganizations->asArray()->all(), 'id', 'organization.name');
        } else {
            $ediOrganizations = [];
        }

        return $this->render($action, [
            'model'                => $model,
            'providers'            => $providers,
            'organization'         => $organization,
            'ediOrganizations'     => $ediOrganizations,
            'orgID'                => $id,
            'checkedOrganizations' => $checkedOrganizations
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionIntegrationSettings($id)
    {
        $organization = Organization::findOne(['id' => $id]);
        $api = new WebApi();
        $list = $api->container->get('IntegrationWebApi')->list([])['services'];

        $dataProvider = new ArrayDataProvider([
            'allModels' => $list
        ]);

        return $this->render('integration-settings', [
            'dataProvider' => $dataProvider,
            'organization' => $organization
        ]);
    }

    public function actionUpdateIntegrationSettings()
    {
        $org_id = Yii::$app->request->get('org_id');
        $service_id = Yii::$app->request->get('service_id');
        $organization = Organization::findOne(['id' => $org_id]);
        $service = AllService::findOne($service_id);
        License::checkLicense($organization->id, $service->id);

        $s = IntegrationSetting::find()->where(['service_id' => $service->id])->all();

        return $this->render('update-integration-settings', [
            'service'      => $service,
            'organization' => $organization,
            'settings'     => $s
        ]);
    }

}
