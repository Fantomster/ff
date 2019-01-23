<?php

namespace api_web\classes;

use api_web\helpers\WebApiHelper;
use api_web\models\ForgotForm;
use common\models\BuisinessInfo;
use common\models\ManagerAssociate;
use common\models\RelationUserOrganization;
use Yii;
use api_web\exceptions\ValidationException;
use common\models\Profile;
use common\models\User;
use common\models\Role;
use common\models\Catalog;
use common\models\Organization;
use common\models\RelationSuppRest;
use yii\helpers\ArrayHelper;
use yii\validators\NumberValidator;
use yii\web\BadRequestHttpException;

/**
 * Class VendorWebApi
 *
 * @package api_web\classes
 */
class VendorWebApi extends \api_web\components\WebApi
{
    /**
     * Информация о поставщике
     *
     * @param $post
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function get($post)
    {
        $this->validateRequest($post, ['vendor_id']);
        if (!ArrayHelper::keyExists($post['vendor_id'], $this->user->organization->getSuppliers('', false))) {
            throw new BadRequestHttpException('vendor.you_are_not_working_with_this_supplier');
        }
        return WebApiHelper::prepareOrganization(Organization::findOne($post['vendor_id']));
    }

    /**
     * Создание нового поставщика в системе, находясь в аккаунте ресторана
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     * @throws ValidationException
     */
    public function create(array $post)
    {
        $vendorID = $post['user']['vendor_id'] ?? null;

        if ($vendorID) {
            $validator = new NumberValidator();
            if (!$validator->validate($vendorID)) {
                throw new ValidationException(['Field vendor_id mast be integer']);
            }

            $organization = Organization::findOne(['id' => $vendorID]);
            if (!$organization) {
                throw new BadRequestHttpException('vendor_not_found');
            }

            $relation = RelationSuppRest::findOne(['supp_org_id' => $vendorID, 'rest_org_id' => $this->user->organization->id]);
            if (empty($relation)) {
                $relation = $this->createRelation($this->user->organization->id, $organization->id);
            }
            $arVendorUsers = User::find()
                ->leftJoin(RelationUserOrganization::tableName() . ' ruo', 'ruo.user_id=user.id')
                ->leftJoin(Organization::tableName() . ' o', 'o.id=ruo.organization_id')
                ->where(['ruo.organization_id' => $vendorID, 'o.type_id' => Organization::TYPE_SUPPLIER])->all();

            foreach ($arVendorUsers as $vendorUser) {
                /**@var User $vendorUser */
                $this->createAssociateManager($vendorUser);
            }

            if ($relation->invite != RelationSuppRest::INVITE_ON) {
                foreach ($organization->users as $recipient) {
                    if ($recipient->role_id != Role::ROLE_SUPPLIER_MANAGER) {
                        continue;
                    }
                    //$this->user->sendInviteToVendor($recipient);
                    $this->user->sendClientInviteSupplier($recipient);
                    if ($recipient->profile->phone && $recipient->profile->sms_allow) {
                        $text = Yii::$app->sms->prepareText('sms.client_invite_repeat', [
                            'name' => $this->user->organization->name
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    }
                }
                $relation->invite = RelationSuppRest::INVITE_ON;
                $relation->save();
                $result = [
                    'success'         => true,
                    'organization_id' => $organization->id,
                    'message'         => "Приглашение отправлено.",
                    'is_edi'          => $organization->isEdi()
                ];
            } else {
                throw new BadRequestHttpException(\Yii::t('app', 'common.models.already_exists_two', ['ru' => 'Данный поставщик уже имеется в вашем списке контактов!']));
            }
            return $result;
        } else {
            $organization = new Organization();
            $businessInfo = new BuisinessInfo();
        }

        $email = $post['user']['email'];
        $fio = $post['user']['fio'];
        $org = $post['user']['organization_name'];
        $phone = $post['user']['phone'];

        $vendorUser = $this->vendorExists($email);

        if ($vendorUser) {
            $user = User::find()->where(['email' => $email])->one();
        } else {
            $user = new User();
        }

        $profile = new Profile();
        $profile->scenario = "invite";

        $currency = null;
        if (isset($post['currency'])) {
            $currency = \common\models\Currency::findOne(['id' => $post['currency']]);
        }

        $organization->type_id = Organization::TYPE_SUPPLIER; //org type_id
        $currentUser = $this->user;
        $arrCatalog = $post['catalog']['products'] ?? [];
        Catalog::addCatalog($arrCatalog, true);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$vendorUser) {
//                Создаем нового поставщика и организацию
                $user->email = $email;
                $businessInfo->legal_email = $email;
                $organization->email = $email;
                $user->setRegisterAttributes(Role::getManagerRole($organization->type_id));
                $user->newPassword = ForgotForm::generatePassword(8);
                $user->newPasswordConfirm = $user->newPassword;
                $user->status = User::STATUS_ACTIVE;
                if (!$user->validate() || !$user->save()) {
                    throw new ValidationException($user->getFirstErrors());
                }
                $profile->setUser($user->id);
                $profile->full_name = $fio;
                $profile->phone = $phone;
                $profile->sms_allow = Profile::SMS_ALLOW;
                if (!$profile->validate() || !$profile->save()) {
                    throw new ValidationException($profile->getFirstErrors());
                }

                if (!$vendorID) {
                    $organization->name = $org;
                    $organization->phone = $phone;
                    $businessInfo->phone = $phone;
                }

                if (!empty($post['user']['inn']) && !$vendorID) {
                    $businessInfo->inn = $post['user']['inn'];
                    $organization->inn = $post['user']['inn'];
                }

                if (!empty($post['user']['contact_name']) && !$vendorID) {
                    $organization->contact_name = $post['user']['contact_name'];
                }

                if (!$organization->validate() || !$organization->save()) {
                    throw new ValidationException($organization->getFirstErrors());
                }
                $businessInfo->organization_id = $organization->id;
                if (!$businessInfo->validate() || !$businessInfo->save()) {
                    throw new ValidationException($businessInfo->getFirstErrors());
                }

                $user->setOrganization($organization)->save();
                $user->createRelationUserOrganization($user->organization->id, $user->role_id);
                $get_supp_org_id = $organization->id;
                $currentOrganization = $currentUser->organization;
                if ($currentOrganization->step == Organization::STEP_ADD_VENDOR) {
                    $currentOrganization->step = Organization::STEP_OK;
                    $currentOrganization->save();
                }
                $this->createAssociateManager($user);
            } else {
                //Поставщик уже есть, но тот еще не авторизовался, забираем его org_id
                $get_supp_org_id = $vendorUser->organization_id;
                $this->createAssociateManager($vendorUser);
            }

            if (count($arrCatalog)) {
                $lastInsert_cat_id = $this->container->get('CatalogWebApi')->addBaseCatalog($get_supp_org_id, $currentUser, $arrCatalog, $currency);
            } else {
                $lastInsert_cat_id = 0;
            }
            /**
             * 5) Связь ресторана и поставщика
             * */
            $relation = $this->createRelation($currentUser->organization_id, $get_supp_org_id, $lastInsert_cat_id);
            /**
             * Отправка почты
             * */
            $relation->invite = RelationSuppRest::INVITE_ON;
            $relation->save();

            $currentOrganization = $currentUser->organization;
            $currentOrganization->step = Organization::STEP_OK;
            $currentOrganization->save();

            $transaction->commit();
            if (!empty($profile->phone)) {
                $text = Yii::$app->sms->prepareText('sms.client_invite', [
                    'name' => $currentUser->organization->name
                ]);
                Yii::$app->sms->send($text, $profile->phone);
            }
            $currentUser->sendInviteToVendor($user);

            $result = [
                'success'         => true,
                'organization_id' => $organization->id,
                'user_id'         => $user->id
            ];

            if (!$vendorUser) {
                $result['message'] = Yii::t('message', 'frontend.controllers.client.vendor', ['ru' => 'Поставщик ']) .
                    $organization->name .
                    Yii::t('message', 'frontend.controllers.client.and_catalog', ['ru' => ' и каталог добавлен! Инструкция по авторизации была отправлена на почту ']) . $email;
            } else {
                $result['message'] = Yii::t('message', 'frontend.controllers.client.catalog_added', ['ru' => 'Каталог добавлен! приглашение было отправлено на почту  ']) . $email . '';
            }
            return $result;
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * @param     $client_id
     * @param     $vendor_id
     * @param int $cat_id
     * @return RelationSuppRest
     * @throws ValidationException
     */
    private function createRelation($client_id, $vendor_id, $cat_id = 0)
    {
        $relationSuppRest = new RelationSuppRest();
        $relationSuppRest->rest_org_id = $client_id;
        $relationSuppRest->supp_org_id = $vendor_id;
        $relationSuppRest->cat_id = $cat_id;
        $relationSuppRest->status = 1;
        $relationSuppRest->invite = RelationSuppRest::INVITE_OFF;
        if (isset($relationSuppRest->uploaded_catalog)) {
            $relationSuppRest->uploaded_processed = 0;
        }
        if (!$relationSuppRest->save()) {
            throw new ValidationException($relationSuppRest->getFirstErrors());
        }
        return $relationSuppRest;
    }

    /**
     * Поиск поставщика по емайл
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function search(array $post)
    {
        $this->validateRequest($post, ['email']);

        $result = [];
        $email = $post['email'];

        $models = Organization::find()
            ->joinWith(['relationUserOrganization', 'relationUserOrganization.user'])
            ->where(['organization.type_id' => Organization::TYPE_SUPPLIER])
            ->andWhere(['or', [
                'organization.email' => $email
            ], [
                'user.email' => $email
            ]])->all();

        if (!empty($models)) {
            /**
             * @var $model Organization
             */
            foreach ($models as $model) {
                $r = WebApiHelper::prepareOrganization($model);

                if ($user = RelationUserOrganization::find()->joinWith('user')->where([
                    'relation_user_organization.organization_id' => $model->id,
                    'user.email'                                 => $email
                ])->one()) {
                    $r['user'] = [
                        'email' => $user->user->email,
                        'name'  => $user->user->profile->full_name,
                        'phone' => $user->user->profile->phone,
                    ];
                }

                $result[] = $r;
            }
        }

        return $result;
    }

