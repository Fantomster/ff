<?php

namespace api_web\classes;

use common\models\CatalogTemp;
use common\models\CatalogTempContent;
use Yii;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use yii\db\Query;
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

    /**
     * Поиск дублей в темповом каталоге
     * @param array $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function getTempDuplicatePosition(array $request)
    {
        if (empty($request['cat_id'])) {
            throw new BadRequestHttpException('empty_param|cat_id');
        }

        $catalogTemp = CatalogTemp::findOne(['cat_id' => $request['cat_id'], 'user_id' => $this->user->id]);
        if (empty($catalogTemp)) {
            throw new BadRequestHttpException('catalog_temp_not_found');
        }

        if (empty($catalogTemp->index_column)) {
            throw new BadRequestHttpException('Для каталога не назначен ключ');
        }

        //Ключ, по которому ищем дубли
        $index = $catalogTemp->index_column;

        //Готовим джойн, по которому будем выцеплять дубли
        $innerJoin = (new Query())
            ->select([$index, 'COUNT(*) AS CountOf'])
            ->from(CatalogTempContent::tableName())
            ->where(['temp_id' => $catalogTemp->id])
            ->groupBy($index)
            ->having("CountOf > 1")
            ->createCommand()
            ->getRawSql();

        /**
         * Сам запрос на поиск дублей
         * можно вывести запрос, если вместо ->all() сделать
         * ->createCommand()->getRawSql();
         * и распечатать результат в $content
         */
        $content = (new Query())
            ->select('*')
            ->from(CatalogTempContent::tableName() . " as T1")
            ->where(["T1.temp_id" => $catalogTemp->id])
            ->innerJoin("({$innerJoin}) as T2", "T1.{$index} = T2.{$index}")
            ->all();

        $result = [];

        //Если не нашли дублей
        if (empty($content)) {
            return $result;
        }

        foreach ($content as $row) {
            $result[$row[$catalogTemp->index_column]][] = $this->prepareTempPosition($row);
        }

        return $result;
    }

    /**
     * @param array $row
     * @return array
     */
    private function prepareTempPosition(array $row)
    {
        return [
            "id" => (int)$row['id'],
            "temp_id" => (int)$row['temp_id'],
            "article" => $row['article'],
            "product" => $row['product'],
            "price" => round($row['price'], 2),
            "units" => round($row['units'], 3),
            "note" => $row['note'],
            "ed" => $row['ed'],
            "CountOf" => (int)$row['CountOf']
        ];
    }
}
