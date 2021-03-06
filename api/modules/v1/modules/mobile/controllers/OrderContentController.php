<?php

namespace api\modules\v1\modules\mobile\controllers;

use common\models\OrderStatus;
use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\OrderContent;
use api\modules\v1\modules\mobile\resources\Order;
use common\models\CatalogBaseGoods;
use yii\data\ActiveDataProvider;
use common\models\RelationSuppRest;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use common\models\Organization;
use common\models\OrderChat;


/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderContentController extends ActiveController {

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\OrderContent';

    /**
     * @return array
     */
    public function behaviors() {
        $behaviors = parent::behaviors();

        $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel']
            ],
            /* 'create' => [
               'class' => 'yii\rest\CreateAction',
               'modelClass' => 'common\models\OrderContent',
               'checkAccess' => [$this, 'checkAccess'],
               'scenario' => $this->updateScenario,
           ],*/
            /* 'update' => [
                'class' => 'api\modules\v1\modules\mobile\controllers\actions\OrderContentEdit',
                'modelClass' => 'common\models\OrderContent',
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],*/
            /*'delete' => [
                'class' => 'yii\rest\DeleteAction',
                'modelClass' => 'common\models\OrderContent',
                'checkAccess' => [$this, 'checkAccess'],
            ]*/
        ];
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    public function findModel($id) {
        $model = OrderContent::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
    
    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $params = new OrderContent();
        $user = Yii::$app->user->getIdentity();
        
        $query = OrderContent::find();
        

        if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT)
        $query = OrderContent::find()->where(['in','order_id', Order::find()->select('id')->where(['client_id' => $user->organization_id])]);
        
        if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
             $query = OrderContent::find()->where(['in','order_id', Order::find()->select('id')->where(['vendor_id' => $user->organization_id])]);
        
        $cbgTable = CatalogBaseGoods::tableName();
        $orderTable = \common\models\Order::tableName();
        $currencyTable = \common\models\Currency::tableName();
        
        $query->select("order_content.*, order_content.product_name as product, $cbgTable.ed as ed, $currencyTable.symbol as symbol");
        $query->leftJoin($cbgTable,"$cbgTable.id = order_content.product_id");
        $query->leftJoin($orderTable,"$orderTable.id = order_content.order_id");
        $query->leftJoin($currencyTable,"$currencyTable.id = $orderTable.currency_id");
     
        $dataProvider =  new ActiveDataProvider(array(
            'query' => $query,
            'pagination' => false,
        ));
        
        
        
        if (!($params->load(Yii::$app->request->queryParams) && $params->validate())) {
            return $dataProvider;
        }
  
        if($params->list != null)
            $query->andWhere ('order_id IN('.implode(',', Json::decode($params->list)).')');
        
        $query->andFilterWhere([
            'id' => $params->id, 
            'order_id' => $params->order_id,
            'product_id' => $params->product_id, 
            'quantity' => $params->quantity, 
            'price' => $params->price, 
            'initial_quantity' => $params->initial_quantity, 
            'units' => $params->units, 
            'article' => $params->article
           ]);
        return $dataProvider;
    }

    public function actionUpdate($id)
    {
        $product = $this->findModel($id);

        //if ($this->checkAccess) {
            $this->checkAccess('update', $product);
       // }

        $position = Yii::$app->getRequest()->getBodyParams();

        $order = $product->order;
        $user = Yii::$app->user->getIdentity();
        $organizationType = $user->organization->type_id;
        $initiator = ($organizationType == Organization::TYPE_RESTAURANT) ? $order->client->name : $order->vendor->name;
        $message = "";
        $orderChanged = 0;

        $initialQuantity = $product->initial_quantity;
        $allowedStatuses = [
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            OrderStatus::STATUS_PROCESSING
        ];
        $quantityChanged = isset($position['quantity']) ? ($position['quantity']!= $product->quantity) : false;
        $priceChanged = isset($position['price']) ? ($position['price'] != $product->price) : false;

        if (($organizationType == Organization::TYPE_RESTAURANT || in_array($order->status, $allowedStatuses)) && ($quantityChanged || $priceChanged)) {
            $orderChanged = ($orderChanged || $quantityChanged || $priceChanged);
            if ($quantityChanged) {
                $ed = isset($product->product->ed) ? ' ' . $product->product->ed : '';
                if ($position['quantity'] == 0) {
                    $message .= "<br/> удалил $product->product_name из заказа";
                } else {
                    $oldQuantity = $product->quantity + 0;
                    $newQuantity = $position["quantity"] + 0;
                    $message .= "<br/> изменил количество $product->product_name с $oldQuantity" . $ed . " на $newQuantity" . $ed;
                }
                $product->quantity = $position['quantity'];
            }
            if ($priceChanged) {
                $message .= "<br/> изменил цену $product->product_name с $product->price руб на $position[price] руб";
                $product->price = $position['price'];
                if ($user->organization->type_id == Organization::TYPE_RESTAURANT && !$order->vendor->hasActiveUsers()) {
                    $prodFromCat = $product->getProductFromCatalog();
                    if (!empty($prodFromCat)) {
                        $prodFromCat->price = $product->price;
                        $prodFromCat->baseProduct->price = $product->price;
                        $prodFromCat->save();
                        $prodFromCat->baseProduct->save();
                    }
                }
            }
            if ($quantityChanged && ($order->status == OrderStatus::STATUS_PROCESSING) && !isset($product->initial_quantity)) {
                $product->initial_quantity = $initialQuantity;
            }
            if ($product->quantity == 0) {
                $product->delete();
            } else {
               $product->save();
            }
        }
        
        if ($order->positionCount == 0 && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = OrderStatus::STATUS_REJECTED;
                $orderChanged = -1;
            }
            if ($order->positionCount == 0 && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = OrderStatus::STATUS_CANCELLED;
                $orderChanged = -1;
            }
            if ($orderChanged < 0) {
                $systemMessage = $initiator . ' отменил заказ!';
                $this->sendSystemMessage($user, $order->id, $systemMessage, true);
                if ($organizationType == Organization::TYPE_RESTAURANT) {
                    $this->sendOrderCanceled($order->client, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order);
                }
            }
            if (($orderChanged > 0) && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = ($order->status === OrderStatus::STATUS_PROCESSING) ? OrderStatus::STATUS_PROCESSING : (($order->status >=4 && $order->status <=6) ? $order->status : OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR);
                $this->sendSystemMessage($user, $order->id, $order->client->name . ' изменил детали заказа №' . $order->id . ":$message");
                $subject = $order->client->name . ' изменил детали заказа №' . $order->id . ":" . str_replace('<br/>', ' ', $message);
                foreach ($order->recipientsList as $recipient) {
                    $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);
                    if (($recipient->organization_id == $order->vendor_id) && $profile->phone && $recipient->smsNotification->order_changed) {
                        $text = $subject;
                        $target = $profile->phone;
                        Yii::$app->sms->send($text, $target);
                    }
                }
                $order->calculateTotalPrice();
                $order->save();
                $this->sendOrderChange($order->client, $order);
            } elseif (($orderChanged > 0) && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = ($order->status == OrderStatus::STATUS_PROCESSING)? OrderStatus::STATUS_PROCESSING : (($order->status >=4 && $order->status <=6) ? $order->status : OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR);
                $order->accepted_by_id = $user->id;
                $order->calculateTotalPrice();
                $order->save();
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . ' изменил детали заказа №' . $order->id . ":$message");
                $this->sendOrderChange($order->vendor, $order);
                $subject = $order->vendor->name . ' изменил детали заказа №' . $order->id . ":" . str_replace('<br/>', ' ', $message);
                foreach ($order->client->users as $recipient) {
                    $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);
                    if ($profile->phone && $recipient->smsNotification->order_changed) {
                        $text = $subject;
                        $target = $profile->phone;
                        Yii::$app->sms->send($text, $target);
                    }
                }
            }
            $order->save();
        return $product;
    }

    public function actionCreate()
    {
        $product = new \common\models\OrderContent();
        $product->setAttributes(Yii::$app->request->post());

        if (!$product->validate())
            throw new BadRequestHttpException('Data error');

        $this->checkAccess('create', $product);

        if (OrderContent::findOne(['order_id' => $product->order_id, 'product_id' => $product->product_id]) != null)
            throw new BadRequestHttpException('This product already exists');

        $order = $product->order;

        if($order->status != 1)
            throw new BadRequestHttpException('This order is close');

        $product->save(false);

        $message = $message = Yii::t('message', 'frontend.controllers.order.add_position', ['ru' => "<br/>добавил {prod} {quantity} {ed} по цене {productPrice} {currencySymbol}/{ed} ",
            'prod' => $product->product_name, 'productPrice' => $product->price, 'currencySymbol' => $order->currency->symbol, 'ed' => $product->product->ed, 'quantity' => $product->quantity]);

        $user = Yii::$app->user->getIdentity();
        $organizationType = $user->organization->type_id;

        if ($organizationType == Organization::TYPE_RESTAURANT) {
            //$order->status = ($order->status === Order::STATUS_PROCESSING) ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
            $this->sendSystemMessage($user, $order->id, $order->client->name . Yii::t('message', 'frontend.controllers.order.change_details_four', ['ru' => ' изменил детали заказа №'])  . $order->id . ":$message");
            $subject = $order->client->name . ' изменил детали заказа №' . $order->id . ":" . str_replace('<br/>', ' ', $message);
            foreach ($order->recipientsList as $recipient) {
                $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);
                if (($recipient->organization_id == $order->vendor_id) && $profile->phone && $recipient->smsNotification->order_changed) {
                    $text = $subject;
                    $target = $profile->phone;
                    Yii::$app->sms->send($text, $target);
                }
            }
            $order->calculateTotalPrice();
            $order->save();
            $this->sendOrderChange($order->client, $order);
        } elseif ($organizationType == Organization::TYPE_SUPPLIER) {
            //$order->status = $order->status == Order::STATUS_PROCESSING ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
            //$order->accepted_by_id = $user->id;
            $order->calculateTotalPrice();
            $order->save();
            $this->sendSystemMessage($user, $order->id, $order->vendor->name . Yii::t('message', 'frontend.controllers.order.change_details_four', ['ru' => ' изменил детали заказа №'])  . $order->id . ":$message");
            $this->sendOrderChange($order->vendor, $order);
            $subject = $order->vendor->name . ' изменил детали заказа №' . $order->id . ":" . str_replace('<br/>', ' ', $message);
            foreach ($order->client->users as $recipient) {
                $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);
                if ($profile->phone && $recipient->smsNotification->order_changed) {
                    $text = $subject;
                    $target = $profile->phone;
                    Yii::$app->sms->send($text, $target);
                }
            }
        }
        return $product;
    }
    
    
    
    /**
    * Checks the privilege of the current user.
    *
    * This method should be overridden to check whether the current user has the privilege
    * to run the specified action against the specified data model.
    * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
    *
    * @param string $action the ID of the action to be executed
    * @param \yii\base\Model $model the model to be accessed. If `null`, it means no specific model is being accessed.
    * @param array $params additional parameters
    * @throws ForbiddenHttpException if the user does not have access
    */
   public function checkAccess($action, $model = null, $params = [])
   {
       // check if the user can access $action and $model
       // throw ForbiddenHttpException if access should be denied
       if ($action === 'update' || $action === 'delete' || $action == 'create') {
           $user = Yii::$app->user->identity;

           if (($model->order->client_id !== $user->organization_id)&&($model->order->vendor_id !== $user->organization_id))
               throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s order content that you\'ve created.', $action));
       }
   }
   
   private function sendSystemMessage($user, $order_id, $message, $danger = false) {
        $order = Order::findOne(['id' => $order_id]);

        $newMessage = new OrderChat();
        $newMessage->order_id = $order_id;
        $newMessage->message = $message;
        $newMessage->is_system = 1;
        $newMessage->sent_by_id = $user->id;
        $newMessage->danger = $danger;
        if ($order->client_id == $user->organization_id) {
            $newMessage->recipient_id = $order->vendor_id;
        } else {
            $newMessage->recipient_id = $order->client_id;
        }
        $newMessage->save();
        $body = $this->renderPartial('@frontend/views/order/_chat-message', [
            'name' => '',
            'message' => $newMessage->message,
            'time' => $newMessage->created_at,
            'isSystem' => 1,
            'sender_id' => $user->id,
            'ajax' => 1,
            'danger' => $danger,
        ]);

        $clientUsers = $order->client->users;
        $vendorUsers = $order->vendor->users;

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body' => $body,
                    'channel' => $channel,
                    'isSystem' => 1,
                    'order_id' => $order_id,
                ])
            ]);
        }
        foreach ($vendorUsers as $vendorUser) {
            $channel = 'user' . $vendorUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body' => $body,
                    'channel' => $channel,
                    'isSystem' => 1,
                    'order_id' => $order_id,
                ])
            ]);
        }

        return true;
    }

    /**
     * Sends mail informing both sides about cancellation of order
     * 
     * @param Organization $senderOrg
     * @param Order $order
     */
    private function sendOrderCanceled($senderOrg, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = "Заказ №" . $order->id . " отменен!";

        $searchModel = new \common\models\search\OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_canceled) {
                        $notification = $mailer->compose('orderCanceled', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }

                $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);

                $notification = $recipient->getSmsNotification($org);
                if ($notification)
                    if ($profile->phone && $notification->order_canceled) {
                        $text = $senderOrg->name . " отменил заказ " . Yii::$app->google->shortUrl($order->getUrlForUser($recipient));//$senderOrg->name . " отменил заказ в системе №" . $order->id;
                        $target = $profile->phone;
                        Yii::$app->sms->send($text, $target);
                    }
            }
        }
    }
    
    /**
     * Sends email informing both sides about order change details
     *
     * @param Organization $senderOrg
     * @param Order $order
     */
    private function sendOrderChange($senderOrg, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = "Измененения в заказе №" . $order->id;

        $searchModel = new \common\models\search\OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            if ($recipient->getEmailNotification($order->vendor_id)->order_changed) {
                $result = $mailer->compose('orderChange', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            
            $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);
            
            if ($profile->phone && $profile->phone && $recipient->getSmsNotification($order->vendor_id)->order_changed) {
                $text = $senderOrg->name . " изменил заказ ".Yii::$app->google->shortUrl($order->getUrlForUser($recipient));//$subject;
                $target = $profile->phone;
                Yii::$app->sms->send($text, $target);
           }
        }
    }

}
