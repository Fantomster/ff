<?php

namespace api_web\classes;

use api_web\components\Notice;
use api_web\components\WebApi;
use api_web\exceptions\ValidationException;
use api_web\helpers\WebApiHelper;
use common\models\Order;
use common\models\OrderChat;
use common\models\Organization;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;

/**
 * @property int $updated_user_id       [int(11)]  Идентификатор пользователя, совершившего последние изменения записи
 *           в таблице
 * @property int $edi_shipment_quantity [int(11)]  Отгруженное количество товара EDI
 * Class ChatWebApi
 * @package api_web\classes
 */
class ChatWebApi extends WebApi
{
    /**
     * Список диалогов пользователя
     *
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
            $where = ['o.client_id' => $client->id];
        } elseif ($client->type_id === Organization::TYPE_SUPPLIER) {
            $where = ['o.vendor_id' => $client->id];
        } else {
            throw new BadRequestHttpException('chat.access_denied');
        }

        $lastMessageDateQuery = (new Query())->select(['COALESCE(MAX(order_chat.created_at), o.created_at)'])
            ->from(OrderChat::tableName())->where(['order_id' => new Expression('o.id')]);
        $unreadMessageQuery = (new Query())->from(OrderChat::tableName())->select('COUNT(*)')
            ->where(['order_id' => new Expression('o.id'), 'recipient_id' => $client->id, 'viewed' => 0]);
        $countMessageQuery = (new Query())->from(OrderChat::tableName())->select('COUNT(*)')
            ->where(['order_id' => new Expression('o.id'), 'recipient_id' => $client->id]);
        $lastMessageQuery = (new Query())->select('message')->from(OrderChat::tableName())
            ->where(['order_id' => new Expression('o.id')])->orderBy('order_chat.created_at DESC')->limit(1);

        $search = Order::find()->alias('o')
            ->with(['vendor', 'client'])
            ->select([
                'o.*',
                'last_message_date' => $lastMessageDateQuery,
                'unread_message'    => $unreadMessageQuery,
                'count_message'     => $countMessageQuery,
                'last_message'      => $lastMessageQuery,
            ])->where($where);

        if (empty($search)) {
            throw new BadRequestHttpException("chat.dialogs_not_found");
        }

        if (isset($post['search'])) {
            if (isset($post['search']['recipient_id'])) {
                $search_field = 'vendor_id';
                if ($client->type_id === Organization::TYPE_SUPPLIER) {
                    $search_field = 'client_id';
                }
                $search->andWhere([$search_field => (int)$post['search']['recipient_id']]);
            }
            if (isset($post['search']['order_id'])) {
                $search_field = 'id';
                $search->andWhere([$search_field => (int)$post['search']['order_id']]);
            }
        }

        $search->orderBy("last_message_date DESC, created_at DESC");

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
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Список сообщений в диалоге
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\base\ErrorException
     */
    public function getDialogMessages(array $post)
    {

        $page = (isset($post['pagination']['page']) ? $post['pagination']['page'] : 1);
        $pageSize = (isset($post['pagination']['page_size']) ? $post['pagination']['page_size'] : 12);

        $this->validateRequest($post, ['dialog_id']);

        $client = $this->user->organization;
        $order = Order::find()->where(['id' => $post['dialog_id']])
            ->andWhere(['or', ['client_id' => $client->id], ['vendor_id' => $client->id]])
            ->one();

        if (empty($order)) {
            throw new BadRequestHttpException("chat.dialog_not_found");
        }

        $orderChat = OrderChat::find()
            ->with(['recipient'])
            ->where(['order_id' => $order->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $orderChat
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        $modelsViewed = [];
        /**
         * @var $model OrderChat
         */
        foreach ($dataProvider->models as $model) {
            $message = $this->prepareMessage($model);

            if ($message['is_my_message'] === false && $message['viewed'] === false) {
                $modelsViewed[] = $model->id;
            }

            $result[] = $message;
        }

        if (!empty($modelsViewed)) {
            OrderChat::updateAll(['viewed' => 1], ['id' => $modelsViewed]);
        }

        /**
         * Отправка уведомлений в FCM
         */
        Notice::init('Chat')->updateCountMessageAndDialog($this->user->organization->id, $order);

        $return = [
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Добавить сообщение в диалог
     *
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \yii\base\ErrorException
     */
    public function addMessage(array $post)
    {

        $client = $this->user->organization;
        /**@var Order $order */
        $order = Order::find()->where(['id' => $post['dialog_id']])
            ->andWhere(['or', ['client_id' => $client->id], ['vendor_id' => $client->id]])
            ->one();

        if (empty($order)) {
            throw new BadRequestHttpException("chat.dialog_not_found");
        }

        if ($client->id == $order->client_id) {
            $recipient_id = $order->vendor_id;
        } else {
            $recipient_id = $order->client_id;
        }

        $dialogMessage = new OrderChat([
            'order_id'     => $order->id,
            'sent_by_id'   => $this->user->id,
            'recipient_id' => $recipient_id,
            'message'      => \Yii::$app->db->quoteValue($post['message']),
            'is_system'    => 0,
            'viewed'       => 0,
            'danger'       => 0
        ]);

        if (!$dialogMessage->validate() || !$dialogMessage->save()) {
            throw new ValidationException($dialogMessage->getFirstErrors());
        }

        //Отправляем нотификацию
        Notice::init('Chat')->updateCountMessageAndDialog($recipient_id, $order);

        return $this->getDialogMessages(['dialog_id' => $order->id]);
    }

    /**
     * Список получателей сообщений
     *
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
        } elseif ($client->type_id === Organization::TYPE_SUPPLIER) {
            $where = ['order.vendor_id' => $client->id];
            $joinWith = 'order.client_id';
        } else {
            throw new BadRequestHttpException('chat.access_denied');
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
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

    /**
     * Отмечаем все сообщения прочитаными
     *
     * @return array
     * @throws \Exception
     */
    public function readAllMessages()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            OrderChat::updateAll(['viewed' => 1], [
                'recipient_id' => $this->user->organization->id,
                'viewed'       => 0
            ]);
            $transaction->commit();
            Notice::init('Chat')->readAllMessages($this->user->organization->id);
            return ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Пометить сообщения прочитанными
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function readMessages($post)
    {
        $this->validateRequest($post, ['dialog_id']);

        $order = Order::find()->where(['id' => $post['dialog_id']])->andWhere([
            'or',
            ['client_id' => $this->user->organization_id],
            ['vendor_id' => $this->user->organization_id]
        ])->one();

        if (empty($order)) {
            throw new BadRequestHttpException("chat.dialog_not_found");
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            OrderChat::updateAll(['viewed' => 1], [
                'recipient_id' => $this->user->organization->id,
                'viewed'       => 0,
                'order_id'     => $order->id
            ]);
            $transaction->commit();
            Notice::init('Chat')->readAllMessages($this->user->organization->id);
            return ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Количество не прочитанных сообщений
     *
     * @param null $r_id
     * @return int
     */
    public function getUnreadMessageCount($r_id = null)
    {
        $recipient_id = $r_id ?? $this->user->organization->id;
        return (int)OrderChat::find()->where(['viewed' => 0, 'recipient_id' => $recipient_id])->count();
    }

    /**
     * Число диалогов с новыми сообщениями
     *
     * @param null $r_id
     * @return array
     */
    public function dialogUnreadCount($r_id = null)
    {
        $recipient_id = $r_id ?? $this->user->organization->id;

        return [
            'result' => (int)OrderChat::find()
                ->select('order_id')
                ->where(['viewed' => 0, 'recipient_id' => $recipient_id])
                ->groupBy('order_id')
                ->count()
        ];
    }

    /**
     * @param Order $model
     * @return array
     */
    private function prepareDialog(Order $model)
    {
        $last_message = $model->last_message ?? \Yii::t('api_web', 'chat.not_message', ['ru' => 'Нет сообщений']);
        if (!empty($last_message)) {
            $last_message = stripcslashes(trim($last_message, "'"));
        }

        return [
            'dialog_id'         => (int)$model->id,
            'client'            => $model->client->name,
            'client_id'         => (int)$model->client->id,
            'vendor'            => $model->vendor->name,
            'vendor_id'         => (int)$model->vendor->id,
            'image'             => $model->vendor->pictureUrl ?? '',
            'count_message'     => (int)$model->count_message ?? 0,
            'unread_message'    => (int)$model->unread_message ?? 0,
            'last_message'      => strip_tags($last_message),
            'last_message_date' => WebApiHelper::asDatetime($model->last_message_date),
            'is_edi'            => $model->vendor->isEdi()
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
            'message_id'     => (int)$model->id,
            'message'        => stripcslashes(trim($model->message, "'")),
            'sender'         => $model->is_system ? 'MixCart Bot' : $model->sentBy->profile->full_name,
            'recipient_name' => $model->recipient->name,
            'recipient_id'   => (int)$model->recipient->id,
            'is_my_message'  => $is_my_message,
            'is_system'      => $model->is_system ? true : false,
            'viewed'         => $model->viewed ? true : false,
            'date'           => WebApiHelper::asDatetime($model->created_at),
        ];
    }
}