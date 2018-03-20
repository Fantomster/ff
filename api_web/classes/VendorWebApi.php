<?php

namespace api_web\classes;

use api_web\exceptions\ValidationException;
use Yii;
use common\models\Profile;
use common\models\restaurant\RestaurantChecker;
use common\models\User;
use common\models\Role;
use common\models\Catalog;
use common\models\CatalogGoods;
use common\models\Organization;
use common\models\RelationSuppRest;
use common\models\CatalogBaseGoods;
use yii\web\BadRequestHttpException;

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
                if ($check['eventType'] == 5) {
                    $result = ['success' => true, 'organization_id'=>$organization->id, 'user_id'=>$user->id, 'message' => Yii::t('message', 'frontend.controllers.client.vendor', ['ru' => 'Поставщик ']) . $organization->name . Yii::t('message', 'frontend.controllers.client.and_catalog', ['ru' => ' и каталог добавлен! Инструкция по авторизации была отправлена на почту ']) . $email . ''];
                    return $result;
                } else {
                    $result = ['success' => true, 'organization_id'=>$organization->id, 'user_id'=>$user->id, 'message' => Yii::t('message', 'frontend.controllers.client.catalog_added', ['ru' => 'Каталог добавлен! приглашение было отправлено на почту  ']) . $email . ''];
                    return $result;
                }
            } catch (Exception $e) {
                $transaction->rollback();
                throw new BadRequestHttpException(Yii::t('message', 'frontend.controllers.client.no_save', ['ru' => 'сбой сохранения, попробуйте повторить действие еще раз']));
            }
        }
    }

}