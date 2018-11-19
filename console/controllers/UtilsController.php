<?php

namespace console\controllers;

use api\common\models\merc\MercVsd;
use api_web\helpers\Product;
use common\models\vetis\VetisProductItem;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\Mercury;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentsChangeList;
use frontend\modules\clientintegr\modules\merc\helpers\api\products\Products;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use common\models\Catalog;
use common\models\CatalogGoods;
use common\models\CatalogBaseGoods;

class UtilsController extends Controller
{

    public function actionAddDeliveries()
    {
        $vendors = \common\models\Organization::find()
            ->leftJoin('delivery', 'organization.id = delivery.vendor_id')
            ->where(['type_id' => \common\models\Organization::TYPE_SUPPLIER])
            ->andWhere('delivery.vendor_id IS NULL')
            ->all();
        foreach ($vendors as $vendor) {
            $delivery = new \common\models\Delivery();
            $delivery->vendor_id = $vendor->id;
            $delivery->save();
            echo "Delivery info for '$vendor->name' (id:$vendor->id) created \n";
        }
    }

    public function actionAddNotifications()
    {
        $users = \common\models\User::find()
            ->leftJoin('email_notification', 'user.id = email_notification.user_id')
            ->leftJoin('sms_notification', 'user.id = sms_notification.user_id')
            ->where('email_notification.id IS NULL')
            ->orWhere('sms_notification.id IS NULL')
            ->limit(300)
            ->all();
        foreach ($users as $user) {
            if (empty($user->emailNotification)) {
                $emailNotification = new \common\models\notifications\EmailNotification();
                $emailNotification->user_id = $user->id;
                $emailNotification->orders = true;
                $emailNotification->requests = true;
                $emailNotification->changes = true;
                $emailNotification->invites = true;
                $emailNotification->save();
            }
            if (empty($user->smsNotification)) {
                $smsNotification = new \common\models\notifications\SmsNotification();
                $smsNotification->user_id = $user->id;
                $smsNotification->orders = true;
                $smsNotification->requests = true;
                $smsNotification->changes = true;
                $smsNotification->invites = true;
                $smsNotification->save();
            }
        }
    }

    public function actionFillChatRecipient()
    {
        $emptyRecipientMessages = \common\models\OrderChat::find()
            ->where(['recipient_id' => 0])
            ->all();
        foreach ($emptyRecipientMessages as $message) {
            $order = $message->order;
            $senderId = $message->sentBy->organization_id;
            if ($order->client_id == $senderId) {
                $message->recipient_id = $order->vendor_id;
            } else {
                $message->recipient_id = $order->client_id;
            }
            if ($message->save()) {
                echo 'Recipient set for message #' . $message->id . " \n";
            }
        }
    }

    public function actionCreateNotifications()
    {

    }

    public function actionCheckProductPictures()
    {
        $products = \common\models\CatalogBaseGoods::find()->where("image is not null")->andWhere("deleted = 0")->all();
        foreach ($products as $product) {
            if ($product->image) {
                $headers = get_headers($product->imageUrl);
                if ($headers[0] == 'HTTP/1.1 403 Forbidden') {
                    echo $product->id;
                    $product->image = null;
                    $product->save();
                    echo " - fixed\n";
                }
            }
        }
    }

    public function actionCheckOrganizationPictures()
    {
        $organizations = \common\models\Organization::find()->where("picture is not null")->all();
        foreach ($organizations as $organization) {
            if ($organization->picture) {
                $headers = get_headers($organization->pictureUrl);
                if ($headers[0] == 'HTTP/1.1 403 Forbidden') {
                    echo $organization->id;
                    $organization->picture = null;
                    $organization->save();
                    echo " - fixed\n";
                }
            }
        }
    }

    public function actionTestRedis()
    {
        \Yii::$app->redis->executeCommand('PUBLISH', [
            'channel' => 'test',
            'message' => 'ololo!'
        ]);
    }

    public function actionUpdateMpCategories()
    {
        $categories = \common\models\MpCategory::find()->all();
        foreach ($categories as $category) {
            $category->update();
        }
    }

