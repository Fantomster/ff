<?php

namespace api_web\classes;

use api_web\helpers\WebApiHelper;
use common\models\CatalogTempContent;
use common\models\ManagerAssociate;
use common\models\RelationUserOrganization;
use Yii;
use api_web\exceptions\ValidationException;
use common\models\Profile;
use common\models\restaurant\RestaurantChecker;
use common\models\User;
use common\models\Role;
use common\models\Catalog;
use common\models\CatalogTemp;
use common\models\Organization;
use common\models\RelationSuppRest;
use yii\validators\NumberValidator;
use yii\web\BadRequestHttpException;
use api_web\helpers\Excel;

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
                throw new BadRequestHttpException('No such organization');
            }

            $relation = RelationSuppRest::findOne(['supp_org_id' => $vendorID, 'rest_org_id' => $this->user->organization->id]);
            if (empty($relation)) {
                $relation = $this->createRelation($this->user->organization->id, $organization->id);
            }

            if ($relation->invite != RelationSuppRest::INVITE_ON) {
                foreach ($organization->users as $recipient) {
                    $this->user->sendInviteToVendor($recipient);
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
                    'success' => true,
                    'organization_id' => $organization->id,
                    'message' => "Приглашение отправлено."
                ];
            } else {
                $result = [
                    'success' => true,
                    'organization_id' => $organization->id,
                    'message' => "Приглашение уже было отправлено."
                ];
            }
            return $result;
        } else {
            $organization = new Organization();
        }

        $email = $post['user']['email'];
        $fio = $post['user']['fio'];
        $org = $post['user']['organization_name'];
        $phone = $post['user']['phone'];

        $check = RestaurantChecker::checkEmail($email);

        if ($check['eventType'] == 7) {
            throw new BadRequestHttpException('Пользователь с емайлом:' . $email . ' найден у нас в системе, но он не завершил регистрацию. Как только он пройдет процедуру регистрации поставщика, вы сможете добавить его.');
        }

        if ($check['eventType'] != 5) {
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
                    if (!$vendorID) {
                        $organization->name = $org;
                    }

                    if (!empty($post['user']['inn']) && !$vendorID) {
                        $organization->inn = $post['user']['inn'];
                    }

                    if (!empty($post['user']['contact_name']) && !$vendorID) {
                        $organization->contact_name = $post['user']['contact_name'];
                    }

                    if (!$organization->validate()) {
                        throw new ValidationException($organization->getFirstErrors());
                    }
                    $organization->save();
                    $user->setOrganization($organization)->save();
                    $relId = $user->createRelationUserOrganization($user->organization->id, $user->role_id);
                    if (!ManagerAssociate::find()->where(['manager_id' => $user->id, 'organization_id' => $user->organization->id])->exists()) {
                        $managerAssociate = new ManagerAssociate();
                        $managerAssociate->manager_id = $user->id;
                        $managerAssociate->organization_id = $this->user->organization->id;
                        $managerAssociate->save();
                    }
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

                if (count($arrCatalog)) {
                    $lastInsert_cat_id = Catalog::addBaseCatalog($check, $get_supp_org_id, $currentUser, $arrCatalog, $currency);
                } else {
                    $lastInsert_cat_id = 0;
                }
                /**
                 *
                 * 5) Связь ресторана и поставщика
                 *
                 * */
                $relation = $this->createRelation($currentUser->organization_id, $get_supp_org_id, $lastInsert_cat_id);
                /**
                 *
                 * Отправка почты
                 *
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
                    'success' => true,
                    'organization_id' => $organization->id,
                    'user_id' => $user->id
                ];

                if ($check['eventType'] == 5) {
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
    }

    /**
     * @param $client_id
     * @param $vendor_id
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
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function search(array $post)
    {
        if (empty($post['email'])) {
            throw new BadRequestHttpException('empty_param|search attribute email');
        }

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
                    'user.email' => $email
                ])->one()) {
                    $r['user'] = [
                        'email' => $user->user->email,
                        'name' => $user->user->profile->full_name,
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
     * @param array $post
     * @return mixed
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function update(array $post)
    {
        if (empty($post['id'])) {
            throw new BadRequestHttpException('empty_param|id');
        }
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
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function uploadLogo(array $post)
    {
        if (empty($post['vendor_id'])) {
            throw new BadRequestHttpException('empty_param|vendor_id');
        }

        $vendor = Organization::findOne($post['vendor_id']);
        if (empty($vendor)) {
            throw new BadRequestHttpException('vendor_not_found');
        }

        if (empty($post['image_source'])) {
            throw new BadRequestHttpException('empty_param|image_source');
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

    /**
     * Загрузка индивид. каталога
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\Exception
     */
    public function uploadFile(array $request)
    {
        if (empty($request['vendor_id'])) {
            throw new BadRequestHttpException('empty_param|vendor_id');
        }
        $vendorID = $request['vendor_id'];

        if (empty($request['data'])) {
            throw new BadRequestHttpException('empty_param|data');
        }

        $catalog = $this->container->get('CatalogWebApi')->getPersonalCatalog($vendorID, $this->user->organization, true);
        if (empty($catalog)) {
            throw new BadRequestHttpException('Catalog not found');
        }
        $catalogID = $catalog->id;

        //проверка нет ли уже загруженного временного каталога
        //если есть - удаляем
        $tempCatalog = CatalogTemp::findOne(['cat_id' => $catalogID, 'user_id' => $this->user->id]);
        if (!empty($tempCatalog)) {
            Yii::$app->get('resourceManager')->delete(Excel::excelTempFolder . DIRECTORY_SEPARATOR . $tempCatalog->excel_file);
            CatalogTempContent::deleteAll(['temp_id' => $tempCatalog->id]);
            $tempCatalog->delete();
        }
        //сохранение и загрузка на s3
        $base64 = $request['data'];
        $type = 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,';
        if (strpos($base64, $type) !== false) {
            try {
                $file = \api_web\helpers\File::getFromBase64($base64, $type, "xlsx");
                Yii::$app->get('resourceManager')->save($file, Excel::excelTempFolder . DIRECTORY_SEPARATOR . $file->name);
                $newTempCatalog = new CatalogTemp();
                $newTempCatalog->cat_id = $catalogID;
                $newTempCatalog->user_id = $this->user->id;
                $newTempCatalog->excel_file = $file->name;
                $newTempCatalog->save();
                return [
                    'result' => true,
                    'temp_id' => $newTempCatalog->id,
                    'rows' => Excel::get20Rows($file->tempName)
                ];
            } catch (\yii\base\Exception $e) {
                throw $e;
            }
        } else {
            throw new BadRequestHttpException("The download format is different from XLSX");
        }
    }

    /**
     * Валидация и импорт уже загруженного файла инд. каталога
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function prepareTemporary(array $request)
    {
        if (empty($request['vendor_id'])) {
            throw new BadRequestHttpException('empty_param|vendor_id');
        }
        $catalog = $this->container->get('CatalogWebApi')->getPersonalCatalog($request['vendor_id'], $this->user->organization, true);
        if (!$catalog) {
            throw new BadRequestHttpException("Catalog not found");
        }
        $tempCatalog = CatalogTemp::findOne(['cat_id' => $catalog->id, 'user_id' => $this->user->id]);
        if (empty($tempCatalog)) {
            throw new BadRequestHttpException("Temp catalog not found");
        }
        $index = $request['index_field'] ?? $tempCatalog->cat->main_index ?? null;
        if (empty($index)) {
            throw new BadRequestHttpException('empty_param|index_field');
        }

        if (empty($request['mapping']) && empty($tempCatalog->cat->mapping)) {
            throw new BadRequestHttpException('empty_param|mapping');
        }

        if (!CatalogTempContent::find()->where(['temp_id' => $tempCatalog->id])->exists()){
            $request['mapping'] = isset($request['mapping']) ? array_flip($request['mapping']) : null;
            $mapping = $request['mapping'] ?? $tempCatalog->cat->mapping;
            if (is_string($mapping)) {
                $mapping = \json_decode($mapping);
            }

            $excelUrl = Yii::$app->get('resourceManager')->getUrl(Excel::excelTempFolder . DIRECTORY_SEPARATOR . $tempCatalog->excel_file);
            $file = \api_web\helpers\File::getFromUrl($excelUrl);

            if (Excel::writeToTempTable($file->tempName, $tempCatalog->id, $mapping, $index)) {
                $tempCatalog->index_column = $index;
                $tempCatalog->cat->main_index = $tempCatalog->index_column;
                $tempCatalog->mapping = \json_encode($mapping);
                $tempCatalog->cat->mapping = $tempCatalog->mapping;
                $tempCatalog->cat->save();
                $tempCatalog->save();
            }
        }
        $dubles = $this->container->get('CatalogWebApi')->getTempDuplicatePosition($request);
        if ($dubles){
            return ['duplicates' => $dubles];
        }

        return ['products' => $this->container->get('CatalogWebApi')->getGoodsInTempCatalog($request)];
    }

    /**
     * Загрузка индивидуального каталога
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function uploadCustomCatalog(array $request)
    {
        //
    }

    /**
     * Валидация и импорт уже загруженного основного каталога
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function importCustomCatalog(array $request)
    {
        //
    }

    /**
     * Удаление основного каталога
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function deleteMainCatalog(array $request)
    {
        if (empty($request['cat_id'])) {
            throw new BadRequestHttpException('empty_param|cat_id');
        }

        $catalog = Catalog::findOne(['id' => $request['cat_id'], 'supp_org_id' => $this->user->organization_id, 'type' => Catalog::BASE_CATALOG]);
        if (empty($catalog)) {
            throw new BadRequestHttpException('Catalog not found');
        }
        return $this->container->get('CatalogWebApi')->deleteMainCatalog($catalog);
    }

    /**
     * Смена уникального индекса главного каталога
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function changeMainIndex(array $request)
    {
        if (empty($request['cat_id'])) {
            throw new BadRequestHttpException('empty_param|cat_id');
        }

        $catalog = Catalog::findOne(['id' => $request['cat_id'], 'supp_org_id' => $this->user->organization_id, 'type' => Catalog::BASE_CATALOG]);
        if (empty($catalog)) {
            throw new BadRequestHttpException('Catalog not found');
        }
        return $this->container->get('CatalogWebApi')->changeMainIndex($catalog, $request['index']);
    }

    /**
     * Удаление загруженного необработанного каталога
     * @param array $request
     * @return array
     * @throws \Exception
     */
    public function cancelTemporary(array $request)
    {
        if (empty($request['vendor_id'])) {
            throw new BadRequestHttpException('empty_param|vendor_id');
        }
        $catalog = $this->container->get('CatalogWebApi')->getPersonalCatalog($request['vendor_id'], $this->user->organization, true);

        $tempCatalog = CatalogTemp::findOne(['cat_id' => $catalog->id, 'user_id' => $this->user->id]);
        if (!empty($tempCatalog)) {
            Yii::$app->get('resourceManager')->delete(Excel::excelTempFolder . DIRECTORY_SEPARATOR . $tempCatalog->excel_file);
            CatalogTempContent::deleteAll(['temp_id' => $tempCatalog->id]);
            $tempCatalog->delete();
        }
        return ['result' => true];
    }

    /**
     * Список ключей для выбора
     * @return array
     */
    public function getListMainIndex()
    {
        return $this->container->get('CatalogWebApi')->getKeys();
    }

    /**
     * Статус загруженного, но не импортированного каталога
     * @param array $request
     * @return array
     */
    public function getTempMainCatalog(array $request)
    {
        $tempCatalog = CatalogTemp::findOne(['cat_id' => $request['cat_id'], 'user_id' => $this->user->id]);
        if (!empty($tempCatalog)) {
            return [
                'exists' => true,
                'rows' => Excel::get20RowsFromTempUploaded($tempCatalog),
                'mapping' => $tempCatalog->mapping,
            ];
        } else {
            return ['exists' => false];
        }
    }

}
