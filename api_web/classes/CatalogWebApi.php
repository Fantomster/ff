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
            $catalog->main_index = $index;
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
     * Обновление главного каталога
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function updateMainCatalog($request)
    {
        if (empty($request['cat_id'])) {
            throw new BadRequestHttpException("empty_param|cat_id");
        }

        $catalogTemp = CatalogTemp::findOne(['cat_id' => (int)$request['cat_id'], 'user_id' => $this->user->id]);
        if (empty($catalogTemp)) {
            throw new BadRequestHttpException("catalog_temp_not_found");
        }

        $catalogTempContent = CatalogTempContent::find()->where(['temp_id' => $catalogTemp->id])->all();
        if (empty($catalogTempContent)) {
            throw new BadRequestHttpException("catalog_temp_content_not_found");
        }

        $catalog = Catalog::findOne(['id' => $catalogTemp->cat_id, 'type' => Catalog::BASE_CATALOG]);
        if (empty($catalog)) {
            throw new BadRequestHttpException("base_catalog_not_found");
        }

        if (!empty($this->getTempDuplicatePosition(['cat_id' => $catalog->id]))) {
            throw new BadRequestHttpException("catalog_temp_exists_duplicate");
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            CatalogBaseGoods::updateAll([
                'status' => CatalogBaseGoods::STATUS_OFF
            ], [
                'supp_org_id' => $catalog->supp_org_id,
                'cat_id' => $catalog->id
            ]);
            /**
             * @var $tempRow CatalogTempContent
             */
            foreach ($catalogTempContent as $tempRow) {
                //Поиск товара в главном каталоге
                $model = CatalogBaseGoods::findOne([
                    'cat_id' => $catalog->id,
                    'article' => $tempRow->article,
                    'product' => $tempRow->product
                ]);
                //Если не нашли, создаем его
                if (empty($model)) {
                    $model = new CatalogBaseGoods([
                        'cat_id' => $catalog->id,
                        'article' => $tempRow->article,
                        'product' => $tempRow->product,
                        'supp_org_id' => $catalog->supp_org_id
                    ]);
                }
                //Заполняем аттрибуты
                $model->ed = $tempRow->ed;
                $model->units = $tempRow->units;
                $model->price = $tempRow->price;
                $model->note = $tempRow->note;
                $model->status = CatalogBaseGoods::STATUS_ON;
                //Если атрибуты изменились или новая запись, сохраняем модель
                $model->save();
            }
            //Убиваем временный каталог
            CatalogTempContent::deleteAll(['temp_id' => $catalogTemp->id]);
            $catalogTemp->delete();
            $transaction->commit();
            return ['result' => true];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
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
     * Удаление из временного каталога
     * @param $request
     * @return array
     * @throws BadRequestHttpException
     */
    public function deletePositionTempCatalog($request)
    {
        if (empty($request['temp_id'])) {
            throw new BadRequestHttpException("empty_param|temp_id");
        }

        if (empty($request['position_id'])) {
            throw new BadRequestHttpException("empty_param|position_id");
        }

        $model = CatalogTempContent::findOne(['temp_id' => (int)$request['temp_id'], 'id' => (int)$request['position_id']]);
        if (empty($model)) {
            throw new BadRequestHttpException("model_not_found");
        }

        if (!$model->delete()) {
            throw new BadRequestHttpException('Model not delete!!!');
        }

        return ['result' => true];
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
            ->orderBy("T1.{$index}")
            ->all();

        $result = [];

        //Если не нашли дублей
        if (empty($content)) {
            return $result;
        }

        foreach ($content as $row) {
            $result[$row[$index]][] = $this->prepareTempDuplicatePosition($row);
        }

        return $result;
    }

    /**
     * Автоматическое удаление дублей из загружаемого каталога
     * @param array $request
     * @return array
     * @throws \Exception
     */
    public function autoClearTempDuplicatePosition(array $request)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //ID каталога темпового
            $temp_id = null;
            //Список id позиций которые будем удалять
            $ids = [];
            //Получаем список дублей
            $doubles = $this->getTempDuplicatePosition($request);
            foreach ($doubles as &$positions) {
                while (count($positions) > 1) {
                    $position = array_pop($positions);
                    //Один раз запоминаем id темпового каталога
                    if ($temp_id === null) {
                        $temp_id = $position['temp_id'];
                    }
                    //Собираем id дублей которые удалим
                    $ids[] = $position['id'];
                }
            }
            //Удаляем все
            if ($temp_id !== null && !empty($ids)) {
                CatalogTempContent::deleteAll(['AND',
                    'temp_id' => $temp_id,
                    ['in', 'id', array_values($ids)]
                ]);
            }
            //Все ок!
            $transaction->commit();
            return ['result' => true];
        } catch (\Exception $e) {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @param array $row
     * @return array
     */
    private function prepareTempDuplicatePosition(array $row)
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
            "CountOf" => (int)$row['CountOf'] ?? 1
        ];
    }
}
