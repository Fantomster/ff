<?php

namespace api_web\classes;

use api_web\helpers\WebApiHelper;
use Yii;
use api_web\exceptions\ValidationException;
use common\models\Profile;
use common\models\restaurant\RestaurantChecker;
use common\models\User;
use common\models\Role;
use common\models\Catalog;
use common\models\Organization;
use common\models\RelationSuppRest;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

/**
 * Class VendorWebApi
 * @package api_web\classes
 */
class VendorWebApi extends \api_web\components\WebApi
{
    /**
     * Создание нового поставщика в системе, находясь в аккаунте ресторана
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function create(array $post)
    {
        $email = $post['user']['email'];
        $fio = $post['user']['fio'];
        $org = $post['user']['organization_name'];
        $phone = $post['user']['phone'];

        $check = RestaurantChecker::checkEmail($email);

        if ($check['eventType'] != 5) {
            $user = User::find()->where(['email' => $email])->one();
        } else {
            $user = new User();
        }
        $relationSuppRest = new RelationSuppRest();
        $organization = new Organization();
        $profile = new Profile();
        $profile->scenario = "invite";

        $currency = null;
        if (isset($post['currency'])) {
            $currency = \common\models\Currency::findOne(['id' => $post['currency']]);
        }

        $organization->type_id = Organization::TYPE_SUPPLIER; //org type_id
        $currentUser = $this->user;
        $arrCatalog = $post['catalog']['products'];
        Catalog::addCatalog($arrCatalog);


        if (in_array($check['eventType'], [1, 2, 4, 6])) {
            throw new BadRequestHttpException(Yii::t('app', 'common.models.already_in_use', ['ru' => 'Данный email не может быть использован']));
        }

        if ($check['eventType'] == 3 || $check['eventType'] == 5) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($check['eventType'] == 5) {
                    /**
                     *
                     * Создаем нового поставщика и организацию
                     *
                     * */
                    $user->email = $email;
                    $user->setRegisterAttributes(Role::getManagerRole($organization->type_id));
                    $user->status = User::STATUS_UNCONFIRMED_EMAIL;
                    if (!$user->validate()) {
                        throw new ValidationException($user->getFirstErrors());
                    }
                    $user->save();
                    $profile->setUser($user->id);
                    $profile->full_name = $fio;
                    $profile->phone = $phone;
                    $profile->sms_allow = Profile::SMS_ALLOW;
                    if (!$profile->validate()) {
                        throw new ValidationException($profile->getFirstErrors());
                    }
                    $profile->save();
                    $organization->name = $org;
                    if (!$organization->validate()) {
                        throw new ValidationException($organization->getFirstErrors());
                    }
                    $organization->save();
                    $user->setOrganization($organization)->save();
                    $get_supp_org_id = $organization->id;
                    $currentOrganization = $currentUser->organization;
                    if ($currentOrganization->step == Organization::STEP_ADD_VENDOR) {
                        $currentOrganization->step = Organization::STEP_OK;
                        $currentOrganization->save();
                    }
                } else {
                    //Поставщик уже есть, но тот еще не авторизовался, забираем его org_id
                    $get_supp_org_id = $check['org_id'];
                }

                $lastInsert_cat_id = Catalog::addBaseCatalog($check, $get_supp_org_id, $currentUser, $arrCatalog, $currency);

                /**
                 *
                 * 5) Связь ресторана и поставщика
                 *
                 * */
                $relationSuppRest->rest_org_id = $currentUser->organization_id;
                $relationSuppRest->supp_org_id = $get_supp_org_id;
                $relationSuppRest->cat_id = $lastInsert_cat_id;
                $relationSuppRest->status = 1;
                $relationSuppRest->invite = RelationSuppRest::INVITE_ON;
                if (isset($relationSuppRest->uploaded_catalog)) {
                    $relationSuppRest->uploaded_processed = 0;
                }
                $relationSuppRest->save();
                /**
                 *
                 * Отправка почты
                 *
                 * */
                $currentUser->sendInviteToVendor($user);
                $currentOrganization = $currentUser->organization;
                $currentOrganization->step = Organization::STEP_OK;
                $currentOrganization->save();

                if (!empty($profile->phone)) {
                    $text = Yii::$app->sms->prepareText('sms.client_invite', [
                        'name' => $currentUser->organization->name
                    ]);
                    Yii::$app->sms->send($text, $profile->phone);
                }
                $transaction->commit();

                $result = [
                    'success' => true,
                    'organization_id' => $organization->id,
                    'user_id' => $user->id
                ];

                if ($check['eventType'] == 5) {
                    $result['message'] =
                        Yii::t('message', 'frontend.controllers.client.vendor', ['ru' => 'Поставщик ']) .
                        $organization->name .
                        Yii::t('message', 'frontend.controllers.client.and_catalog', ['ru' => ' и каталог добавлен! Инструкция по авторизации была отправлена на почту ']) .
                        $email;
                } else {
                    $result['message'] = Yii::t('message', 'frontend.controllers.client.catalog_added', ['ru' => 'Каталог добавлен! приглашение было отправлено на почту  ']) . $email . '';
                }
                return $result;

            } catch (\Exception $e) {
                $transaction->rollback();
                throw new BadRequestHttpException(Yii::t('message', 'frontend.controllers.client.no_save', ['ru' => 'сбой сохранения, попробуйте повторить действие еще раз']));
            }
        }
    }

    /**
     * Поиск поставщика по емайл
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function search(array $post)
    {
        if (empty($post['email'])) {
            throw new BadRequestHttpException('Empty search attribute email');
        }

        $email = $post['email'];

        $model = Organization::find()->where(['email' => $email, 'type_id' => Organization::TYPE_SUPPLIER])->one();
        if (!empty($model)) {
            return WebApiHelper::prepareOrganization($model);
        }

        $user = User::find()->where(['email' => $email])->one();
        if (!empty($user)) {
            throw new BadRequestHttpException("Email $email является пользователем. Необходимо уточнить email адрес поставщика.");
        }

        return [];
    }

    /**
     * Обновление поставщика
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function update(array $post)
    {
        if (empty($post['id'])) {
            throw new BadRequestHttpException('Empty attribute id');
        }
        //Поиск поставщика в системе
        $model = Organization::find()->where(['id' => $post['id'], 'type_id' => Organization::TYPE_SUPPLIER])->one();
        if (empty($model)) {
            throw new BadRequestHttpException('Vendor not found');
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
                    throw new BadRequestHttpException('You are not working with this supplier.');
                }
            } else {
                throw new BadRequestHttpException('You need to add vendors.');
            }

            //Можно ли ресторану редактировать этого поставщика
            if ($model->allow_editing == 0) {
                throw new BadRequestHttpException('Vendor not allow editing.');
            }
        }

        //Если запрос на изменение прилетел от поставщика
        if ($this->user->organization->type_id == Organization::TYPE_SUPPLIER) {
            //Разрешаем редактировать только свои данные
            if ($model->id != $this->user->organization->id) {
                throw new BadRequestHttpException('Вы можете редактировать только свои данные.');
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
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function uploadLogo(array $post)
    {
        if (empty($post['vendor_id'])) {
            throw new BadRequestHttpException('Empty attribute vendor_id');
        }

        $vendor = Organization::findOne($post['vendor_id']);
        if (empty($vendor)) {
            throw new BadRequestHttpException('Vendor not found');
        }

        if (empty($post['image_source'])) {
            throw new BadRequestHttpException('Empty image_source');
        }

        if ($vendor->type_id !== Organization::TYPE_SUPPLIER) {
            throw new BadRequestHttpException('The organization is not a vendor.');
        }

        //Можно ли ресторану редактировать этого поставщика
        if ($vendor->allow_editing == 0) {
            throw new BadRequestHttpException('Vendor not allow editing.');
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
}