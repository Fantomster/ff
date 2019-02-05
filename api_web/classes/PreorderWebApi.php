<?php
/**
 * Date: 04.02.2019
 * Author: Mike N.
 * Time: 14:35
 */

namespace api_web\classes;

use api_web\ {
    helpers\WebApiHelper,
    exceptions\ValidationException,
    components\WebApi
};
use common\models\ {
    Order,
    Preorder,
    Cart,
    Organization,
    PreorderContent
};
use yii\web\BadRequestHttpException;

/**
 * Class PreorderWebApi
 *
 * @package api_web\classes
 */
class PreorderWebApi extends WebApi
{
    /**
     * @param array $vendors
     * @param Cart  $cart
     * @return int
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \Throwable
     */
    private function createPreorder(array $vendors, Cart $cart)
    {
        $preOrder = new Preorder();
        $preOrder->organization_id = $this->user->organization->id;
        $preOrder->user_id = $this->user->id;
        if (!$preOrder->save(true)) {
            throw new ValidationException($preOrder->getFirstErrors());
        }
        $cartWebApi = new CartWebApi();
        $noCommentAndDate = [];
        $preOrderId = $preOrder->id;
        foreach ($vendors as $index => $vendor) {
            $contents = $cart->getCartContents()->andWhere(['vendor_id' => $vendor->id])->all();
            if (empty($contents)) {
                throw new BadRequestHttpException('В корзине нет товаров данного веднора');
            }
            if ($cartWebApi->createOrder($cart, $vendor, $noCommentAndDate, Order::STATUS_PREORDER, $preOrderId)) {
                foreach ($contents as $key => $item) {
                    $preOrderContent = new PreorderContent();
                    $preOrderContent->preorder_id = $preOrderId;
                    $preOrderContent->product_id = $item->product_id;
                    $preOrderContent->plan_quantity = $item->quantity;
                    if (!$preOrderContent->save(true)) {
                        throw new ValidationException($preOrderContent->getFirstErrors());
                    }
                }
            }
        }
        return $preOrder->id;
    }

    /**
     * Создание предзаказа из корзины
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     * @throws \Throwable
     */
    public function create($post)
    {
        $cart = Cart::findOne(['organization_id' => $this->user->organization->id]);
        if (empty($cart)) {
            throw new BadRequestHttpException('Вы ещё не создавали корзину');
        }
        if (isset($post['vendor_id'])) {
            $vendor = Organization::findOne(['id' => $post['vendor_id'], 'type_id' => 2]);
            if (empty($vendor)) {
                throw new BadRequestHttpException('У вас нет поставщика с таким id');
            }
            $vendors[] = $vendor;
            $preOrderId = $this->createPreorder($vendors, $cart);
            $preOrder = Preorder::findOne(['id' => $preOrderId]);
            return $this->prepareModel($preOrder);
        } else {
            $vendors = $cart->getVendors();
            if (empty($vendors)) {
                throw new BadRequestHttpException('Корзина в данный момент пуста');
            }

            $preOrderId = $this->createPreorder($vendors, $cart);
            $preOrder = Preorder::findOne(['id' => $preOrderId]);
            return $this->prepareModel($preOrder);
        }
    }

    /**
     * Меняет статус предзаказа на неактивный
     *
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function complete($post)
    {
        $this->validateRequest($post, ['id']);
        $model = Preorder::findOne([
            'id'              => (int)$post['id'],
            'organization_id' => $this->user->organization_id
        ]);
        if (empty($model)) {
            throw new BadRequestHttpException('preorder.not_found');
        }
        $model->is_active = 0;
        if ($model->save()) {
            return $this->prepareModel($model);
        } else {
            throw new ValidationException($model->getFirstErrors());
        }
    }

    /**
     * Подготовка модели к выдаче фронту
     *
     * @param Preorder $model
     * @return array
     */
    private function prepareModel(Preorder $model)
    {
        $return = [
            'id'           => $model->id,
            'is_active'    => (bool)$model->is_active,
            'organization' => [
                'id'   => $model->organization->id,
                'name' => $model->organization->name
            ],
            'user'         => [
                'id'   => $model->user->id,
                'name' => $model->user->profile->full_name
            ],
            'count'        => [
                'products' => $model->getPreorderContents()->count(),
                'orders'   => $model->getOrders()->count(),
            ],
            'sum'          => $model->getSum(),
            'currency'     => [
                'id'     => $model->currency->id,
                'symbol' => $model->currency->symbol,
            ],
            'created_at'   => WebApiHelper::asDatetime($model->created_at),
            'updated_at'   => WebApiHelper::asDatetime($model->updated_at)
        ];

        return $return;
    }
}
