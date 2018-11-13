<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 29.09.2018
 * Time: 22:14
 */

namespace tests\phpunit;

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\helpers\WaybillHelper;
use common\components\ecom\EDIIntegration;
use common\components\ecom\providers\TestProvider;
use common\components\ecom\realization\TestRealization;
use common\models\Cart;
use common\models\CartContent;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use PHPUnit\Framework\TestCase;

/**
 * Class EdiTest
 *
 * @package tests\phpunit
 */
class EdiTest extends TestCase
{

    /**
     *
     */
    public function testUploadEdiFilesListToTable(): void
    {
        $eComIntegration = new EDIIntegration();
        $eComIntegration->setProvider(new TestProvider());
        $eComIntegration->setRealization(new TestRealization([]));
        $eComIntegration->handleFilesList();
        $list = $eComIntegration->provider->getResponse('', '');
        $rows = (new \yii\db\Query())
            ->select(['id'])
            ->from('edi_files_queue')
            ->where(['name' => $list])
            ->indexBy('id')
            ->all();
        $this->assertEquals(count($list), count($rows));
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function testParseFiles(): void
    {
        $order = $this->createOrder(3768, 3795, [1564828, 1564207, 1563629, 1564554, 1564387]);
        $fileName = 'test_ordrsp_20180918171352_2412656653.xml';

        $eComIntegration = new EDIIntegration(['orgId' => 7777], ['TestRealization' => [$fileName =>
                                                                                                  $order->id]]);

        $eComIntegration->handleFilesListQueue();
        /**@var Order $tO - testOrder*/
        $tO = Order::findOne($order->id);
        $this->assertEquals($tO->edi_ordersp, $fileName);
        $this->assertNotEquals($tO->total_price, 898.05);
        $this->assertEquals($tO->service_id, Registry::EDI_SERVICE_ID);
        foreach ($tO->orderContent as $content){
//            $wC = WaybillContent::findOne(['order_content_id' => $content->id]);
//            $this->assertTrue($wC);
            if ($content->product_id == 1563629){
                $notChanged = true;
            }
            if ($content->product_id == 1564207){
                $this->assertEquals($content->price, 118);
                $this->assertEquals($content->vat_product, 18);
//                $this->assertEquals($wC->price_with_vat, 118);
            }
            if ($content->product_id == 1564387){
                $this->assertEquals($content->price, 110);
                $this->assertEquals($content->vat_product, 10);
//                $this->assertEquals($wC->price_with_vat, 110);
            }
            if ($content->product_id == 1564554){
                $this->assertEquals($content->quantity, 4);
//                $this->assertEquals($wC->quantity_waybill, 4);
            }
            if ($content->product_id == 1565242){
                $this->assertEquals($content->quantity, 1);
//                $this->assertEquals($wC->quantity_waybill, 1);
            }
            if ($content->product_id == 1564828){
//                $deleted = true;
            }
//            $arWc[] = $wC;
        }
        $this->assertTrue($notChanged);
        foreach ($tO->orderChat as $item) {
            $item->delete();
        }

        foreach ($tO->orderContent as $content){
            $content->delete();
        }
        $tO->delete();

//        foreach ($arWc as $wC) {
//            $w = $wC->waybill;
//            $wC->delete();
//        }
//        $w->delete();
        (new \yii\db\Query())->createCommand()->delete('edi_files_queue', ['like', 'name', 'test_'])->execute();
    }


    /**
     * @param       $orgId
     * @param       $userId
     * @param array $catalogGoods ids
     * @return \common\models\Order
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function createOrder($orgId, $userId, $catalogGoods){
        $cart = new Cart([
            'organization_id' => $orgId,
            'user_id' => $userId,
        ]);
        if (!$cart->save()) {
            $cart = Cart::findOne(['organization_id' => $orgId, 'user_id' => $userId]);
            CartContent::deleteAll(['cart_id' => $cart->id]);
        }

        $products = CatalogBaseGoods::find()->andWhere(['id' => $catalogGoods])->all();
        $cartPositions = [];
        foreach ($products as $product){
            $c = Catalog::find()->where(['id' => $product['cat_id']])->one();
            $currency = $product->catalog->currency ?? 1;
            $position = new CartContent();
            $position->cart_id = $cart->id;
            $position->product_id = $product['id'];
            $position->quantity = 5;
            $position->price = $product['price'];
            $position->product_name = $product['product'];
            $position->units = $product['units'];
            $position->vendor_id = $c->supp_org_id;
            $position->currency_id = $currency->id;
            $position->save();
            $cartPositions[] = $position;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //Создаем заказ
            $order = new Order();
            $order->client_id = $userId;
            $order->created_by_id = $userId;
            $order->vendor_id = $c->supp_org_id;
            $order->status = OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
            $order->currency_id = $currency->id;
            $order->service_id = 9;
            if (!$order->validate() || !$order->save()) {
                throw new ValidationException($order->getFirstErrors());
            }
            /**@var CartContent $cartContent*/
            foreach ($cartPositions as $cartContent) {
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
//                $orderContent->article = $cartContent->product['article'];
                if ($orderContent->validate() && $orderContent->save()) {
                    $cartContent->delete();
                } else {
                    throw new ValidationException($orderContent->getFirstErrors());
                }
            }
            $order->calculateTotalPrice();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        
        return $order;
    }




}