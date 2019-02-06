<?php
/**
 * Date: 06.02.2019
 * Author: Mike N.
 * Time: 12:16
 */

namespace api_web\classes;

use api_web\components\{Registry, WebApi};
use api_web\exceptions\ValidationException;
use api_web\helpers\WebApiHelper;
use common\models\{Catalog, Delivery, Organization, RelationSuppRest};
use yii\web\BadRequestHttpException;

class LazyVendorWebApi extends WebApi
{
    /**
     * Создание поставщика (Ленивого)
     *
     * @param $post
     * @return mixed
     * @throws \Exception
     */
    public function create($post)
    {
        $this->validateRequest($post, ['lazy-vendor']);
        $request = $post['lazy-vendor'];
        $this->validateRequest($request, ['name', 'address', 'email', 'phone', 'contact_name', 'inn', 'additional_params']);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $addParams = $request['additional_params'];
            $exists = Organization::find()->where([
                'name'    => $request['name'],
                'inn'     => $request['inn'],
                'type_id' => Organization::TYPE_LAZY_VENDOR
            ])->exists();
            if ($exists) {
                throw new BadRequestHttpException('vendor.exists');
            }
            /**
             * Создаем организацию
             */
            $model = new Organization();
            $model->name = $request['name'];
            $model->address = $request['address'];
            $model->email = $request['email'];
            $model->phone = $request['phone'];
            $model->contact_name = $request['contact_name'];
            $model->inn = $request['inn'];
            $model->type_id = Organization::TYPE_LAZY_VENDOR;
            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
            /**
             * Создаем каталог
             */
            $catalog = $this->createCatalog($model->id);
            /**
             * Создаем связь с каталогом
             */
            $this->createRelation($model->id, $catalog->id, $addParams['discount_product'] ?? 0);
            /**
             * Создаем запись в доставку поставщика
             */
            $delivery = Delivery::findOne(['vendor_id' => $model->id]);
            if (empty($delivery)) {
                $delivery = new Delivery();
                $delivery->vendor_id = $model->id;
                $delivery->delivery_charge = $addParams['delivery_price'] ?? 0;
                $delivery->delivery_discount_percent = $addParams['delivery_discount_percent'] ?? 0;
                $delivery->min_order_price = $addParams['min_order_price'] ?? 0;
                if (!empty($addParams['delivery_days'])) {
                    foreach ($addParams['delivery_days'] as $key => $value) {
                        $delivery->setAttribute($key, (int)$value);
                    }
                }
                if (!$delivery->save()) {
                    throw new ValidationException($delivery->getFirstErrors());
                }
            }
            $transaction->commit();
            return WebApiHelper::prepareOrganization($model);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Создание пустого каталога для ленивого поставщика
     *
     * @param $vendor_id
     * @return Catalog
     * @throws ValidationException
     */
    private function createCatalog($vendor_id)
    {
        $name = trim($this->user->organization->name) . '_LC';
        $catalog = Catalog::findOne(['supp_org_id' => $vendor_id, 'name' => $name]);
        if (!empty($catalog)) {
            return $catalog;
        }
        $model = new Catalog();
        $model->supp_org_id = $vendor_id;
        $model->currency_id = Registry::DEFAULT_CURRENCY_ID;
        $model->name = $name;
        $model->status = Catalog::STATUS_ON;
        $model->type = Catalog::CATALOG;
        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }
        return $model;
    }

    /**
     * Создание связи ресторана с поставщиком
     *
     * @param      $supp_org_id
     * @param null $cat_id
     * @param int  $discount_products
     * @return RelationSuppRest
     * @throws ValidationException
     */
    private function createRelation($supp_org_id, $cat_id = null, $discount_products = 0)
    {
        $rest_org_id = $this->user->organization->id;
        $relation = RelationSuppRest::findOne(['supp_org_id' => $supp_org_id, 'rest_org_id' => $rest_org_id]);
        if (empty($relation)) {
            $relation = new RelationSuppRest();
            $relation->rest_org_id = $rest_org_id;
            $relation->supp_org_id = $supp_org_id;
            $relation->discount_product = $discount_products;
            $relation->invite = RelationSuppRest::INVITE_OFF;
            $relation->cat_id = $cat_id;
            if (!$relation->save()) {
                throw new ValidationException($relation->getFirstErrors());
            }
        }
        return $relation;
    }
}
