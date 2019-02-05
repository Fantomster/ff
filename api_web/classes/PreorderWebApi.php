<?php
/**
 * Date: 04.02.2019
 * Author: Mike N.
 * Time: 14:35
 */

namespace api_web\classes;

use api_web\components\WebApi;
use common\models\Order;
use common\models\Preorder;
use common\models\PreorderContent;
use common\models\Organization;
use common\models\Cart;

/**
 * Class PreorderWebApi
 *
 * @package api_web\classes
 */
class PreorderWebApi extends WebApi
{

    private function createPreorder($vendor)
    {
        $cart = Cart::findOne(['organization_id' => $this->user->organization->id]);
        $preOrder = new Preorder();
        $preOrder->organization_id=$this->user->organization->id;
        $preOrder->user_id=$this->user->id;
        $preOrder->save(true);
        $cartWebApi = new CartWebApi();
        $noCommentAndDate = [];
        $preOrderId = $preOrder->id;
        if ($contents = $cartWebApi->createOrder($cart,$vendor,$noCommentAndDate,Order::STATUS_PREORDER, $preOrderId)) {
            foreach ($contents as $index => $item) {
                $preOrderContent = new PreorderContent();
                $preOrderContent->preorder_id = $preOrderId;
                $preOrderContent->product_id = $item->product_id;
                $preOrderContent->plan_quantity = $item->quantity;
                $preOrderContent->save(true);
            }
        }

    }
    /**
     * Создание предзаказа из корзины
     *
     * @param $post
     * @return array
     */
    public function create($post)
    {
        if (isset($post['vendor_id'])) {
            $vendor = Organization::findOne(['id'=>$post['vendor_id'],'type_id'=>2]);
            $this->createPreorder($vendor);
        } else {
            $cart = Cart::findOne(['organization_id' => $this->user->organization->id]);
            $vendors = $cart->getVendors();
            foreach ($vendors as $index => $vendor) {
                $this->createPreorder($vendor);
            }
        }

        return ['STATUS_PREORDER' => Order::STATUS_PREORDER];
    }
}
