<?php

namespace api_web\classes;

use api_web\components\Notice;
use api_web\helpers\Product;
use api_web\helpers\WebApiHelper;
use common\models\Cart;
use common\models\CartContent;
use common\models\Order;
use common\models\OrderContent;
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
    /**
     * Добавляем/Удаляем товар в заказе
     * @param array $post
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function add(array $post)
    {
        //Если прилетел массив товаров
        if (isset($post[0])) {
            foreach ($post as $item) {
                $this->add($item);
            }
        } else {
            $this->addItem($post);
        }

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
            throw new BadRequestHttpException("ERROR: Empty quantity");
        }
        if (empty($post['product_id'])) {
            throw new BadRequestHttpException("ERROR: Empty product_id");
        }
        /**
         * @var Organization $client
         */
        $client = $this->user->organization;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $cart = $this->getCart();
            $product = (new Product())->findFromCatalogs($post['product_id']);
            $catalogs = explode(',', $client->getCatalogs());
            //В корзину можно добавлять товары с маркета, или с каталогов Поставщиков ресторана
            if (!in_array($product['cat_id'], $catalogs) && $product['market_place'] !== CatalogBaseGoods::MARKETPLACE_ON) {
                throw new BadRequestHttpException("Каталог {$product['cat_id']} недоступен для вас.");
            }
            $this->setPosition($cart, $product, $post['quantity']);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
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
                    'for_fvree_delivery' => $this->getCart()->forFreeDelivery($row->vendor_id),
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
        return $this->items();
    }

    /**
     * Создание заказа из корзины
     * @param array $post
     * @return array
     */
    public function register(array $post)
    {
        $cart = $this->getCart();
        return $this->createOrder($cart, $post ?? []);
    }

    /**
     * @param Cart $cart
     * @param $post
     * @return array
     * @throws \Exception
     */
    private function createOrder(Cart $cart, $post)
    {
        WebApiHelper::clearRequest($post);

        $vendors = [];
        if (!empty($post)) {
            foreach ($post as $row) {
                if (empty($row['id'])) {
                    throw new BadRequestHttpException("ERROR: Empty id");
                }
                $vendors[$row['id']] = [
                    'delivery_date' => $row['delivery_date'] ?? null,
                    'comment' => $row['comment'] ?? null,
                ];
            }
        }

        $client = $this->user->organization;
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $result = [];
            /**
             * @var $vendor Organization
             */
            //Бежим по поставщикам в корзине
            foreach ($cart->getVendors() as $vendor) {
                //Создаем заказ
                $order = new Order();
                $order->client_id = $client->id;
                $order->created_by_id = $this->user->id;
                $order->vendor_id = $vendor->id;
                $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $order->currency_id = $vendor->baseCatalog->currency_id;
                $order->created_at = gmdate("Y-m-d H:i:s");

                if (!empty($vendors)) {
                    if (!isset($vendors[$vendor->id])) {
                        continue;
                    } else {
                        if (!empty($vendors[$vendor->id]['delivery_date'])) {
                            $d = str_replace('.', '-', $vendors[$vendor->id]['delivery_date']);
                            $order->requested_delivery = date('Y-m-d H:i:s', strtotime($d . ' 19:00:00'));
                        }
                        if (!empty($vendors[$vendor->id]['comment'])) {
                            $order->comment = $vendors[$vendor->id]['comment'];
                        }
                    }
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
                    $orderContent->initial_quantity = $cartContent->quantity;
                    $orderContent->price = $cartContent->price;
                    $orderContent->product_name = $cartContent->product_name;
                    $orderContent->units = $cartContent->units;
                    $orderContent->comment = $cartContent->comment;
                    if ($orderContent->validate() && $orderContent->save()) {
                        $cartContent->delete();
                    } else {
                        throw new ValidationException($orderContent->getFirstErrors());
                    }
                }
                $order->calculateTotalPrice();
                //Сообщение в очередь поставщику, что есть новый заказ
                Notice::init('Order')->sendOrderToTurnVendor($vendor);
                //Емайл и смс о новом заказе
                Notice::init('Order')->sendEmailAndSmsOrderCreated($client, $order);
                $result[] = $order->id;
            }
            //Сообщение в очередь, Изменение количества товара в корзине
            Notice::init('Order')->sendOrderToTurnClient($client);
            $cart->updated_at = new Expression('NOW()');
            $cart->save();
            $transaction->commit();
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
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
            throw new BadRequestHttpException("ERROR: Empty product_id");
        }

        if (empty($post['comment'])) {
            throw new BadRequestHttpException("ERROR: Empty comment");
        }

        /**
         * @var $model CartContent
         */
        $model = $this->getCart()->getCartContents()->andWhere(['product_id' => $post['product_id']])->one();

        if (empty($model)) {
            throw new BadRequestHttpException("Нет такого товара в корзине");
        }

        $model->comment = $post['comment'];

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
        $cart = Cart::findOne(['organization_id' => $this->user->organization->id, 'user_id' => $this->user->id]);
        if (empty($cart)) {
            $cart = new Cart([
                'organization_id' => $this->user->organization->id,
                'user_id' => $this->user->id,
            ]);

            if (!$cart->save()) {
                throw new ValidationException($cart->getFirstErrors());
            }
        }
        return $cart;
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
    private function setPosition(Cart &$cart, array &$product, $quantity)
    {
        foreach ($cart->cartContents as $position) {
            if ($position->product_id == $product['id']) {
                if ($quantity <= 0) {
                    $position->delete();
                    return true;
                } else {
                    $position->quantity = $this->recalculationQuantity($product, $quantity);
                    $position->updated_at = new Expression('NOW()');
                    $position->save();
                    return true;
                }
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
    private function recalculationQuantity($product, $quantity)
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
    private function prepareProduct($row)
    {
        $model = $row->product;

        $item['id'] = (int)$model['id'];
        $item['product'] = $model['product'];
        $item['catalog_id'] = (int)$model['cat_id'];
        $item['category_id'] = isset($model['model']->category) ? (int)$model['model']->category->id : 0;
        $item['price'] = round($model['price'], 2);
        $item['rating'] = round($model['model']->ratingStars, 1);
        $item['supplier'] = Organization::findOne($model['vendor_id'])->name;
        $item['brand'] = ($model['model']->brand ? $model['model']->brand : '');
        $item['article'] = $model['model']->article;
        $item['ed'] = $model['model']->ed;
        $item['units'] = round(($model['units'] ?? 0), 3);
        $item['currency'] = $model['model']->catalog->currency->symbol;
        $item['currency_id'] = $model['model']->catalog->currency->id;
        $item['image'] = (new MarketWebApi())->getProductImage($model['model']);
        $item['in_basket'] = $this->countProductInCart($model['id']);
        $item['comment'] = $row->comment;
        return $item;
    }
}