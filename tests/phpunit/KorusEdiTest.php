<?php

namespace tests\phpunit;

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use api_web\helpers\WaybillHelper;
use common\components\edi\EDIIntegration;
use common\components\edi\providers\KorusProvider;
use common\components\edi\realization\KorusRealization;
use common\models\Cart;
use common\models\CartContent;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use PHPUnit\Framework\TestCase;
use yii\base\Exception;
use yii\base\Controller;

/**
 * Class EdiTest
 *
 * @package tests\phpunit
 */
class KorusEdiTest extends TestCase
{
    public function orderProvider()
    {
        $arr = [
            'firstTest' => [6054, 6055, 3713, [1644739]]
        ];
        return $arr;
    }

    public function testData()
    {
        $arr = [
            'orgId' => 6055,
            'providerID' => 2
        ];
        $this->assertNotEmpty($arr);
        return $arr;
    }

    /**
     * @depends testData
     */
    public function testUploadEdiFilesListToTable($arr): void
    {
        $ediIntegration = new EDIIntegration(['orgId' => $arr['orgId'], 'providerID' => $arr['providerID']]);
        $ediIntegration->setProvider(new KorusProvider());
        $ediIntegration->setRealization(new KorusRealization([]));
        $this->assertNotEmpty($ediIntegration);
        $ediIntegration->handleFilesList();

        $list = $ediIntegration->provider->getFilesList($arr['orgId']);

        $rows = (new \yii\db\Query())
            ->select(['id', 'name'])
            ->from('edi_files_queue')
            ->where(['name' => $list])
            ->indexBy('id')
            ->all();
        $this->assertCount(count($list), $rows);
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @dataProvider orderProvider
     * @depends testData
     */
    public function testParseFiles($orgID, $vendorID, $userID, $goodsArray, $arr): void
    {
        $order = $this->createOrder($orgID, $vendorID, $userID, $goodsArray);
        $this->assertNotEmpty($order);

        $controller = new Controller("", "");
        $this->assertNotEmpty($controller);

        $string = $controller->renderFile('tests/edi_xml/test_ordrsp_korus.php', ['order' => $order]);
        $this->assertNotEmpty($string);

        $ediIntegration = new EDIIntegration(['orgId' => $vendorID, 'providerID' => $arr['providerID']]);
        $this->assertNotEmpty($ediIntegration);

        $ediIntegration->handleFilesListQueue();
        $ediIntegration->provider->parseFile($string);
    }

    /**
     * @param       $orgId
     * @param       $userId
     * @param array $catalogGoods ids
     * @return \common\models\Order
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function createOrder($orgId, $vendorId, $userId, $catalogGoods)
    {
        $cart = new Cart([
            'organization_id' => $orgId,
            'user_id'         => $userId,
        ]);
        if (!$cart->save()) {
            $cart = Cart::findOne(['organization_id' => $orgId, 'user_id' => $userId]);
            CartContent::deleteAll(['cart_id' => $cart->id]);
        }

        $products = CatalogBaseGoods::find()->andWhere(['id' => $catalogGoods])->all();
        $cartPositions = [];
        foreach ($products as $product) {

            $currency = $product->catalog->currency ?? 1;
            $position = new CartContent();
            $position->cart_id = $cart->id;
            $position->product_id = $product['id'];
            $position->quantity = 5;
            $position->price = $product['price'];
            $position->product_name = $product['product'];
            $position->units = $product['units'];
            $position->vendor_id = $vendorId;
            $position->currency_id = $currency->id;
            $position->save();
            $cartPositions[] = $position;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //Создаем заказ
            $order = new Order();
            $order->client_id = $orgId;
            $order->created_by_id = $userId;
            $order->vendor_id = $vendorId;
            $order->status = OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
            $order->currency_id = $currency->id;
            $order->service_id = 9;
            if (!$order->validate() || !$order->save()) {
                throw new ValidationException($order->getFirstErrors());
            }
            /**@var CartContent $cartContent */
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
            //$order->calculateTotalPrice();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $order;
    }

}