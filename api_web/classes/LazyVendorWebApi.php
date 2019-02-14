<?php
/**
 * Date: 06.02.2019
 * Author: Mike N.
 * Time: 12:16
 */

namespace api_web\classes;

use common\models\{
    Catalog,
    CatalogBaseGoods,
    Delivery,
    Organization,
    OrganizationContact,
    OrganizationContactNotification,
    RelationSuppRest,
    RelationUserOrganization,
    User
};
use api_web\components\{Registry, WebApi};
use api_web\exceptions\ValidationException;
use api_web\helpers\WebApiHelper;
use function PHPSTORM_META\type;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class LazyVendorWebApi
 *
 * @package api_web\classes
 */
class LazyVendorWebApi extends WebApi
{
    /**
     * @var array
     */
    private $arAvailableFields = [
        'name'
    ];

    /**
     * Создание поставщика (Ленивого)
     *
     * @param $post
     * @return mixed
     * @throws \Exception
     */
    public function create($post)
    {
        $this->validateRequest($post, ['lazy-vendor']);
        $request = $post['lazy-vendor'];
        $this->validateRequest($request, ['name', 'address', 'email', 'phone', 'contact_name', 'inn', 'additional_params']);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $addParams = $request['additional_params'];
            $exists = Organization::find()->where([
                'name'    => $request['name'],
                'inn'     => $request['inn'],
                'type_id' => Organization::TYPE_LAZY_VENDOR
            ])->exists();
            if ($exists) {
                throw new BadRequestHttpException('vendor.exists');
            }
            /**
             * Создаем организацию
             */
            $model = new Organization();
            $model->name = $request['name'];
            $model->address = $request['address'];
            $model->email = $request['email'];
            $model->phone = $request['phone'];
            $model->contact_name = $request['contact_name'];
            $model->inn = $request['inn'];
            $model->type_id = Organization::TYPE_LAZY_VENDOR;
            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
            /**
             * Создаем каталог
             */
            $catalog = $this->createCatalog($model->id);
            /**
             * Создаем связь с каталогом
             */
            $this->createRelation($model->id, $catalog->id, $addParams['discount_product'] ?? 0);
            /**
             * Создаем запись в доставку поставщика
             */
            $delivery = Delivery::findOne(['vendor_id' => $model->id]);
            if (empty($delivery)) {
                $delivery = new Delivery();
                $delivery->vendor_id = $model->id;
                $delivery->delivery_charge = $addParams['delivery_price'] ?? 0;
                $delivery->delivery_discount_percent = $addParams['delivery_discount_percent'] ?? 0;
                $delivery->min_order_price = $addParams['min_order_price'] ?? 0;
                if (!empty($addParams['delivery_days'])) {
                    foreach ($addParams['delivery_days'] as $key => $value) {
                        $delivery->setAttribute($key, (int)$value);
                    }
                }
                if (!$delivery->save()) {
                    throw new ValidationException($delivery->getFirstErrors());
                }
            }
            $transaction->commit();
            return WebApiHelper::prepareOrganization($model);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Список ленивых поставщиков
     *
     * @param $request
     * @return array
     */
    public function list($request)
    {
        $page = $request['pagination']['page'] ?? 1;
        $pageSize = $request['pagination']['page_size'] ?? 12;
        $sort = $request['sort'] ?? 'name';
        $result = [];

        $tableName = Organization::tableName();
        $tableNameRelation = RelationSuppRest::tableName();
        $tableNameCBG = CatalogBaseGoods::tableName();

        $countQueryAll = (new Query())
            ->select('COUNT(*)')
            ->from($tableNameCBG)
            ->where("$tableNameCBG.cat_id = $tableNameRelation.cat_id");

        $countQueryAllow = (new Query())
            ->select('COUNT(*)')
            ->from($tableNameCBG)
            ->where("$tableNameCBG.cat_id = $tableNameRelation.cat_id AND $tableNameCBG.status = :s", [
                ":s" => CatalogBaseGoods::STATUS_ON
            ]);

        $query = (new Query())
            ->select($tableName . '.*')
            ->addSelect([
                $tableNameRelation . '.cat_id',
                'product_count'       => $countQueryAll,
                'product_count_allow' => $countQueryAllow
            ])
            ->from($tableName)
            ->innerJoin($tableNameRelation, "$tableNameRelation.supp_org_id = $tableName.id")
            ->where([
                $tableName . '.type_id'             => Organization::TYPE_LAZY_VENDOR,
                $tableNameRelation . '.rest_org_id' => $this->user->organization_id
            ]);

        if (isset($request['search'])) {
            //Поисковая строка
            if (!empty($request['search']['query'])) {
                $query->andFilterWhere(['like', "{$tableName}.name", $request['search']['query']]);
            }
            //Поиск по адресу
            if (!empty($request['search']['address'])) {
                $query->andFilterWhere(['like', "{$tableName}.address", $request['search']['address']]);
            }
        }

        if ($query->count()) {
            if ($sort && in_array(ltrim($sort, '-'), $this->arAvailableFields)) {
                $sortDirection = SORT_ASC;
                if (strpos($sort, '-') !== false) {
                    $sortDirection = SORT_DESC;
                }
                $query->orderBy([ltrim($sort, '-') => $sortDirection]);
            }

            $dataProvider = new ArrayDataProvider([
                'allModels' => $query->all()
            ]);

            $pagination = new Pagination();
            $pagination->setPage($page - 1);
            $pagination->setPageSize($pageSize);
            $dataProvider->setPagination($pagination);
            /** @var Organization $model */
            if (!empty($dataProvider->models)) {
                foreach (WebApiHelper::generator($dataProvider->models) as $model) {
                    $result[] = $this->prepareModel($model);
                }
            }
            $page = ($dataProvider->pagination->page + 1);
            $pageSize = $dataProvider->pagination->pageSize;
            $totalPage = ceil($dataProvider->totalCount / $pageSize);
        }

        $return = [
            'items'      => $result,
            'pagination' => [
                'page'       => $page,
                'page_size'  => $pageSize,
                'total_page' => $totalPage ?? 0
            ],
            'sort'       => $sort
        ];

        return $return;
    }

    /**
     * Список контактов ленивого поставщика
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function contactList($request)
    {
        $this->validateRequest($request, ['id']);

        $page = $request['pagination']['page'] ?? 1;
        $pageSize = $request['pagination']['page_size'] ?? 12;

        $model = $this->getVendor((int)$request['id']);

        $notificationFields = array_keys((new OrganizationContactNotification())->getRulesAttributes());
        $fields = ArrayHelper::merge(['oc.id', 'oc.type_id', 'oc.contact'], $notificationFields);

        $contacts = (new Query())
            ->select($fields)
            ->from(OrganizationContact::tableName() . " as oc")
            ->leftJoin(OrganizationContactNotification::tableName() . " as ocn", "oc.id = ocn.organization_contact_id")
            ->where(['oc.organization_id' => $model->id])
            ->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $this->prepareContactRows($contacts, $notificationFields)
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $return = [
            'items'      => $dataProvider->models ?? [],
            'pagination' => [
                'page'       => $page,
                'page_size'  => $pageSize,
                'total_page' => $totalPage ?? 0
            ]
        ];

        return $return;
    }

    /**
     * Отправка тестового сообщения
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function contactSendTestMessage($request)
    {
        $this->validateRequest($request, ['id']);
        $model = OrganizationContactNotification::findOne([
            'client_id'               => $this->user->organization_id,
            'organization_contact_id' => $request['id']
        ]);

        if (empty($model)) {
            throw new BadRequestHttpException('model_not_found');
        }

        return ['result' => $model->organizationContact->sendTestMessage()];
    }

    /**
     * Получить тип контакта по значению
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function contactCheckType($request)
    {
        $this->validateRequest($request, ['contact']);
        $type = OrganizationContact::checkType($request['contact']);
        return ['type' => $type];
    }

    /**
     * Создание контакта поставщика
     *
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function contactCreate($request)
    {
        $this->validateRequest($request, ['vendor_id', 'contact', 'type_id']);
        //проверим тип который прилетел
        if (!isset(OrganizationContact::TYPE_CLASS[$request['type_id']])) {
            throw new BadRequestHttpException('lazy_vendor.type_not_found');
        }
        //Поиск вендора ленивого
        $model = $this->getVendor((int)$request['vendor_id']);
        //Проверяем нет ли уже такого контакта
        $exists = $model->getOrganizationContact()->andWhere(['contact' => $request['contact']])->exists();
        if ($exists) {
            throw new BadRequestHttpException('lazy_vendor.contact_exists');
        }
        //Проверяем совпадение типов с фронта и с нашим
        $type = OrganizationContact::checkType($request['contact']);
        if ($type !== (int)$request['type_id']) {
            throw new BadRequestHttpException('lazy_vendor.types_do_not_match');
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //Создаем контакт
            $contactModel = new OrganizationContact([
                'contact'         => $request['contact'],
                'type_id'         => (int)$type,
                'organization_id' => $model->id
            ]);
            if (!$contactModel->save()) {
                throw new ValidationException($contactModel->getFirstErrors());
            }
            //Создаем уведомления для текущего ресторана
            $contactModel->setNotifications($this->user->organization_id);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        //Возвращаем список контактов
        return $this->contactList(['id' => $model->id]);
    }

    /**
     * Поиск ленивого поставщика по email
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function search($post)
    {
        $this->validateRequest($post, ['email']);

        $result = [];
        $email = $post['email'];

        $models = Organization::find()
            ->joinWith(['relationUserOrganization', 'relationUserOrganization.user'])
            ->where(['organization.type_id' => Organization::TYPE_LAZY_VENDOR])
            ->andWhere(['or', [
                'organization.email' => $email
            ], [
                'user.email' => $email
            ]])->all();

        if (!empty($models)) {
            /**
             * @var Organization $model
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
        } else {
            $obOrg = Organization::findOne([
                'email'   => $email,
                'type_id' => [
                    Organization::TYPE_RESTAURANT,
                    Organization::TYPE_SUPPLIER
                ]]);
            $obUser = User::findOne(['email' => $email]);
            if ($obOrg || $obUser) {
                throw new BadRequestHttpException('lazy_vendor.rest_or_common_supplier');
            }
        }

        return $result;
    }

    /**
     * @param $model
     * @return array
     */
    private function prepareModel($model)
    {
        return [
            "id"            => (int)$model['id'],
            "name"          => $model['name'],
            "address"       => $model['address'],
            "contact_count" => 0,
            "product_count" => [
                "all"   => (int)$model['product_count'],
                "allow" => (int)$model['product_count_allow']
            ],
            "cat_id"        => (int)$model['cat_id']
        ];
    }

    /**
     * @param $rows
     * @param $notificationFields
     * @return array
     */
    private function prepareContactRows($rows, $notificationFields)
    {
        $array_map = [];
        foreach ($rows as $key => $contact) {
            $contact['id'] = (int)$contact['id'];
            $contact['type_id'] = (int)$contact['type_id'];
            foreach ($notificationFields as $field) {
                $contact[$field] = (int)$contact[$field];
            }
            $array_map[$key] = $contact;
        }
        return $array_map;
    }

    /**
     * Создание пустого каталога для ленивого поставщика
     *
     * @param $vendor_id
     * @return Catalog
     * @throws ValidationException
     */
    private function createCatalog($vendor_id)
    {
        $name = trim($this->user->organization->name) . '_LC';
        $catalog = Catalog::findOne(['supp_org_id' => $vendor_id, 'name' => $name]);
        if (!empty($catalog)) {
            return $catalog;
        }
        $model = new Catalog();
        $model->supp_org_id = $vendor_id;
        $model->currency_id = Registry::DEFAULT_CURRENCY_ID;
        $model->name = $name;
        $model->status = Catalog::STATUS_ON;
        $model->type = Catalog::CATALOG;
        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }
        return $model;
    }

