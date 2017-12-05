<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\CatalogBaseGoods;
use yii\helpers\HtmlPurifier;

class ImportController extends Controller {

    public function actionTest($id, $path) {
        echo "$id: $path\n";
    }

    public function actionBase($id, $importType, $path) {
        set_time_limit(0);
        $time_start = microtime(true);
        $vendor = \common\models\Catalog::find()->where([
                            'id' => $id,
                            'type' => \common\models\Catalog::BASE_CATALOG
                        ])
                        ->one()
                ->vendor;
        $sql_array_products = CatalogBaseGoods::find()->select(['id', 'product'])->where(['cat_id' => $id, 'deleted' => 0])->asArray()->all();
        $arr = array_map('mb_strtolower', \yii\helpers\ArrayHelper::map($sql_array_products, 'id', 'product'));
        $localFile = \PHPExcel_IOFactory::identify($path);
        $objReader = \PHPExcel_IOFactory::createReader($localFile);
        $objPHPExcel = $objReader->load($path);

        $worksheet = $objPHPExcel->getSheet(0);
        $highestRow = $worksheet->getHighestRow(); // получаем количество строк
        $newRows = 0;
        $xlsArray = [];
        //Проверяем наличие дублей в списке
        if ($importType == 2 || $importType == 3) {
            $rP = 0;
        } else {
            $rP = 1;
        }
        for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
            $row_unique = mb_strtolower(trim($worksheet->getCellByColumnAndRow($rP, $row))); //наименование
            if (!empty($row_unique)) {
                if (!in_array($row_unique, $arr)) {
                    $newRows++;
                }
//                if (!isset($arr[$row_unique])) {
//                    $newRows++;
//                }
                array_push($xlsArray, (string) $row_unique);
            }
        }

        if ($importType == 1) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $data_insert = [];
                for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
//                    $row_article = HtmlPurifier::process(trim($worksheet->getCellByColumnAndRow(0, $row))); //артикул
//                    $row_product = HtmlPurifier::process(trim($worksheet->getCellByColumnAndRow(1, $row))); //наименование
//                    $row_units = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(2, $row))); //количество
//                    $row_price = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(3, $row))); //цена
//                    $row_ed = HtmlPurifier::process(trim($worksheet->getCellByColumnAndRow(4, $row))); //единица измерения
//                    $row_note = HtmlPurifier::process(trim($worksheet->getCellByColumnAndRow(5, $row)));  //Комментарий
                    $row_article = strip_tags(trim($worksheet->getCellByColumnAndRow(0, $row))); //артикул
                    $row_product = strip_tags(trim($worksheet->getCellByColumnAndRow(1, $row))); //наименование
                    $row_units = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(2, $row))); //количество
                    $row_price = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(3, $row))); //цена
                    $row_ed = strip_tags(trim($worksheet->getCellByColumnAndRow(4, $row))); //единица измерения
                    $row_note = strip_tags(trim($worksheet->getCellByColumnAndRow(5, $row)));  //Комментарий
                    if (!empty($row_product && $row_price && $row_ed)) {
                        if (empty($row_units) || $row_units < 0) {
                            $row_units = 0;
                        }
                        if (!in_array(mb_strtolower($row_product), $arr)) {
                       // if (!isset($arr[mb_strtolower($row_product)])) {
                            $data_insert[] = [
                                $id,
                                $vendor->id,
                                $row_article,
                                $row_product,
                                $row_units,
                                $row_price,
                                $row_ed,
                                $row_note,
                                CatalogBaseGoods::STATUS_ON
                            ];
                        }
                    }
                }
                if (!empty($data_insert)) {
                    $db = Yii::$app->db;
                    $sql = $db->queryBuilder->batchInsert(CatalogBaseGoods::tableName(), [
                        'cat_id', 'supp_org_id', 'article', 'product', 'units', 'price', 'ed', 'note', 'status'
                            ], $data_insert);
                    Yii::$app->db->createCommand($sql)->execute();
                }
                $transaction->commit();
                unlink($path);
            } catch (Exception $e) {
                unlink($path);
                $transaction->rollback();
            }
        }
        if ($importType == 2) {
            $data_update = "";
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $cbgTable = CatalogBaseGoods::tableName();
                for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                    $row_product = HtmlPurifier::process(trim($worksheet->getCellByColumnAndRow(0, $row))); //наименование
                    $row_price = floatval(preg_replace("/[^-0-9\.]/", "", $worksheet->getCellByColumnAndRow(1, $row))); //цена
                    if (!empty($row_product && $row_price)) {
                        if (empty($row_units) || $row_units < 0) {
                            $row_units = 0;
                        }
                        $cbg_id = array_search(mb_strtolower($row_product), $arr);
                        if ($cbg_id) {
                            $data_update .= "UPDATE $cbgTable set 
                                    `price` = $row_price
                                     where cat_id=$id and id=$cbg_id;";
                        }
                    }
                }
                if (!empty($data_update)) {
                    Yii::$app->db->createCommand($data_update)->execute();
                }
                $transaction->commit();
                unlink($path);
            } catch (Exception $e) {
                unlink($path);
                $transaction->rollback();
            }
        }
        if ($importType == 3) {
            $data_update = "";
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $cbgTable = CatalogBaseGoods::tableName();
                for ($row = 1; $row <= $highestRow; ++$row) { // обходим все строки
                    $row_product = HtmlPurifier::process(trim($worksheet->getCellByColumnAndRow(0, $row))); //наименование
                    if (!empty($row_product)) {
                        if (empty($row_units) || $row_units < 0) {
                            $row_units = 0;
                        }
                        $cbg_id = array_search(mb_strtolower($row_product), $arr);
                        if ($cbg_id) {
                            $data_update .= "UPDATE $cbgTable set 
                                    `market_place` = 1,
                                    `mp_show_price` = 1,
                                    `es_status` = 1
                                     where cat_id=$id and id='$cbg_id'"
                                    . " and `ed` is not null and `category_id` is not null;";
                        }
                    }
                }
                if (!empty($data_update)) {
                    Yii::$app->db->createCommand($data_update)->execute();
                }
                $transaction->commit();
                unlink($path);
            } catch (Exception $e) {
                unlink($path);
                $transaction->rollback();
            }
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo "Process Time: {$time}\n";
    }

}