    /**
     * Обновление поставщика
     *
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function update(array $post)
    {
        $this->validateRequest($post, ['id']);
        //Поиск поставщика в системе
        $model = Organization::find()->where(['id' => $post['id'], 'type_id' => Organization::TYPE_SUPPLIER])->one();
        if (empty($model)) {
            throw new BadRequestHttpException('vendor_not_found');
        }

        //Если запрос от ресторана
        if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
            //Проверяем, работает ли ресторан с этим поставщиком
            $vendor_ids = [];
            $searchModel = new \common\models\search\VendorSearch();
            $dataProvider = $searchModel->search([], $this->user->organization->id);
            $dataProvider->pagination->setPage(0);
            $dataProvider->pagination->pageSize = 1000;
            $vendors = $dataProvider->getModels();
            if (!empty($vendors)) {
                foreach ($vendors as $vendor) {
                    $vendor_ids[] = $vendor->supp_org_id;
                }
                if (!in_array($model->id, array_unique($vendor_ids))) {
                    throw new BadRequestHttpException('vendor.you_are_not_working_with_this_supplier');
                }
            } else {
                throw new BadRequestHttpException('vendor.not_found_vendors');
            }

            //Можно ли ресторану редактировать этого поставщика
            if ($model->allow_editing == 0) {
                throw new BadRequestHttpException('vendor.not_allow_editing');
            }
        }

        //Если запрос на изменение прилетел от поставщика
        if ($this->user->organization->type_id == Organization::TYPE_SUPPLIER) {
            //Разрешаем редактировать только свои данные
            if ($model->id != $this->user->organization->id) {
                throw new BadRequestHttpException('vendor.not_you_editing');
            }
        }

        //прошли все проверки, будем обновлять поставщика
        $transaction = \Yii::$app->db->beginTransaction();
        try {

            if (!empty($post['phone'])) {
                $model->phone = $post['phone'];
            }

            if (!empty($post['site'])) {
                $model->website = $post['site'];
            }

            if (!empty($post['email'])) {
                $model->email = $post['email'];
            }

            if (!empty($post['name'])) {
                $model->name = $post['name'];
            }

            if (!empty($post['inn'])) {
                $model->inn = $post['inn'];
            }

            if (!empty($post['gmt'])) {
                $model->gmt = $post['gmt'];
            }

            if (!empty($post['contact_name'])) {
                $model->contact_name = $post['contact_name'];
            }

            if (!empty($post['address'])) {
                if (!empty($post['address']['country'])) {
                    $model->country = $post['address']['country'];
                }
                if (!empty($post['address']['region'])) {
                    $model->administrative_area_level_1 = $post['address']['region'];
                }
                if (!empty($post['address']['locality'])) {
                    $model->locality = $post['address']['locality'];
                    $model->city = $post['address']['locality'];
                }
                if (!empty($post['address']['route'])) {
                    $model->route = $post['address']['route'];
                }
                if (!empty($post['address']['house'])) {
                    $model->street_number = $post['address']['house'];
                }
                if (!empty($post['address']['lat'])) {
                    $model->lat = $post['address']['lat'];
                }
                if (!empty($post['address']['lng'])) {
                    $model->lng = $post['address']['lng'];
                }
                if (!empty($post['address']['place_id'])) {
                    $model->place_id = $post['address']['place_id'];
                }
                unset($post['address']['lat']);
                unset($post['address']['lng']);
                unset($post['address']['place_id']);
                $model->address = implode(', ', $post['address']);
                $model->formatted_address = $model->address;
            }

            if (!$model->validate()) {
                throw new ValidationException($model->getFirstErrors());
            }

            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }

            $transaction->commit();
            return WebApiHelper::prepareOrganization($model);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Обновление логотипа поставщика
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function uploadLogo(array $post)
    {
        $this->validateRequest($post, ['vendor_id']);

        $vendor = Organization::findOne($post['vendor_id']);
        if (empty($vendor)) {
            throw new BadRequestHttpException('vendor_not_found');
        }

        $this->validateRequest($post, ['image_source']);

        if ($vendor->type_id !== Organization::TYPE_SUPPLIER) {
            throw new BadRequestHttpException('vendor.is_not_vendor');
        }

        //Можно ли ресторану редактировать этого поставщика
        if ($vendor->allow_editing == 0) {
            throw new BadRequestHttpException('vendor.not_allow_editing');
        }

        /**
         * Поехало обновление картинки
         */
        $vendor->scenario = "settings";
        $vendor->picture = WebApiHelper::convertLogoFile($post['image_source']);

