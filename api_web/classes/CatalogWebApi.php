<?php

namespace api_web\classes;

use common\models\Catalog;
use yii\web\HttpException;
use common\models\CatalogGoods;
use common\models\Organization;
use common\models\RelationSuppRest;
use common\models\CatalogBaseGoods;
use yii\web\BadRequestHttpException;
use api_web\exceptions\ValidationException;
use api_web\components\WebApi;

/**
 * Class CatalogWebApi
 * @package api_web\classes
 */
class CatalogWebApi extends WebApi {

    /**
     * Смена уникального индекса главного каталога
     * @param Catalog $catalog
     * @param integer $index
     * @return array
     * @throws BadRequestHttpException
     */
    public function changeMainIndex($catalog, $index) {
        $isEmpty = !CatalogBaseGoods::find()->where(['cat_id' => $catalog->id, 'deleted' => false])->exists();
        if ($isEmpty) {
            $catalog->index_column = $index;
            $catalog->save();
            return [
                'result' => true
            ];
        } else {
            throw new BadRequestHttpException('Catalog not empty');
        }
    }

    /**
     * Удаление главного каталога
     * @param Catalog $catalog
     * @return array
     * @throws BadRequestHttpException
     */
    public function deleteMainCatalog($catalog) {
        $isEmpty = !CatalogBaseGoods::find()->where(['cat_id' => $catalog->id, 'deleted' => false])->exists();
        if ($isEmpty) {
            throw new BadRequestHttpException('Catalog is empty');
        } else {
            if ($catalog->deleteAllProducts()) {
                return [
                    'result' => true
                ];
            } else {
                throw new BadRequestHttpException('Deleting failed');
            }
        }
    }

}
