<?php

namespace api_web\helpers;

/**
 * @author elbabuino
 */

use api_web\exceptions\ValidationException;
use common\models\CatalogTemp;
use common\models\Journal;
use PhpOffice\PhpSpreadsheet\IOFactory;
use common\models\CatalogTempContent;

/**
 * Class Excel
 *
 * @package api_web\helpers
 */
class Excel
{

    const excelTempFolder = "excelTemp";

    /**
     * @param string $excelFile
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public static function get20Rows($excelFile)
    {
        $spreadsheet = \PHPExcel_IOFactory::load($excelFile);

        $worksheet = $spreadsheet->getActiveSheet();

        $rows = [];
        $rowsCount = 0;
        $maxCellsCount = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = [];
            $i = 0;
            $emptyCellsCount = 0;
            foreach ($cellIterator as $cell) {
                if ($i > 5 && !$cell->getValue()) {
                    break;
                }
                $cellValue = trim(htmlspecialchars($cell->getValue(), ENT_QUOTES));
                $cells[] = $cellValue;
                $i++;
                if (!$cellValue) {
                    $emptyCellsCount++;
                }

            }
            if (count($cells) > $maxCellsCount) {
                $maxCellsCount = count($cells);
            }
            if (count($cells) != $emptyCellsCount) {
                $rows[] = $cells;
            }
            $rowsCount++;
            if ($rowsCount == 20) {
                /**
                 * Заполняем каждый элемент массива до кол-ва, равному наибольшей длинне его элементов
                 */
                return array_map(function ($el) use ($maxCellsCount) {
                    if (count($el) < $maxCellsCount) {
                        end($el);
                        $fillForFront = array_fill(key($el), $maxCellsCount - count($el), '');
                        return array_merge($el, $fillForFront);
                    }
                    return $el;
                }, $rows);
            }
        }

        return $rows;
    }

    /**
     * @param \common\models\CatalogTemp $tempCatalog
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function get20RowsFromTempUploaded($tempCatalog)
    {
        if (empty($tempCatalog)) {
            return [];
        }
        $url = \Yii::$app->get('resourceManager')->getUrl(self::excelTempFolder . DIRECTORY_SEPARATOR . $tempCatalog->excel_file);
        $file = File::getFromUrl($url);
        return self::get20Rows($file->tempName);
    }

    /**
     * @param string  $excelFile
     * @param integer $tmpCatId
     * @param array   $mapping ['article', 'price', 'units', 'note', 'ed', 'product', 'other'] - в указанном при
     *                         добавлении каталога порядке
     * @param string  $index
     * @throws \Exception
     * @return bool
     */
    public static function writeToTempTable($excelFile, $tmpCatId, $mapping, $index = 'article')
    {
        $spreadsheet = IOFactory::load($excelFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = [];
        $allSkipCount = 0;
        $edSkipCount = 0;
        $tempCatalogModel = CatalogTemp::findOne($tmpCatId);
        foreach ($worksheet->getRowIterator() as $row) {
            $cellsCount = 1;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $attributes = [];
            $attributes['temp_id'] = $tmpCatId;
            $write = true;
            foreach ($cellIterator as $cell) {
                if ($cellsCount > max(array_flip($mapping))) {
                    break;
                }
                if (!array_key_exists($cellsCount, $mapping)) {
                    $cellsCount++;
                    continue;
                }
                $value = trim($cell->getValue());
                if ($value == 'Наименование'){
                    $_ = empty(false);
                }
                if ($mapping[$cellsCount] == 'article' && $value == 'Артикул') {
                    $write = false;
                    break;
                }

                if ($mapping[$cellsCount] == 'price') {
                    $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
                    if (empty($value) || !is_numeric($value)){
                        $write = false;
                        break;
                    }
                }

                if (in_array($mapping[$cellsCount], [$index, 'ed', 'product']) && empty($value)) {
                    if ($mapping[$cellsCount] == 'ed') {
                        $edSkipCount++;
                    }
                    $write = false;
                    break;
                }

                if ($mapping[$cellsCount] == 'units' && !empty($value)) {
                    $value = (float)(str_replace(',', '.', $value));
                }

                $attributes[$mapping[$cellsCount]] = $value;
                $cellsCount++;
            }

            if (!array_search('units', $mapping) || empty($attributes['units'])) {
                $attributes['units'] = 0;
            }

            if ($write === true) {
                $rows[] = $attributes;
            } else {
                $allSkipCount++;
            }
        }

        self::writeInJournal(date('Y-m-d H:i:s') . " подготовка к загрузке прайс-листа $excelFile:" . PHP_EOL . "- Пропущено строк с пустым Артикулом, Названием или Ценой: $allSkipCount" . PHP_EOL .
            "- Пропущено строк с пустой единицей измерения: $edSkipCount", $tempCatalogModel->user_id, '1');

        if (empty($rows)) {
            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            CatalogTempContent::deleteAll(['temp_id' => $tmpCatId]);
            $attributes = array_keys($rows[0]);
            $count = \Yii::$app->db->createCommand()->batchInsert(CatalogTempContent::tableName(), $attributes, $rows)->execute();
            self::writeInJournal(date('Y-m-d H:i:s') . " результат загрузки прайс-листа $excelFile:" . PHP_EOL . "- В каталог загружено товаров: $count", $tempCatalogModel->user_id, '2');
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * Запись в журнал
     *
     * @param $message
     * @param $userId
     * @param $code
     * @throws ValidationException
     */
    public static function writeInJournal($message, $userId, $code): void
    {
        $journal = new Journal();
        $journal->response = is_array($message) ? json_encode($message) : $message;
        $journal->service_id = 9;
        $journal->type = 'success';
        $journal->log_guide = 'UploadExcel';
        $journal->user_id = $userId;
        $journal->operation_code = $code;
        if (!$journal->save()) {
            throw new ValidationException($journal->getFirstErrors());
        }
    }

}
