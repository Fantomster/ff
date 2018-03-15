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
        $this->addCatalog($arrCatalog);

        $fio = $post['user']['fio'];
        $org = $post['user']['organization_name'];
        $phone = $post['user']['phone'];

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
                    $user->save();
                    $profile->setUser($user->id);
                    $profile->full_name = $fio;
                    $profile->phone = $phone;
                    $profile->sms_allow = Profile::SMS_ALLOW;
                    $profile->save();
                    $organization->name = $org;
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

                $lastInsert_cat_id = $this->addBaseCatalog($check, $get_supp_org_id, $currentUser, $arrCatalog, $currency);

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


    public function addCatalog($arrCatalog){
        if ($arrCatalog === Array()) {
            throw new BadRequestHttpException(Yii::t('message', 'frontend.controllers.client.empty_catalog', ['ru' => 'Каталог пустой!']));
        }

        $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
        if (count($arrCatalog) > CatalogBaseGoods::MAX_INSERT_FROM_XLS) {
            throw new BadRequestHttpException(Yii::t('message', 'frontend.controllers.client.more_position', ['ru' => 'Чтобы добавить больше <strong> {max} </strong> позиций, пожалуйста свяжитесь с нами', 'max' => CatalogBaseGoods::MAX_INSERT_FROM_XLS])
                . '<a href="mailto://info@mixcart.ru" target="_blank" class="text-success">info@mixcart.ru</a>');
        }
        $productNames = [];
        foreach ($arrCatalog as $arrCatalogs) {
            if(!isset($arrCatalogs['product'])){
                throw new BadRequestHttpException(Yii::t('message', 'frontend.controllers.client.empty_catalog', ['ru' => 'Каталог пустой!']));
            }
            $product = strip_tags(trim($arrCatalogs['product']));
            $price = floatval(trim(str_replace(',', '.', $arrCatalogs['price'])));
            $ed = strip_tags(trim($arrCatalogs['ed']));
            if (empty($product)) {
                $result = ['attribute'=>'product', 'message' => Yii::t('error', 'frontend.controllers.client.empty_field', ['ru' => 'Ошибка: Пустое поле'])];
                throw new ValidationException($result);
            }

            $price = str_replace(',', '.', $price);
            if (empty($price) || !preg_match($numberPattern, $price)) {
                $result = ['attribute'=>'price', 'message' => Yii::t('message', 'frontend.controllers.client.wrong_price', ['ru' => 'Ошибка: <strong>[Цена]</strong> в неверном формате!'])];
                throw new ValidationException($result);
            }
            if (empty($units) || $units < 0) {
                $units = 0;
            }
            $units = str_replace(',', '.', $units);
            if (!empty($units) && !preg_match($numberPattern, $units)) {
                $result = ['attribute'=>'units', 'message' => Yii::t('message', 'frontend.controllers.client.wrong_measure', ['ru' => 'Ошибка: <strong>[Кратность]</strong> товара в неверном формате'])];
                throw new ValidationException($result);
            }
            if (empty($ed)) {
                $result = ['attribute'=>'ed', 'message' => Yii::t('message', 'frontend.controllers.client.empty', ['ru' => 'Ошибка: Пустое поле <strong>[Единица измерения]</strong>!'])];
                throw new ValidationException($result);
            }
            array_push($productNames, mb_strtolower(trim($product)));
        }

        if (count($productNames) !== count(array_flip($productNames))) {
            throw new BadRequestHttpException(Yii::t('app', 'Вы пытаетесь загрузить одну или более позиций с одинаковым наименованием!'));
        }
    }


    public function addBaseCatalog($check, $get_supp_org_id, $currentUser, $arrCatalog, $currency = null)
    {
        /**
         *
         * 2) Создаем базовый и каталог для ресторана
         *
         * */
        if ($check['eventType'] == 5) {
            $newBaseCatalog = new Catalog();
            $newBaseCatalog->supp_org_id = $get_supp_org_id;
            $newBaseCatalog->name = Yii::t('app', 'Главный каталог');
            $newBaseCatalog->type = Catalog::BASE_CATALOG;
            $newBaseCatalog->status = Catalog::STATUS_ON;
            if (isset($currency)) {
                $newBaseCatalog->currency_id = $currency->id;
            }
            $newBaseCatalog->save();
            $newBaseCatalog->refresh();
            $lastInsert_base_cat_id = $newBaseCatalog->id;
        } else {
            //Поставщик зарегистрирован, но не авторизован
            //проверяем, есть ли у поставщика Главный каталог и если нету, тогда создаем ему каталог
            if (Catalog::find()->where(['supp_org_id' => $get_supp_org_id, 'type' => Catalog::BASE_CATALOG])->exists()) {
                $lastInsert_base_cat_id = Catalog::find()->select('id')->where(['supp_org_id' => $get_supp_org_id, 'type' => Catalog::BASE_CATALOG])->one();
                $lastInsert_base_cat_id = $lastInsert_base_cat_id['id'];
            } else {
                $newBaseCatalog = new Catalog();
                $newBaseCatalog->supp_org_id = $get_supp_org_id;
                $newBaseCatalog->name = Yii::t('message', 'frontend.controllers.client.main_cat', ['ru' => 'Главный каталог']);
                $newBaseCatalog->type = Catalog::BASE_CATALOG;
                $newBaseCatalog->status = Catalog::STATUS_ON;
                if (isset($currency)) {
                    $newBaseCatalog->currency_id = $currency->id;
                }
                $newBaseCatalog->save();
                $newBaseCatalog->refresh();
                $lastInsert_base_cat_id = $newBaseCatalog->id;
            }
        }

        $newCatalog = new Catalog();
        $newCatalog->supp_org_id = $get_supp_org_id;
        $newCatalog->name = '"' . $currentUser->organization->name . '"';
        $newCatalog->type = Catalog::CATALOG;
        $newCatalog->status = Catalog::STATUS_ON;
        if (isset($currency)) {
            $newCatalog->currency_id = $currency->id;
        }
        $newCatalog->save();
        $lastInsert_cat_id = $newCatalog->id;
        $newCatalog->refresh();

        /**
         *
         * 3 и 4) Создаем каталог базовый и его продукты, создаем новый каталог для ресторана и забиваем продукты на основе базового каталога
         *
         * */
        $article_create = 0;
        foreach ($arrCatalog as $arrCatalogs) {
            $article_create++;
            $article = $article_create;
            $product = strip_tags(trim($arrCatalogs['product']));
            $units = null;

            if (empty($units) || $units < 0) {
                $units = null;
            }
            $price = strip_tags(trim($arrCatalogs['price']));
            $ed = strip_tags(trim($arrCatalogs['ed']));
            $price = str_replace(',', '.', $price);
            if (substr($price, -3, 1) == '.') {
                $price = explode('.', $price);
                $last = array_pop($price);
                $price = join($price, '') . '.' . $last;
            } else {
                $price = str_replace('.', '', $price);
            }
            $newProduct = new CatalogBaseGoods();
            $newProduct->scenario = "import";
            $newProduct->cat_id = $lastInsert_base_cat_id;
            $newProduct->supp_org_id = $get_supp_org_id;
            $newProduct->article = (string)$article;
            $newProduct->product = $product;
            $newProduct->units = $units;
            $newProduct->price = $price;
            $newProduct->ed = $ed;
            $newProduct->status = CatalogBaseGoods::STATUS_ON;
            $newProduct->market_place = CatalogBaseGoods::MARKETPLACE_OFF;
            $newProduct->deleted = CatalogBaseGoods::DELETED_OFF;
            $newProduct->save();
            $newProduct->refresh();

            $lastInsert_base_goods_id = $newProduct->id;

            $newGoods = new CatalogGoods();
            $newGoods->cat_id = $lastInsert_cat_id;
            $newGoods->base_goods_id = $lastInsert_base_goods_id;
            $newGoods->price = $price;
            $newGoods->save();
            $newGoods->refresh();
        }
        return $lastInsert_cat_id;
    }
}