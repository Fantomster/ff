<?php

namespace api_web\classes;

use Yii;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use yii\web\BadRequestHttpException;
use api_web\components\WebApi;

/**
 * Class CatalogWebApi
 * @package api_web\classes
 */
class CatalogWebApi extends WebApi
{

    /**
     * Смена уникального индекса главного каталога
     * @param Catalog $catalog
     * @param integer $index
     * @return array
     * @throws BadRequestHttpException
     */
    public function changeMainIndex($catalog, $index)
    {
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
    public function deleteMainCatalog($catalog)
    {
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


    /**
     * Список ключей
     * @return array
     */
    public function getKeys()
    {
        return [
            'product' => Yii::t('api_web', 'api_web.catalog.key.product', ['ru' => 'Нименование товара']),
            'article' => Yii::t('api_web', 'api_web.catalog.key.article', ['ru' => 'Артикул']),
            'other' => Yii::t('api_web', 'api_web.catalog.key.other', ['ru' => 'Другое']),
        ];
    }
}
