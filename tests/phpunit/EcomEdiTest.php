<?php

namespace tests\phpunit;

use api_web\exceptions\ValidationException;
use common\components\edi\EDIIntegration;
use common\models\Cart;
use common\models\CartContent;
use common\models\CatalogBaseGoods;
use common\models\edi\EdiProvider;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use PHPUnit\Framework\TestCase;
use yii\base\Controller;

/**
 * Class EdiTest
 *
 * @package tests\phpunit
 */
class EcomEdiTest extends TestCase
{

    public function orderProvider()
    {
        $ediProvider = EdiProvider::findOne(['provider_class' => 'EcomProvider']);
        $this->assertNotEmpty($ediProvider);

        $ordrspPath = 'tests/edi_xml/test_ordrsp_ecom.php';
        $desadvPath = 'tests/edi_xml/test_desadv_ecom.php';

        $paramsFirst = \Yii::$app->params['unitTestsData']['ecom']['firstTest'];
        $this->assertNotEmpty($paramsFirst);

        $arr = [
            'firstTest' => [$paramsFirst['restOrgID'], $paramsFirst['vendorOrgID'], $paramsFirst['userID'], $paramsFirst['goodsArray'], $ediProvider->id, $ordrspPath, $desadvPath]
        ];
        return $arr;
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @dataProvider orderProvider
     */
    public function testUploadEdiFilesListToTable($restOrgID, $vendorOrgID, $userID, $goodsArray, $providerID, $ordrspPath, $desadvPath): void
    {
        $ediIntegration = new EDIIntegration(['orgId' => $vendorOrgID, 'providerID' => $providerID]);
        $this->assertNotEmpty($ediIntegration);

        $ediIntegration->handleFilesList();

        $list = $ediIntegration->provider->getFilesList($vendorOrgID);

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
     */
    public function testParseFiles($restOrgID, $vendorOrgID, $userID, $goodsArray, $providerID, $ordrspPath, $desadvPath): void
    {
        $order = $this->createOrder($restOrgID, $vendorOrgID, $userID, $goodsArray);
        $this->assertNotEmpty($order);

        $controller = new Controller("", "");
        $this->assertNotEmpty($controller);

        //test ordrsp
        $string = $controller->renderFile($ordrspPath, ['order' => $order]);
        $this->assertNotEmpty($string);

        $ediIntegration = new EDIIntegration(['orgId' => $vendorOrgID, 'providerID' => $providerID]);
        $this->assertNotEmpty($ediIntegration);

        $ediIntegration->handleFilesListQueue();
        $ediIntegration->provider->parseFile($string);

        //test desadv
        $stringDesadv = $controller->renderFile($desadvPath, ['order' => $order]);
        $this->assertNotEmpty($stringDesadv);

        $ediIntegration2 = new EDIIntegration(['orgId' => $vendorOrgID, 'providerID' => $providerID]);
        $this->assertNotEmpty($ediIntegration2);

        $ediIntegration2->handleFilesListQueue();
        $ediIntegration2->provider->parseFile($stringDesadv);

        $this->assertNotEmpty($order);
        $order->status = OrderStatus::STATUS_DONE;
        $order->save();
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
            $order->calculateTotalPrice();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $order;
    }

}