        if (!$vendor->validate()) {
            throw new ValidationException($vendor->getFirstErrors());
        }

        if (!$vendor->save()) {
            throw new ValidationException($vendor->getFirstErrors());
        }

        return WebApiHelper::prepareOrganization($vendor);
    }

    /**
     * @param $email
     * @return bool|User|null
     * @throws BadRequestHttpException
     */
    private function vendorExists($email)
    {
        $vendorUser = User::findOne(['email' => $email]);

        if (!$vendorUser) {
            return false;
        } elseif (empty($vendorUser->organization_id)) {
            throw new BadRequestHttpException("User with email: found in our system, but he did not complete the registration. 
            As soon as he goes through the supplier registration procedure, you can add him.|{$vendorUser->email}");
        }

        if ($vendorUser->organization->type_id != Organization::TYPE_SUPPLIER) {
            //найден email ресторана
            throw new BadRequestHttpException(\Yii::t('app', 'common.models.already_in_use',
                ['ru' => 'Данный email не может быть использован']));
        }

        $userRelationSuppRest = RelationSuppRest::find()
            ->where(['rest_org_id' => $this->user->organization_id, 'supp_org_id' => $vendorUser->organization_id, 'deleted' => false])
            ->one();
        if (!$userRelationSuppRest) {
            $managersIsActive = User::find()->where(['organization_id' => $vendorUser->organization_id, 'status' => 1])->count();
            if ($managersIsActive == 0) {
                //поставщик не авторизован
                //добавляем к базовому каталогу поставщика каталог ресторана и создаем связь
//                throw new BadRequestHttpException(\Yii::t('app', 'common.models.vendor_not_auth', ['ru' => 'Поставщик еще не авторизован / добавляем каталог']));
//                $result = ['success'      => true, 'eventType' => self::NO_AUTH_ADD_RELATION_AND_CATALOG, 'message' => ),

                return $vendorUser;
            } else {
                //поставщик авторизован
                throw new BadRequestHttpException(\Yii::t('app', 'common.models.already_register', ['ru' => 'Поставщик уже зарегистрирован в системе, Вы можете его добавить нажав кнопку Пригласить']));
            }
        }

        if ($userRelationSuppRest->invite == RelationSuppRest::INVITE_ON) {
            //есть связь с поставщиком invite_on
            throw new BadRequestHttpException(\Yii::t('app', 'common.models.already_exists_two', ['ru' => 'Данный поставщик уже имеется в вашем списке контактов!']));
        } else {
            //поставщику было отправлено приглашение, но поставщик еще не добавил этот ресторан
            throw new BadRequestHttpException(\Yii::t('app', 'common.models.already_sent', ['ru' => 'Вы уже отправили приглашение этому поставщику, ожидается подтверждение от поставщика']));
        }
    }

    /**
     * @param User $vendorUser
     */
    private function createAssociateManager($vendorUser): void
    {
        if (!$vendorUser->organization->vendor_is_work) {
            if (!ManagerAssociate::find()->where(['manager_id' => $vendorUser->id, 'organization_id' => $this->user->organization->id])->exists()) {
                $managerAssociate = new ManagerAssociate();
                $managerAssociate->manager_id = $vendorUser->id;
                $managerAssociate->organization_id = $this->user->organization->id;
                $managerAssociate->save();
            }
        }
    }
}
