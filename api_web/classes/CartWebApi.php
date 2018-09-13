<?php

namespace api_web\classes;

use api_web\components\FireBase;
use api_web\components\Notice;
use api_web\helpers\Product;
use api_web\helpers\WebApiHelper;
use common\models\Cart;
use common\models\CartContent;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use yii\db\Expression;
use yii\db\Query;
use common\models\Organization;
use common\models\CatalogBaseGoods;
use yii\web\BadRequestHttpException;
use api_web\exceptions\ValidationException;

/**
 * Class CartWebApi
 * @package api_web\classes
 */
class CartWebApi extends \api_web\components\WebApi
{

    public static $cart;
    public $catalogs;

    /**
     * Добавляем/Удаляем товар в заказе
     * @param array $post
     * @param bool $ajax_published
     * @return array
     */
    public function add(array $post, $ajax_published = false)
    {
        //Если прилетел массив товаров
        if (!isset($post['product_id'])) {
            foreach ($post as $item) {
                $this->addItem($item);
            }
        } else {
            $this->addItem($post);
        }

        if (!$ajax_published) {
            //Сообщение в очередь, Изменение количества товара в корзине
            $this->noticeWhenProductAddToCart();
        }

        //Обновляем дату изменения корзины
        $cart = $this->getCart();
        $cart->updated_at = new Expression('NOW()');
        $cart->save(false);

        return $this->items();
    }

    /**
     * Добавляем товар в корзину
     * @param array $post
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    private function addItem(array $post)
    {
        if (!isset($post['quantity'])) {
            throw new BadRequestHttpException("empty_param|quantity");
        }
        if (empty($post['product_id'])) {
            throw new BadRequestHttpException("empty_param|product_id");
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $cart = $this->getCart();
            $product = (new Product())->findFromCatalogs($post['product_id']);
            //В корзину можно добавлять товары с маркета, или с каталогов Поставщиков ресторана
            if (!in_array($product['cat_id'], $this->getCatalogs()) && $product['market_place'] !== CatalogBaseGoods::MARKETPLACE_ON) {
                throw new BadRequestHttpException("Каталог {$product['cat_id']} недоступен для вас.");
            }
            $this->setPosition($cart, $product, $post['quantity']);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Содержимое корзины
     * @return array
     */
    public function items()
    {
        $client = $this->user->organization;
        //Корзина теущего клиента
        $content = $client->_getCart();

        if (empty($content)) {
            return [];
        }
        $return = [];
        $items = [];
        /**
         * @var CartContent $row
         */
        foreach ($content as $row) {
            $items[$row->vendor->id][] = $this->prepareProduct($row);
            if (!isset($return[$row->vendor->id])) {
                $return[$row->vendor->id] = [
                    'id' => $row->vendor->id,
                    'delivery_price' => $this->getCart()->calculateDelivery($row->vendor_id),
                    'for_min_cart_price' => $this->getCart()->forMinCartPrice($row->vendor_id),
                    'for_free_delivery' => $this->getCart()->forFreeDelivery($row->vendor_id),
                    'total_price' => $this->getCart()->calculateTotalPrice($row->vendor_id),
                    'vendor' => WebApiHelper::prepareOrganization($row->vendor),
                    'currency' => $items[$row->vendor->id][0]['currency'],
                    'items' => $items[$row->vendor->id]
                ];
            } else {
                $return[$row->vendor->id]['items'] = $items[$row->vendor->id];
            }
        }

        return array_values($return);
    }

    /**
     * Очистка корзины, полная или частичная
     * @param array $post
     * @return array
     */
    public function clear(array $post)
    {
        $client = $this->user->organization;
        $query = Cart::find()->where(['organization_id' => $client->id]);
        /**
         * @var $cart Cart
         * @var $position CartContent
         */
        $carts = $query->all();
        foreach ($carts as $cart) {
            foreach ($cart->cartContents as $position) {
                if (isset($post['vendor_id'])) {
                    if ($position->vendor_id == $post['vendor_id']) {
                        $position->delete();
                    }
                } else {
                    $position->delete();
                }
            }
        }

        //Сообщение в очередь, Изменение количества товара в корзине
        Notice::init('Order')->sendOrderToTurnClient($this->user);

        return $this->items();
    }