    /**
     * Создание связи ресторана с поставщиком
     *
     * @param      $supp_org_id
     * @param null $cat_id
     * @param int  $discount_products
     * @return RelationSuppRest
     * @throws ValidationException
     */
    private function createRelation($supp_org_id, $cat_id = null, $discount_products = 0)
    {
        $rest_org_id = $this->user->organization->id;
        $relation = RelationSuppRest::findOne(['supp_org_id' => $supp_org_id, 'rest_org_id' => $rest_org_id]);
        if (empty($relation)) {
            $relation = new RelationSuppRest();
            $relation->rest_org_id = $rest_org_id;
            $relation->supp_org_id = $supp_org_id;
            $relation->discount_product = $discount_products;
            $relation->invite = RelationSuppRest::INVITE_OFF;
            $relation->cat_id = $cat_id;
            if (!$relation->save()) {
                throw new ValidationException($relation->getFirstErrors());
            }
        }
        return $relation;
    }

    /**
     * Работаю ли с этим поставщиком
     *
     * @param $vendor_id
     * @return bool
     */
    private function isMyVendor($vendor_id)
    {
        return (bool)RelationSuppRest::find()->where([
            'supp_org_id' => $vendor_id,
            'rest_org_id' => $this->user->organization->id
        ])->exists();
    }

