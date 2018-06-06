<?php

namespace api_web\helpers;

/**
 *
 * @author elbabuino
 */
class Excel {

    /**
     * @param string $excelFile
     * 
     * @return array
     */
    public static function get20Rows($excelFile) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelFile);
        $worksheet = $spreadsheet->getActiveSheet();
        unset($objReader);
        unset($objPHPExcel);

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
            $rowsCount++;
            if ($rowsCount == 20) {
                return $rows;
            }
        }
        
        return $rows;
    }

    
    /**
     * @param string $excelFile
     * @param integer $tmpCatId
     * @param array $mapping
     */
    public static function writeToTempTable($excelFile, $tmpCatId, $mapping) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelFile);
        $worksheet = $spreadsheet->getActiveSheet();
        unset($objReader);
        unset($objPHPExcel);

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