    /**
     * Создание заказа из корзины
     * https://api-dev.mixcart.ru/site/doc#!/Cart/post_cart_registration
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function registration(array $post)
    {
        WebApiHelper::clearRequest($post);
        $cart = $this->getCart();

        //Результат для ответа
        $result = [
            'success' => 0,
            'error' => 0,
            'message' => '',
        ];

        if (!empty($post)) {

            if (isset($post['id'])) {
                $post[] = $post;
            }

            $orders = [];
            foreach ($post as $row) {
                if (empty($row['id'])) {
                    throw new BadRequestHttpException("empty_param|id");
                }
                $orders[$row['id']] = [
                    'delivery_date' => $row['delivery_date'] ?? null,
                    'comment' => $row['comment'] ?? null,
                ];
            }
        }

        try {
            foreach ($cart->getVendors() as $vendor) {
                if (isset($orders) && empty($orders[$vendor->id])) {
                    continue;
                }
                if ($this->createOrder($cart, $vendor, $orders[$vendor->id])) {
                    $result['success'] += 1;
                }
            }
        } catch (\Exception $e) {
            $result['error'] += 1;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * Создание заказа
     * @param Cart $cart
     * @param $vendor
     * @param array $post ['id', 'delivery_date', 'comment']
     * @return bool
     * @throws \Exception
     */
    private function createOrder(Cart $cart, Organization $vendor, array $post)
    {
        $client = $this->user->organization;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //Создаем заказ
            $order = new Order();
            $order->client_id = $client->id;
            $order->created_by_id = $this->user->id;
            $order->vendor_id = $vendor->id;
            $order->status = OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
            $order->currency_id = ($cart->getCartContents()->andWhere(['vendor_id' => $vendor->id])->one())->currency_id;

            if (!empty($post['delivery_date'])) {
                $d = str_replace('.', '-', $post['delivery_date']);
                $order->requested_delivery = date('Y-m-d H:i:s', strtotime($d . ' 19:00:00'));
            }

            if (!empty($post['comment'])) {
                $order->comment = $post['comment'];
            }

            if (!$order->validate() || !$order->save()) {
                throw new ValidationException($order->getFirstErrors());
            }
            /**
             * @var $cartContent CartContent
             */
            //Получаем записи только нужного нам поставщика
            $contents = $cart->getCartContents()->andWhere(['vendor_id' => $vendor->id])->all();
            foreach ($contents as $cartContent) {
                $orderContent = new OrderContent();
                $orderContent->order_id = $order->id;
                $orderContent->product_id = $cartContent->product_id;
                $orderContent->quantity = $cartContent->quantity;
                $orderContent->plan_quantity = $cartContent->quantity;
                $orderContent->initial_quantity = $cartContent->quantity;
                $orderContent->price = $cartContent->price;
                $orderContent->plan_price = $cartContent->price;
                $orderContent->product_name = $cartContent->product_name;
                $orderContent->units = $cartContent->units;
                $orderContent->comment = $cartContent->comment;
                $orderContent->article = $cartContent->product['article'];
                if ($orderContent->validate() && $orderContent->save()) {
                    $cartContent->delete();
                } else {
                    throw new ValidationException($orderContent->getFirstErrors());
                }
            }
            $order->calculateTotalPrice();
            $cart->updated_at = new Expression('NOW()');
            $cart->save();
            $transaction->commit();
            $orderCreated = true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        if ($orderCreated) {
            try {
                //Сообщение в очередь поставщику, что есть новый заказ
                Notice::init('Order')->sendOrderToTurnVendor($vendor);
                //Емайл и смс о новом заказе
                Notice::init('Order')->sendEmailAndSmsOrderCreated($client, $order);
                //Сообщение в очередь, Изменение количества товара в корзине
                Notice::init('Order')->sendOrderToTurnClient($this->user);
            } catch (\Exception $e) {
                \Yii::error($e->getMessage());
            }
            return true;
        }
        return false;
    }

    /**
     * Количество конкретного продукта в корзине пользователя
     * @param $id
     * @return float|int
     */
    public function countProductInCart($id)
    {
        $return = 0;

        if (\Yii::$app->user->isGuest) {
            return $return;
        }

        $result = (new Query())->from('cart as c')
            ->innerJoin('cart_content as cc', 'c.id = cc.cart_id')
            ->andWhere(['c.organization_id' => $this->user->organization->id])
            ->andWhere(['cc.product_id' => $id])
            ->one();

        if (!empty($result['quantity'])) {
            $return = round($result['quantity'], 3);
        }

        return $return;
    }

    /**
     * Добавить комментарий к товару
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function productComment(array $post)
    {
        if (empty($post['product_id'])) {
            throw new BadRequestHttpException("empty_param|product_id");
        }

        /**
         * @var $model CartContent
         */
        $model = $this->getCart()->getCartContents()->andWhere(['product_id' => $post['product_id']])->one();