    /**
     * @param $vendor_id
     * @return Organization|null
     * @throws BadRequestHttpException
     */
    private function getVendor($vendor_id)
    {
        $model = Organization::findOne(['id' => $vendor_id, 'type_id' => Organization::TYPE_LAZY_VENDOR]);
        if (empty($model)) {
            throw new BadRequestHttpException('lazy_vendor.not_found');
        }

        if ($this->isMyVendor($model->id) === false) {
            throw new BadRequestHttpException('lazy_vendor.not_is_my_vendor');
        }
        return $model;
    }

    /**
     * Проверка входа notificationUpdate
     *
     * @param $notifications
     * @return array
     * @throws BadRequestHttpException
     */
    private function validateNotifications($notifications)
    {
        $vals = [0, 1];
        $newNotifications = [];
        $notificationIds = [];
        $attributeRules = (new OrganizationContactNotification())->getRulesAttributes();
        foreach ($notifications as $notification) {
            if (!isset($notification['id'])) {
                throw new BadRequestHttpException('lazy_vendor.no_required_param');
            } elseif (!is_int($notification['id'])) {
                throw new BadRequestHttpException('lazy_vendor.wrong_value');
            }
            $notificationIds[$notification['id']] = $notification['id'];
            foreach ($attributeRules as $index => $attributeRule) {
                if (!isset($notification[$index])) {
                    throw new BadRequestHttpException('lazy_vendor.no_required_param');
                } elseif (!is_int($notification[$index]) || !in_array($notification[$index], $vals)) {
                    throw new BadRequestHttpException('lazy_vendor.wrong_value');
                }
                $newNotifications[$notification['id']][$index] = $notification[$index];
            }
        }
        if (count($notificationIds) !== count($notifications)) {
            throw new BadRequestHttpException('id не должны повторяться.');
        }
        return [
            'notifications'   => $newNotifications,
            'notificationIds' => $notificationIds,
        ];
    }

    /**
     * Обновление контактов ленивого поставщика
     *
     * @param $post
     * @return array|\yii\db\ActiveRecord[]
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function contactUpdate($post)
    {
        $this->validateRequest($post, ['vendor_id', 'notifications']);

        if (!is_array($post['notifications'])) {
            throw new BadRequestHttpException('lazy_vendor.wrong_value');
        }

        $result = $this->validateNotifications($post['notifications']);

        $vendor = $this->getVendor($post['vendor_id']);
        $n = $vendor->getOrganizationContact()->andWhere([
            'id' => $result['notificationIds']
        ])->all();

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            /** @var OrganizationContact $nModel */
            foreach ($n as $nModel) {
                if (in_array($nModel->id, $result['notificationIds'])) {
                    $nModel->setNotifications($this->user->organization_id, $result['notifications'][$nModel->id]);
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->contactList(['id' => $vendor->id]);
    }
}
