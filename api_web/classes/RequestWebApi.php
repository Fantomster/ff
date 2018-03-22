<?php

namespace api_web\classes;

use yii\db\Expression;
use yii\data\Pagination;
use common\models\Request;
use yii\helpers\ArrayHelper;
use api_web\components\Notice;
use api_web\components\WebApi;
use common\models\Organization;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use common\models\RequestCallback;
use yii\web\BadRequestHttpException;
use api_web\exceptions\ValidationException;

class RequestWebApi extends WebApi
{
    /**
     * Список заявок для ресторана
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getListClient(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Раздел доступен только для ресторанов...');
        }

        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $query = Request::find();
        $query->where(['rest_org_id' => $this->user->organization->id]);

        if (isset($post['search'])) {
            /**
             * Фильтр по статусу
             */
            if (isset($post['search']['status'])) {
                $query->andWhere(['active_status' => (int)$post['search']['status']]);
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->orderBy(['created_at' => SORT_DESC])->all()
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $models = $dataProvider->models;

        $result = [];
        foreach ($models as $model) {
            $result[] = $this->prepareRequest($model);
        }

        $return = [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Список заявок для поставщика
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getListVendor(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_SUPPLIER) {
            throw new BadRequestHttpException('Раздел доступен только для поставщиков...');
        }

        $organization = $this->user->organization;
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $query = Request::find()->joinWith('client')->orderBy('id DESC');
        //Массив в доставками
        $deliveryRegions = $organization->deliveryRegionAsArray;
        //Доступные для доставки регионы
        if (!empty($deliveryRegions['allow'])) {
            foreach ($deliveryRegions['allow'] as $row) {
                if (!empty($row['administrative_area_level_1']) && !empty($row['locality'])) {
                    $p = $row['administrative_area_level_1'] . $row['locality'];
                    $query->orWhere('CONCAT(`administrative_area_level_1`, `locality`) = :p', [':p' => $p]);
                } elseif ((empty($row['administrative_area_level_1']) || $row['administrative_area_level_1'] == 'undefined') && !empty($row['locality'])) {
                    $query->orWhere(['=', 'locality', $row['locality']]);
                } elseif (!empty($row['administrative_area_level_1']) && empty($row['locality'])) {
                    $query->orWhere(['=', 'administrative_area_level_1', $row['administrative_area_level_1']]);
                }
            }
        }
        //Условия для исключения доставки с регионов
        if (!empty($deliveryRegions['exclude'])) {
            if (!empty($deliveryRegions['exclude'])) {
                foreach ($deliveryRegions['exclude'] as $row) {
                    if (!empty($row['administrative_area_level_1']) && !empty($row['locality'])) {
                        $p = $row['administrative_area_level_1'] . $row['locality'];
                        $query->andWhere('CONCAT(`administrative_area_level_1`, `locality`) <> :s', [':s' => $p]);
                    } elseif ((empty($row['administrative_area_level_1']) || $row['administrative_area_level_1'] == 'undefined') && !empty($row['locality'])) {
                        $query->andWhere(['!=', 'locality', $row['locality']]);
                    } elseif (!empty($row['administrative_area_level_1']) && empty($row['locality'])) {
                        $query->andWhere(['!=', 'administrative_area_level_1', $row['administrative_area_level_1']]);
                    }
                }
            }
        }

        $query->andWhere(['>=', 'end', new \yii\db\Expression('NOW()')])->andWhere(['active_status' => Request::ACTIVE]);

        /**
         * только мои заявки, на которые откликнулся
         */
        if (isset($post['my_only']) && $post['my_only'] == true) {
            $query->andWhere(['responsible_supp_org_id' => (int)$organization->id]);
        }

        if (isset($post['search'])) {
            /**
             * Фильтр по Категории
             */
            if (isset($post['search']['category'])) {
                $query->andWhere(['category' => (int)$post['search']['category']]);
            }
            /**
             * поиск по продукту
             */
            if (isset($post['search']['product'])) {
                $query->andWhere(['like', 'product', $post['search']['product']]);
            }
            /**
             * только срочные заявки
             */
            if (isset($post['search']['urgent']) && $post['search']['urgent'] == true) {
                $query->andWhere(['rush_order' => 1]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $this->prepareRequest($model);
        }

        $return = [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Список категорий
     * @return array
     */
    public function getCategoryList()
    {
        $result = [];
        $category = ArrayHelper::map(\common\models\MpCategory::find()->where(['parent' => null])->orderBy('name')->all(), 'id', 'name');

        if (!empty($category)) {
            foreach ($category as $key => $item) {
                $result[] = ['id' => $key, 'name' => \Yii::t('app', $item)];
            }
        }

        return $result;
    }

    /**
     * Список откликов на заявку
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getCallbackList(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Вы не можете смотреть предложения, могут только рестораны...');
        }

        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('Empty request_id');
        }

        $model = Request::find()
            ->where(['id' => (int)$post['request_id'], 'rest_org_id' => $this->user->organization->id])
            ->one();
        if (empty($model)) {
            throw new BadRequestHttpException('Not found request');
        }

        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $model->requestCallbacks
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $this->getCallback($model->id);
        }

        $return = [
            'result' => $result,
            'pagination' => [
                'page' => ($dataProvider->pagination->page + 1),
                'page_size' => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Карточка заявки
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getRequest(array $post)
    {
        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('Empty request_id');
        }

        $model = Request::find()->where(['id' => (int)$post['request_id']])->one();
        if (empty($model)) {
            throw new BadRequestHttpException('Not found request');
        }

        return $this->prepareRequest($model);
    }

    /**
     * Создание заявки
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function create(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_RESTAURANT) {
            throw new BadRequestHttpException('Вы не можете создавать заявки, могут только рестораны...');
        }

        if (empty($post['category_id'])) {
            throw new BadRequestHttpException('Empty category_id');
        }

        if (empty($post['product'])) {
            throw new BadRequestHttpException('Empty product');
        }

        if (empty($post['amount'])) {
            throw new BadRequestHttpException('Empty amount');
        }

        $issetCategory = false;
        $category = $this->getCategoryList();
        foreach ($category as $item) {
            if ($item['id'] === $post['category_id']) {
                $issetCategory = true;
            }
        }

        if ($issetCategory === false) {
            throw new BadRequestHttpException('Такой категории не существует');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $model = new Request();
            $model->category = (int)$post['category_id'];
            $model->product = (string)$post['product'];
            $model->amount = (string)$post['amount'];
            $model->regular = (isset($post['regular']) ? (string)$post['regular'] : "1");
            $model->payment_method = (int)$post['payment_type'] ?? 1;
            $model->rush_order = (int)$post['urgent'] ?? 0;
            $model->comment = (string)$post['comment'] ?? '';
            $model->deferment_payment = (string)$post['deferment_payment'] ?? '';
            $model->rest_org_id = $this->user->organization->id;
            $model->rest_user_id = $this->user->id;
            $model->active_status = 1;

            if (!$model->validate()) {
                throw new ValidationException($model->getFirstErrors());
            }

            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
            $transaction->commit();
            return $this->prepareRequest($model);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Снять заявку
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function close(array $post)
    {
        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('Empty request_id');
        }

        $model = Request::find()
            ->where(['id' => (int)$post['request_id'], 'rest_org_id' => $this->user->organization->id])
            ->one();
        if (empty($model)) {
            throw new BadRequestHttpException('Not found request');
        }

        $model->active_status = Request::INACTIVE;
        $model->save();
        return $this->prepareRequest($model);
    }

    /**
     * Добавить предложение
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function addCallback(array $post)
    {
        if ($this->user->organization->type_id !== Organization::TYPE_SUPPLIER) {
            throw new BadRequestHttpException('Вы не можете отправить предложение, доступно только поставщикам...');
        }

        if (empty($post['request_id'])) {
            throw new BadRequestHttpException('Empty request_id');
        }

        if (empty($post['price'])) {
            throw new BadRequestHttpException('Empty price');
        }

        $request = Request::findOne((int)$post['request_id']);
        if (empty($request)) {
            throw new BadRequestHttpException('Not found request');
        }

        if ($request->active_status == 0) {
            throw new BadRequestHttpException('Request not active');
        }

        $model = RequestCallback::find()->where(['request_id' => $request->id])
            ->andWhere(['supp_org_id' => $this->user->organization->id]);

        if ($model->exists()) {
            throw new BadRequestHttpException('Вы уже оставили отклик');
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $model = new RequestCallback();
            $model->request_id = $request->id;
            $model->supp_org_id = $this->user->organization->id;
            $model->supp_user_id = $this->user->id;
            $model->price = $post['price'];
            $model->comment = $post['comment'] ?? '';
            $model->created_at = new Expression('NOW()');

            if ($model->validate() && $model->save()) {
                //Отправляем уведомления
                Notice::init('Request')->addCallback($request, $this->user);
                $transaction->commit();
                $model->refresh();
                return $this->prepareRequestCallback($model);
            } else {
                throw new ValidationException($model->getFirstErrors());
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     */
    private function getCallback($id)
    {
        $model = RequestCallback::findOne($id);
        if (empty($model)) {
            throw new BadRequestHttpException('Not found RequestCallback::id = ' . $id);
        }

        return $this->prepareRequestCallback($model);
    }

    /**
     * Информация по заявке
     * @param Request $model
     * @return array
     */
    private function prepareRequest(Request $model)
    {
        return [
            'id' => (int)$model->id,
            "name" => $model->product,
            "status" => (int)$model->active_status,
            "created_at" => date('d.m.Y H:i', strtotime($model->created_at)),
            "category" => $model->categoryName->name,
            "category_id" => $model->category,
            "amount" => $model->amount,
            "comment" => $model->comment,
            "client" => $this->container->get('MarketWebApi')->prepareOrganization($model->client),
            "vendor" => $this->container->get('MarketWebApi')->prepareOrganization($model->vendor) ?? null,
            "hits" => (int)$model->count_views ?? 0,
            "count_callback" => (int)$model->countCallback ?? 0,
            "urgent" => $model->rush_order ?? 0,
            "payment_method" => $model->payment_method,
            "deferment_payment" => $model->deferment_payment,
            "regular" => $model->regular,
            "regular_name" => $model->regularName
        ];
    }

    /**
     * Информация о предложении
     * @param RequestCallback $model
     * @return array
     */
    private function prepareRequestCallback(RequestCallback $model)
    {
        return [
            'id' => (int)$model->id,
            "request_id" => $model->request_id,
            "client" => $this->container->get('MarketWebApi')->prepareOrganization($model->request->client),
            "vendor" => $this->container->get('MarketWebApi')->prepareOrganization($model->organization),
            "price" => round($model->price, 2),
            "comment" => $model->comment,
            "created_at" => $model->created_at,
            "updated_at" => $model->updated_at,
        ];
    }
}