    public function actionEraseOrganization($orgId)
    {
        $organization = \common\models\Organization::findOne(['id' => $orgId]);
        if (empty($organization)) {
            return;
        }
        echo $organization->name . "\n";
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            \common\models\DeliveryRegions::deleteAll(['supplier_id' => $orgId]);
            \common\models\FranchiseeAssociate::deleteAll(['organization_id' => $orgId]);
            $guides = \common\models\guides\Guide::findAll(['client_id' => $orgId]);
            foreach ($guides as $guide) {
                \common\models\guides\GuideProduct::deleteAll(['guide_id' => $guide->id]);
                $guide->delete();
            }
            \common\models\RelationSuppRest::deleteAll(['supp_org_id' => $orgId]);
            \common\models\RelationSuppRest::deleteAll(['rest_org_id' => $orgId]);
            $orders = ($organization->type_id === \common\models\Organization::TYPE_RESTAURANT) ? \common\models\Order::findAll(['client_id' => $orgId]) : \common\models\Order::findAll(['vendor_id' => $orgId]);
            foreach ($orders as $order) {
                //\common\models\OrderContent::deleteAll(['order_id' => $order->id]);
                foreach ($order->orderContent as $content) {
                    $content->delete();
                }
                foreach ($order->orderChat as $chat) {
                    $chat->delete();
                }
                //\common\models\OrderChat::deleteAll(['order_id' => $this->id]);
                $order->delete();
            }
            $catalogs = \common\models\Catalog::findAll(['supp_org_id' => $orgId]);
            foreach ($catalogs as $catalog) {
                \common\models\CatalogGoods::deleteAll(['cat_id' => $catalog->id]);
            }
            $goodsNotes = \common\models\GoodsNotes::find()
                ->leftJoin('catalog_base_goods', 'catalog_base_goods.id = goods_notes.catalog_base_goods_id')
                ->where(['catalog_base_goods.supp_org_id' => $orgId])
                ->all();
            foreach ($goodsNotes as $note) {
                $note->delete();
            }
            \common\models\CatalogBaseGoods::deleteAll(['supp_org_id' => $orgId]);
            \common\models\Catalog::deleteAll(['supp_org_id' => $orgId]);
            \common\models\RequestCallback::deleteAll(['supp_org_id' => $orgId]);
            $requests = \common\models\Request::findAll(['rest_org_id' => $orgId]);
            foreach ($requests as $request) {
                \common\models\RequestCallback::deleteAll(['request_id' => $this->id]);
                \common\models\RequestCounters::deleteAll(['request_id' => $this->id]);
                $request->delete();
            }
            \common\models\ManagerAssociate::deleteAll(['organization_id' => $orgId]);
            $users = \common\models\User::findAll(['organization_id' => $orgId]);
            foreach ($users as $user) {
                \common\models\ManagerAssociate::deleteAll(['manager_id' => $this->id]);
                $user->emailNotification->delete();
                $user->smsNotification->delete();
                \common\models\UserFcmToken::deleteAll(['user_id' => $this->id]);
                \common\models\UserToken::deleteAll(['user_id' => $this->id]);
                $user->profile->delete();
                $user->delete();
            }
            $organization->buisinessInfo->delete();
            $organization->delete();
            $transaction->commit();
        } catch (Exception $ex) {
            $transaction->rollback();
        }
    }

    public function actionMassErase()
    {
        $organizationsIds = [];
        foreach ($organizationsIds as $organizationId) {
            $this->actionEraseOrganization($organizationId);
        }
    }

    public function actionMassDecode()
    {
        set_time_limit(180);
        do {
            $products = \common\models\CatalogBaseGoods::find()->where("product like '%&#039;%' ")->limit(100)->all();
            foreach ($products as $cbg) {
                $cbg->product = \yii\helpers\Html::decode($cbg->product);
                $cbg->save(false);
                echo $cbg->product . "\n";
            }
        } while ($products);
    }

    public function actionMigrateCart()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            echo "Find order in cart..." . PHP_EOL;
            $orders = \common\models\Order::findAll(['status' => 7]);
            $count = count($orders);
            echo "Find " . $count . " orders";
            $i = 1;
            foreach ($orders as $order) {
                echo "Migrate " . $i . " of " . $count . PHP_EOL;
                $cart = \common\models\Cart::findOne(['user_id' => $order->created_by_id, 'organization_id' => $order->client_id]);
                if ($cart == null) {
                    $cart = new \common\models\Cart();
                    $cart->user_id = $order->created_by_id;
                    $cart->organization_id = $order->client_id;
                    if (!$cart->save()) {
                        throw new \Exception ("Error create cart from order ID" . $order->id . PHP_EOL);
                    }
                }
                foreach ($order->orderContent as $position) {
                    $cartContent = new \common\models\CartContent();
                    $cartContent->cart_id = $cart->id;
                    $cartContent->vendor_id = $order->vendor_id;
                    $cartContent->product_id = $position->product_id;
                    $cartContent->product_name = $position->product_name;
                    $cartContent->quantity = $position->quantity;
                    $cartContent->price = $position->price;
                    $cartContent->units = $position->units;
                    $cartContent->comment = $position->comment;
                    $cartContent->currency_id = $order->currency_id;

                    if (!$cartContent->save()) {
                        throw new \Exception ("Error add to cart position from order_content ID" . $position->id . PHP_EOL);
                    }
                }
                $i++;
            }
            $transaction->commit();
        } catch (\Exception $e) {
            echo $e->getMessage();
            $transaction->rollBack();
        }
    }

    public function actionUpdateVetisProductItem($count = 10000)
    {
        $product = new Products();
        $all_count = VetisProductItem::find()->count();
        echo "Update ".$all_count." rows".PHP_EOL;
        $offset = 0;
        $i=0;

        do {
            $query = (new \yii\db\Query())
                ->select([
                    'uuid',
                    'data' //=> '(SELECT t2.data FROM ' . VetisProductItem::tableName() . ' t2 WHERE t2.uuid = t1.uuid)'
                ])
                ->from(VetisProductItem::tableName() . ' t1')
                ->limit($count)
                ->offset($offset)
                ->indexBy('uuid');

            echo "start SQL".PHP_EOL;
            $rows = $query->all(\Yii::$app->get('db_api'));
            echo "end SQL".PHP_EOL;
            $this->vetisWork($rows, $i, $all_count);
            $offset += $count;
        } while ($i < $all_count);
    }

    private function vetisWork($rows, &$i, $all_count)
    {
        $generator = function ($items) {
            foreach ($items as &$item) {
                yield $item;
            }
        };

        foreach ($generator($rows) as $row) {
            $i++;
            $dataPackaging = (unserialize($row['data']))->packaging;
            if (isset($dataPackaging)) {
                $params = ['packagingType_guid' => isset($dataPackaging->packagingType->guid) ? $dataPackaging->packagingType->guid : null,
                    'packagingType_uuid' => isset($dataPackaging->packagingType->uuid) ? $dataPackaging->packagingType->uuid : null,
                    'unit_uuid' => isset($dataPackaging->unit->uuid) ? $dataPackaging->unit->uuid : null,
                    'unit_guid' => isset($dataPackaging->unit->guid) ? $dataPackaging->unit->guid : null,
                    'packagingQuantity' => isset($dataPackaging->quantity) ? $dataPackaging->quantity : null,
                    'packagingVolume' => isset($dataPackaging->volumne) ? $dataPackaging->volumne : null
                ];
                $arWhere['uuid'] = $row['uuid'];
                (new \yii\db\Query())->createCommand(\Yii::$app->db_api)->update(VetisProductItem::tableName(),$params, $arWhere)->execute();
            }
            echo $i . "/" . $all_count . PHP_EOL;
        }
    }

    public function actionUpdateMercVsd($count = 1000)
    {
        $mercury = new Mercury();
        $all_count = MercVsd::find()->count();
        echo "Update ".$all_count." rows".PHP_EOL;
        $offset = 0;
        $i=0;

        do {
            $query = (new \yii\db\Query())
                ->select([
                    'uuid',
                    'raw_data' //=> '(SELECT t2.data FROM ' . VetisProductItem::tableName() . ' t2 WHERE t2.uuid = t1.uuid)'
                ])
                ->from(MercVsd::tableName() . ' t1')
                ->limit($count)
                ->offset($offset)
                ->indexBy('uuid');

            echo "start SQL".PHP_EOL;
            $rows = $query->all(\Yii::$app->get('db_api'));
            echo "end SQL".PHP_EOL;
            $this->mercVSDWork($rows, $i, $all_count);
            $offset += $count;
        } while ($i < $all_count);
    }

    private function mercVSDWork($rows, &$i, $all_count)
    {
        $generator = function ($items) {
            foreach ($items as &$item) {
                yield $item;
            }
        };

        $vsd = new VetDocumentsChangeList();
        $vsd->org_id = 0;
        foreach ($generator($rows) as $row) {
            $i++;
            $doc = (unserialize($row['raw_data']));
            $vsd->updateDocumentsList($doc);
            echo $i . "/" . $all_count . PHP_EOL;
        }
    }

    public function actionCopyBaseCatalogs()
    {
        {
            $array_catalogs = Catalog::find()->where(['type' => 1])->all();
            $array_1 = ArrayHelper::getColumn($array_catalogs, 'id');
            unset($array_catalogs);
            foreach ($array_1 as $cat) {
                $products = CatalogBaseGoods::find()->where(['cat_id' => $cat])->andWhere(['deleted' => 0])->all();
                /** @var CatalogBaseGoods $product */
                foreach ($products as $product) {
                    $result = CatalogGoods::find()->where(['base_goods_id' => $product->id])->exists();
                    if ($result === false) {
                        $row = new CatalogGoods;
                        $row->cat_id = $product->cat_id;
                        $row->base_goods_id = $product->id;
                        $row->price = $product->price;
                        $row->vat = null;
                        if (!$row->save()) {
                            throw new \Exception('Не удалось сохранить для каталога ' . $product->cat_id . ' в таблице catalog_goods новую запись из catalog_base_goods ' . $product->id);
                        }
                    }
                }
            }
        }
    }
}
