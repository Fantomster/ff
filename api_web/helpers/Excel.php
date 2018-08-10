<?php

namespace api_web\helpers;

/**
 *
 * @author elbabuino
 */
use PhpOffice\PhpSpreadsheet\IOFactory;

class Excel
{

    const excelTempFolder = "excelTemp";

    /**
     * @param string $excelFile
     *
     * @return array
     */
    public static function get20Rows($excelFile)
    {
        $spreadsheet = IOFactory::load($excelFile);
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = [];
        $rowsCount = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = htmlspecialchars($cell->getValue(), ENT_QUOTES);
            }
            $rows[] = $cells;
            $rowsCount++;
            if ($rowsCount == 20) {
                return $rows;
            }
        }

        return $rows;
    }

    /**
     *
     * @param \common\models\CatalogTemp $tempCatalog
     * @return array
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
     * @param string $excelFile
     * @param integer $tmpCatId
     * @param array $mapping
     */
    public static function writeToTempTable($excelFile, $tmpCatId, $mapping)
    {
        $spreadsheet = IOFactory::load($excelFile);
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = [];
        $rowsCount = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }
            $rows[] = $cells;
        }
    }

}