        if (empty($model)) {
            throw new BadRequestHttpException("Нет такого товара в корзине");
        }

        $model->comment = $post['comment'] ?? '';

        if (!$model->validate()) {
            throw new ValidationException($model->getFirstErrors());
        }

        $model->save();

        return $this->items();
    }

    /**
     * Получить объект Cart текушего пользователя
     * @return Cart|null|static
     * @throws ValidationException
     */
    private function getCart()
    {
        if (empty(static::$cart)) {
            $cart = Cart::findOne(['organization_id' => $this->user->organization->id]);
            if (isset($individual_cart_enable)) {
                $cart = Cart::findOne(['organization_id' => $this->user->organization->id, 'user_id' => $this->user->id]);
            }

            if (empty($cart)) {
                $cart = new Cart([
                    'organization_id' => $this->user->organization->id,
                    'user_id' => $this->user->id,
                ]);

                if (!$cart->save()) {
                    throw new ValidationException($cart->getFirstErrors());
                }
            }
            static::$cart = $cart;
        }
        return static::$cart;
    }

    /**
     * Список доступных каталогов
     * @return array
     */
    private function getCatalogs()
    {
        if (empty($this->catalogs)) {
            $this->catalogs = explode(',', $this->user->organization->getCatalogs());
        }
        return $this->catalogs;
    }

    /**
     * Записываем позицию в корзину
     * @param Cart $cart
     * @param array $product
     * @param $quantity
     * @return bool
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function setPosition(Cart $cart, array &$product, $quantity)
    {
        /**
         * @var $productInCart CartContent
         */
        $productInCart = $cart->getCartContents()->andWhere(['product_id' => $product['id']])->one();
        if ($productInCart) {
            if ($quantity <= 0) {
                $productInCart->delete();
                return true;
            } else {
                $productInCart->quantity = $this->recalculationQuantity($productInCart, $quantity);
                $productInCart->updated_at = new Expression('NOW()');
                $productInCart->save(false);
                return true;
            }
        }

        if ($quantity > 0) {
            $position = new CartContent();
            $position->cart_id = $cart->id;
            $position->product_id = $product['id'];
            $position->quantity = $this->recalculationQuantity($product, $quantity);
            $position->price = $product['price'];
            $position->product_name = $product['product'];
            $position->units = $product['units'];
            $position->vendor_id = $product['vendor_id'];
            $position->currency_id = $product['currency_id'];
            if (!$position->validate()) {
                throw new ValidationException($position->getFirstErrors());
            }
            $position->save();
        } else {
            throw new BadRequestHttpException("ERROR: the quantity must be greater than zero");
        }
        return true;

    }

    /**
     * Считаем количество в корзине, исходя из кратности
     * @param $product
     * @param $quantity
     * @return float
     */
    public function recalculationQuantity($product, $quantity)
    {
        $units = $product['units'];

        if ($units == 0) {
            return round($quantity, 3);
        }

        if ($quantity < $units) {
            $quantity = $units;
        } else {
            if (strstr($units, '.') !== false || strstr($units, ',') !== false) {
                $quantity = round(round($quantity / $units) * $units, 3);
            } else {
                $quantity = round($quantity / $units, 0) * $units;
            }
        }
        return $quantity;
    }

    /**
     * Продукт. Собираем необходимые данные из модели
     * @param $row CartContent
     * @return mixed
     */
    private function prepareProduct(CartContent $row)
    {
        $model = $row->product;

        $item['id'] = (int)$model['id'];
        $item['product'] = $model['product'];
        $item['catalog_id'] = (int)$model['cat_id'];
        $item['category_id'] = isset($model['model']->category) ? (int)$model['model']->category->id : 0;
        $item['price'] = round($model['price'], 2);
        $item['rating'] = round($model['model']->ratingStars, 1);
        $item['supplier'] = $row->vendor->name;
        $item['brand'] = ($model['model']->brand ? $model['model']->brand : '');
        $item['article'] = $model['model']->article;
        $item['ed'] = $model['model']->ed;
        $item['units'] = round(($model['units'] ?? 0), 3);
        $item['currency'] = $row->currency->symbol;
        $item['currency_id'] = $row->currency->id;
        $item['image'] = (new MarketWebApi())->getProductImage($model['model']);
        $item['in_basket'] = $this->countProductInCart($model['id']);
        $item['comment'] = $row->comment;
        return $item;
    }

    public function noticeWhenProductAddToCart()
    {
        Notice::init('Order')->sendOrderToTurnClient($this->user);
        //Notice::init('Order')->sendLastUserCartAdd($this->user);
    }

}
