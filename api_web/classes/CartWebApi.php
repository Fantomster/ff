<?php

namespace api_web\classes;

use yii\db\Query;
use common\models\Order;
use yii\web\HttpException;
use common\models\CatalogGoods;
use common\models\OrderContent;
use common\models\Organization;
use common\models\RelationSuppRest;
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
        if (empty($post['catalog_id'])) {
            throw new BadRequestHttpException("ERROR: Empty catalog_id");
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $product = $this->findProduct($post['product_id'], $post['catalog_id']);
            /**
             * @var Organization $client
             * @var CatalogBaseGoods $product
             */
            $client = $this->user->organization;
            $catalogs = explode(',', $client->getCatalogs());
            //В корзину можно добавлять товары с маркета, или с каталогов Поставщиков ресторана
            if (!in_array($post['catalog_id'], $catalogs) && $product->model->market_place !== CatalogBaseGoods::MARKETPLACE_ON) {
                throw new BadRequestHttpException("Каталог {$post['catalog_id']} недоступен для вас.");
            }
            $order = $this->getOrder($product);
            $this->setPosition($order, $product, $post['quantity']);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function items()
    {
        $client = $this->user->organization;
        //Корзина теущего клиента
        $return = [];
        $orders = $client->getCart();

        if (empty($orders)) {
            return $return;
        }
        /**
         * @var Order $order
         */
        foreach ($orders as $order) {
            $catalog = RelationSuppRest::find()->
            where([
                'rest_org_id' => $client->id,
                'supp_org_id' => $order->vendor_id,
                'status' => RelationSuppRest::CATALOG_STATUS_ON
            ])->orderBy("updated_at DESC")->one();

            if (empty($catalog)) {
                $cat_id = null;
            } else {
                $cat_id = $catalog->cat_id;
            }

            $items = [];
            if (!empty($order->orderContent)) {
                foreach ($order->orderContent as $content) {
                    $product = $this->findProduct($content->product_id, $cat_id);
                    $items[] = $this->prepareProduct($product);
                }
            }

            $return[] = [
                'order' => $this->prepareOrderInfo($order),
                'organization' => (new MarketWebApi())->prepareOrganization($order->vendor),
                'items' => $items
            ];
        }

        return $return;
    }

    /**
     * @param array $post
     * @return array
     */
    public function clear(array $post)
    {
        $client = $this->user->organization;
        $query = Order::find()->where(['client_id' => $client->id, 'status' => Order::STATUS_FORMING]);

        if (isset($post['order_id'])) {
            $query->andWhere(['id' => $post['order_id']]);
        }

        $orders = $query->all();
        foreach ($orders as $order) {
            foreach ($order->orderContent as $position) {
                $position->delete();
                if (!($order->positionCount)) {
                    $order->delete();
                }
            }
        }
        return $this->items();
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

        $result = (new Query())->from('order as o')
            ->innerJoin('order_content as oc', 'o.id = oc.order_id')
            ->andWhere(['o.client_id' => $this->user->organization->id])
            ->andWhere(['o.status' => Order::STATUS_FORMING])
            ->andWhere(['oc.product_id' => $id])
            ->one();

        if (!empty($result['quantity'])) {
            $return = round($result['quantity'], 3);
        }

        return $return;
    }

    /**
     * Записываем позицию в заказ
     * @param Order $order
     * @param \stdClass $product
     * @param $quantity
     * @return bool
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    private function setPosition(Order &$order, \stdClass &$product, $quantity)
    {
        foreach ($order->orderContent as $position) {
            if ($position->product_id == $product->model->id) {
                if ($quantity <= 0) {
                    $position->delete();
                    $order->refresh();
                    if (count($order->orderContent) == 0) {
                        $order->delete();
                        $order = null;
                    }
                    return true;
                } else {
                    $position->quantity = $this->recalculationQuantity($product, $quantity);
                    $position->save();
                    return true;
                }
            }
        }

        if ($quantity > 0) {
            $position = new OrderContent();
            $position->order_id = $order->id;
            $position->product_id = $product->model->id;
            $position->quantity = $this->recalculationQuantity($product, $quantity);
            $position->price = $product->price;
            $position->product_name = $product->model->product;
            $position->units = $product->model->units;
            $position->article = $product->model->article;
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
        $units = $product->model->units;

        if ($units == 0) {
            return round($quantity, 3);
        }

        if ($quantity < $units) {
            $quantity = $units;
        } else {
            $quantity = round($quantity / $units, 3) * $units;
        }

        return $quantity;
    }

    /**
     * Получаем существующий заказ, или создаем новый
     * @param \stdClass $product
     * @return Order
     * @throws HttpException
     * @throws ValidationException
     */
    private function getOrder(\stdClass $product)
    {
        $client = $this->user->organization;
        //Корзина теущего клиента
        $orders = $client->getCart();

        foreach ($orders as $order) {
            if ($order->vendor_id == $product->model->vendor->id) {
                return $order;
            }
        }

        if (empty($product->model->catalog->currency_id)) {
            throw new HttpException(401, "В каталоге поставщика не установлена валюта.", 401);
        }

        $order = new Order();
        $order->client_id = $client->id;
        $order->vendor_id = $product->model->vendor->id;
        $order->status = Order::STATUS_FORMING;
        $order->currency_id = $product->model->catalog->currency_id;

        if (!$order->validate()) {
            throw new ValidationException($order->getFirstErrors());
        }

        $order->save();

        return $order;
    }

    /**
     * Составной объект продукта
     * @param $id
     * @param $cat_id
     * @return \stdClass
     * @throws BadRequestHttpException
     */
    private function findProduct($id, $cat_id)
    {
        $product = new \stdClass();

        $model = CatalogGoods::findOne(['base_goods_id' => $id, 'cat_id' => $cat_id]);
        if (empty($model)) {
            $model = $baseModel = CatalogBaseGoods::findOne(['id' => $id]);
        } else {
            $baseModel = $model->baseProduct;
        }

        if (empty($model)) {
            throw new BadRequestHttpException("Продукт не найден.");
        }

        /**
         * Есть ли в наличии
         */
        if ($baseModel->status == CatalogBaseGoods::STATUS_OFF) {
            throw new BadRequestHttpException("Продукта (" . $baseModel->product . ") нет в наличии.");
        }

        /**
         * Если не установлена кратность, считаем кратность 0
         */
        if (empty($baseModel->units)) {
            $baseModel->units = 0;
        }

        if ($model instanceof CatalogGoods) {
            $product->discount_price = $model->getDiscountPrice();
            $product->cat_id = $model->cat_id;
        } else {
            $product->discount_price = 0;
            $product->cat_id = $baseModel->cat_id;
        }

        $product->price = $model->price;
        $product->model = $baseModel;

        return $product;
    }

    /**
     * Продукт. Собираем необходимые данные из модели
     * @param $model \stdClass result function $this->findProduct()
     * @return mixed
     */
    private function prepareProduct($model)
    {
        $item['id'] = (int)$model->model->id;
        $item['product'] = $model->model->product;
        $item['catalog_id'] = (int)$model->cat_id;
        $item['category_id'] = isset($model->model->category) ? (int)$model->model->category->id : 0;
        $item['price'] = round($model->price, 2);
        $item['rating'] = round($model->model->ratingStars, 1);
        $item['supplier'] = $model->model->vendor->name;
        $item['brand'] = ($model->model->brand ? $model->model->brand : '');
        $item['article'] = $model->model->article;
        $item['ed'] = $model->model->ed;
        $item['units'] = round(($model->model->units ?? 0), 3);
        $item['currency'] = $model->model->catalog->currency->symbol;
        $item['image'] = (new MarketWebApi())->getProductImage($model->model);
        $item['in_basket'] = $this->countProductInCart($model->model->id);
        return $item;
    }

    /**
     * @param Order $order
     * @return array
     */
    private function prepareOrderInfo(Order $order)
    {

        if (empty($order->id)) {
            return null;
        }

        $order->calculateTotalPrice();

        $order_r = $order->attributes;
        $order_r['status_text'] = $order->statusText;
        $order_r['position_count'] = $order->positionCount;
        return $order_r;
    }
}