<?php

namespace api_web\classes;

use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use common\models\Order;
use common\models\OrderChat;
use common\models\Organization;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * Class ChatWebApi
 * @package api_web\classes
 */
class ChatWebApi extends WebApi
{
    /**
     * Список диалогов пользователя
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getDialogList(array $post)
    {
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $client = $this->user->organization;

        if ($client->type_id === Organization::TYPE_RESTAURANT) {
            $where = ['client_id' => $client->id];
        } else if ($client->type_id === Organization::TYPE_SUPPLIER) {
            $where = ['vendor_id' => $client->id];
        } else {
            throw new BadRequestHttpException('У вас нет доступа к диалогам');
        }

        $search = Order::find()->where($where);

        if (empty($search)) {
            throw new BadRequestHttpException("Нет диалогов");
        }

        if (isset($post['search'])) {
            if (isset($post['search']['recipient_id'])) {
                $search_field = 'vendor_id';
                if ($client->type_id === Organization::TYPE_SUPPLIER) {
                    $search_field = 'client_id';
                }
                $search->andWhere([$search_field => (int)$post['search']['recipient_id']]);
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $search->all()
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $this->prepareDialog($model);
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
     * Список сообщений в диалоге
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getDialogMessages(array $post)
    {

        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        if (empty($post['dialog_id'])) {
            throw new BadRequestHttpException("ERROR: Empty dialog_id");
        }

        $client = $this->user->organization;
        $order = Order::find()->where(['id' => $post['dialog_id']])
            ->andWhere(['or', ['client_id' => $client->id], ['vendor_id' => $client->id]])
            ->one();

        if (empty($order)) {
            throw new BadRequestHttpException("Нет такого диалога");
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $order->getOrderChat()->orderBy(['created_at' => SORT_DESC])->all()
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $this->prepareMessage($model);
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
     * Добавить сообщение в диалог
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function addMessage(array $post)
    {

        $client = $this->user->organization;
        $order = Order::find()->where(['id' => $post['dialog_id']])
            ->andWhere(['or', ['client_id' => $client->id], ['vendor_id' => $client->id]])
            ->one();

        if (empty($order)) {
            throw new BadRequestHttpException("Нет такого диалога");
        }

        if ($client->id == $order->client_id) {
            $recipient_id = $order->vendor_id;
        } else {
            $recipient_id = $order->client_id;
        }

        $dialogMessage = new OrderChat([
            'order_id' => $order->id,
            'sent_by_id' => $this->user->id,
            'recipient_id' => $recipient_id,
            'message' => \Yii::$app->db->quoteValue($post['message']),
            'is_system' => 0,
            'viewed' => 0,
            'danger' => 0
        ]);

        if (!$dialogMessage->validate()) {
            throw new ValidationException($dialogMessage->getFirstErrors());
        }

        if (!$dialogMessage->save()) {
            throw new ValidationException($dialogMessage->getFirstErrors());
        }

        return $this->getDialogMessages(['dialog_id' => $order->id]);
    }

    /**
     * Список получателей сообщений
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function getRecipientList(array $post)
    {
        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $client = $this->user->organization;

        if ($client->type_id === Organization::TYPE_RESTAURANT) {
            $where = ['order.client_id' => $client->id];
            $joinWith = 'order.vendor_id';
        } else if ($client->type_id === Organization::TYPE_SUPPLIER) {
            $where = ['order.vendor_id' => $client->id];
            $joinWith = 'order.client_id';
        } else {
            throw new BadRequestHttpException('У вас нет доступа к диалогам');
        }

        $query = new Query();
        $query->distinct();
        $query->from(Order::tableName());
        $query->select(['organization.id as recipient_id', 'organization.name as name']);
        $query->where($where);
        $query->innerJoin('organization', 'organization.id = ' . $joinWith);

        if (isset($post['search'])) {
            if (isset($post['search']['name'])) {
                $query->andWhere("organization.name LIKE :search", [':search' => '%' . $post['search']['name'] . '%']);
            }
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->all()
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = ['recipient_id' => (int)$model['recipient_id'], 'name' => $model['name']];
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
     * @param Order $model
     * @return array
     */
    private function prepareDialog(Order $model)
    {
        return [
            'dialog_id' => (int)$model->id,
            'client' => $model->client->name,
            'client_id' => (int)$model->client->id,
            'vendor' => $model->vendor->name,
            'vendor_id' => (int)$model->vendor->id,
            'count_message' => (int)$model->orderChatCount ?? 0,
            'unread_message' => (int)$model->orderChatUnreadCount ?? 0,
            'last_message' => $model->orderChatLastMessage->message ?? 'Нет сообщений',
            'last_message_date' => $model->orderChatLastMessage->created_at ?? null,
        ];
    }

    /**
     * @param OrderChat $model
     * @return array
     */
    private function prepareMessage(OrderChat $model)
    {

        $is_my_message = false;

        if (!$model->is_system) {
            if ($model->recipient->id != $this->user->organization_id) {
                $is_my_message = true;
            }
        }

        return [
            'message_id' => (int)$model->id,
            'message' => $model->message,
            'sender' => $model->is_system ? 'MixCart Bot' : $model->sentBy->profile->full_name,
            'recipient_name' => $model->recipient->name,
            'recipient_id' => (int)$model->recipient->id,
            'is_my_message' => $is_my_message,
            'is_system' => $model->is_system ? true : false,
            'viewed' => $model->viewed ? true : false,
            'date' => date('Y-m-d', strtotime($model->created_at)),
            'time' => date('H:i:s', strtotime($model->created_at)),
        ];
    }
}