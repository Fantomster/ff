<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 15/11/2018
 * Time: 11:29
 */

namespace api_web\modules\integration\classes;

use api_web\exceptions\ValidationException;
use common\models\OuterProduct;
use common\models\OuterProductMap;
use common\models\OuterStore;
use yii\web\BadRequestHttpException;

/**
 * Class OuterProductMapper
 *
 * @package api_web\modules\integration\classes
 */
class OuterProductMapper
{

    /**
     * @var bool
     */
    private $isMainOrg = false;

    /**
     * @var null|string
     */
    public $mainOrgId = null;

    /**
     * @var array|null
     */
    public $childs = null;

    /**
     * @var int
     */
    private $orgId;

    /**
     * @var
     */
    private $request;

    /**
     * @var int
     */
    private $serviceId;

    /**
     * OuterProductMapper constructor.
     *
     * @param int $orgId
     * @param int $serviceId
     */
    public function __construct(int $orgId, int $serviceId)
    {
        $this->orgId = $orgId;
        $this->mainOrgId = OuterProductMap::getMainOrg($orgId);
        $this->childs = OuterProductMap::getChildOrgsId($this->orgId);
        $this->serviceId = $serviceId;

        if (!$this->mainOrgId) {
            $this->isMainOrg = true;
        }
        if (!empty($this->childs)) {
            $this->childs[] = $this->orgId;
        }
    }

    /**
     * @param $outerProductId
     * @return bool
     */
    public function productExists($outerProductId)
    {
        return OuterProduct::find()
            ->where(['id' => $outerProductId])
            ->andWhere(['org_id' => $this->orgId])
            ->andWhere(['service_id' => $this->serviceId])
            ->exists();
    }

    /**
     * @param $outerStoreId
     * @return bool
     */
    public function storeExists($outerStoreId)
    {
        return OuterStore::find()
            ->where(['id' => $outerStoreId])
            ->andWhere(['org_id' => $this->orgId])
            ->andWhere(['service_id' => $this->serviceId])
            ->exists();
    }

    /**
     * @param $request
     * @throws BadRequestHttpException
     */
    public function loadRequest($request): void
    {
        foreach ($request as $key => $value) {
            if ((!empty($value) || $value == 0) && !is_null($value)) {
                $this->request[$key] = $value;

                if ($key == 'outer_product_id') {
                    if ($this->isMainOrg) {
                        if (!$this->productExists($value)) {
                            throw new BadRequestHttpException('outer product not found');
                        }
                    }
                }
                //Проверяем что сопоставляемый склад связан с нашей организацией
                if ($key == 'outer_store_id') {
                    if (!$this->storeExists($value)) {
                        throw new BadRequestHttpException('store.not_found');
                    }
                    $store = OuterStore::findOne($value);
                    //Если это категория а не склад
                    if (!$store->isLeaf()) {
                        throw new BadRequestHttpException('store.is_category');
                    }
                }
            }
        }
    }

    /**
     * Если меняется сопоставление с продуктом, и бизнес главный и есть дочерние бизнесы, то обновляем сопоставление в
     * их записях
     * todo_refactoring кажется тут есть баг при обновлении, потому что надо еще учитывать vendor_id
     */
    public function updateChildesMap(): void
    {
        if (!empty($this->childs) && $this->isMainOrg) {
            $condition = [
                'and',
                ['service_id' => $this->serviceId],
                ['product_id' => $this->request['product_id']],
                ['in', 'organization_id', $this->childs]
            ];

            OuterProductMap::updateAll(['outer_product_id' => $this->request['outer_product_id']], $condition);
        }
    }

    /**
     * Ищем запись для редактирования
     *
     * @param int $orgId
     * @return OuterProductMap|null
     */
    private function getModel($orgId)
    {
        return OuterProductMap::findOne([
            'product_id'      => $this->request['product_id'],
            'service_id'      => $this->serviceId,
            'organization_id' => $orgId,
        ]);
    }

    /**
     * @throws BadRequestHttpException
     * @throws ValidationException
     */
    public function updateModel()
    {
        $mainOrgModel = null;
        $model = $this->getModel($this->orgId);
        if (!$model && !$this->isMainOrg) {
            $mainOrgModel = $this->getModel($this->mainOrgId);
        }
        if ($mainOrgModel) {
            if (isset($this->request['outer_product_id'])) {
                unset($this->request['outer_product_id']);
            }
            $model = new OuterProductMap();
            $model->service_id = $mainOrgModel->service_id;
            $model->organization_id = $this->orgId;
            $model->vendor_id = $mainOrgModel->vendor_id;
            $model->product_id = $mainOrgModel->product_id;
            $model->outer_product_id = $mainOrgModel->outer_product_id;
            $model->outer_unit_id = $mainOrgModel->outer_unit_id;
            $model->outer_store_id = $mainOrgModel->outer_store_id;
            $model->coefficient = $mainOrgModel->coefficient;
            $model->vat = $mainOrgModel->vat;
        }
        if (!$model) {
            $model = new OuterProductMap();
            $model->service_id = $this->serviceId;
            $model->organization_id = $this->orgId;

        }

        $model->attributes = $this->request;

        if ($model->outerProduct) {
            $model->outer_unit_id = $model->outerProduct->outer_unit_id;
        }

        if (!$model->product) {
            throw new BadRequestHttpException('product_not_found');
        }
        $model->vendor_id = $model->product->supp_org_id;
        if (!$model->save()) {
            throw new ValidationException($model->getFirstErrors());
        }
    }

}

