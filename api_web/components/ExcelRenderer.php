<?php

namespace api_web\components;

use common\models\Order;
use yii\web\BadRequestHttpException;
use yii\helpers\Html;

/**
 * Class Notice
 *
 * @package api_web\components
 */
class ExcelRenderer
{
    /**
     * Генерация excel файла заказа
     *
     * @param $order_id
     * @return \PHPExcel
     * @throws BadRequestHttpException
     * @throws \PHPExcel_Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function OrderRender($order_id)
    {
        $order = Order::findOne(['id' => $order_id]);
        if (empty($order)) {
            throw new BadRequestHttpException('order_not_found');
        }

        $styleArray = [
            'borders' => [
                'allborders' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ];

        $width = 30;
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("MixCart")
            ->setLastModifiedBy("MixCart")
            ->setTitle("mixcart_order_" . $order->id);

        $sheet = 0;
        $objPHPExcel->setActiveSheetIndex($sheet);
        $activeSheet = $objPHPExcel->getActiveSheet();

        $activeSheet->getColumnDimension('A')->setWidth(8);
        $activeSheet->getColumnDimension('B')->setWidth($width);
        $activeSheet->getColumnDimension('C')->setWidth($width);
        $activeSheet->getColumnDimension('D')->setWidth(10);
        $activeSheet->getColumnDimension('E')->setWidth(20);
        $activeSheet->getColumnDimension('F')->setWidth(10);
        $activeSheet->getColumnDimension('G')->setWidth(20);
        $activeSheet->getColumnDimension('H')->setWidth(20);

        $activeSheet->mergeCells('A1:H1');
        $activeSheet->setTitle(\Yii::t('message', 'frontend.controllers.order.rep', ['ru' => 'отчет']))
            ->setCellValue('A1', \Yii::t('message', 'frontend.views.order.order_number', ['ru' => 'Заказ №']) . " " . $order->id);
        $activeSheet->getStyle('A1:H1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $activeSheet->getRowDimension(1)->setRowHeight(25);

        $activeSheet->mergeCells('A2:H2');
        $activeSheet->setCellValue('A2', \Yii::t('app', 'от') . " " . \Yii::$app->formatter->asDate($order->created_at, "dd.MM.yyyy, HH:mm"));
        $activeSheet->getStyle('A2:H2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);;
        $activeSheet->getRowDimension(2)->setRowHeight(18);

        $requestedDelivery = isset($order->requested_delivery) ? " " . \Yii::$app->formatter->asDate($order->requested_delivery, 'dd.MM.yyyy') . " " . \Yii::t('app', 'frontend.excel.year') : "";
        $activeSheet->mergeCells('A3:H3');
        $activeSheet->setCellValue('A3', \Yii::t('app', 'common.mail.bill.delivery_date') . $requestedDelivery);
        $activeSheet->getStyle('A3:H3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $activeSheet->getRowDimension(3)->setRowHeight(18);

        $activeSheet->getRowDimension(4)->setRowHeight(5);
        $activeSheet->getStyle('A5:H5')->getBorders()
            ->getTop()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $activeSheet->mergeCells('A6:D6');
        $activeSheet->setCellValue('A6', \Yii::t('message', 'frontend.views.order.customer'));
        $activeSheet->getStyle('A6:D6')->applyFromArray(['font' => ['bold' => true]]);
        $activeSheet->mergeCells('E6:H6');
        $activeSheet->setCellValue('E6', \Yii::t('app', 'Поставщик'));
        $activeSheet->getStyle('E6:H6')->applyFromArray(['font' => ['bold' => true]]);
        $activeSheet->getRowDimension(6)->setRowHeight(22);

        $clientName = (!empty($order->client->legal_entity)) ? $order->client->name . " (" . $order->client->legal_entity . ")" : $order->client->name;
        $vendorName = (!empty($order->vendor->legal_entity)) ? $order->vendor->name . " (" . $order->vendor->legal_entity . ")" : $order->vendor->name;
        $activeSheet->mergeCells('A7:D7');
        $activeSheet->setCellValue('A7', $clientName);
        $activeSheet->getStyle('A7:D7')->applyFromArray(['font' => ['bold' => true]])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        $activeSheet->mergeCells('E7:H7');
        $activeSheet->setCellValue('E7', $vendorName);
        $activeSheet->getStyle('E7:H7')->applyFromArray(['font' => ['bold' => true]])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        $activeSheet->getRowDimension(7)->setRowHeight(25);

        $acceptedName = isset($order->acceptedBy->profile->full_name) ? $order->acceptedBy->profile->full_name : '';
        $this->fillCellData($objPHPExcel, 8, \Yii::t('message', 'frontend.views.order.phone_four') . " " . $order->client->phone, \Yii::t('message', 'frontend.views.order.phone_four') . " " . $order->vendor->phone);
        $this->fillCellData($objPHPExcel, 9, 'E-mail: ' . $order->client->email, 'E-mail: ' . $order->vendor->email);
        $this->fillCellData($objPHPExcel, 10, \Yii::t('app', 'Заказ создал:') . " " . $order->createdBy->profile->full_name, \Yii::t('app', 'Заказ принял:') . " " . $acceptedName);
        $this->fillCellData($objPHPExcel, 11, \Yii::t('message', 'market.views.site.supplier.address') . " " . $order->client->locality . " " . $order->client->address, \Yii::t('message', 'market.views.site.supplier.address') . " " . $order->vendor->locality . " " . $order->vendor->address);
        $activeSheet->getStyle('A11')->getAlignment()->setWrapText(true);
        $activeSheet->getStyle('D11')->getAlignment()->setWrapText(true);
        $activeSheet->getRowDimension(11)->setRowHeight(50);

        $activeSheet->getStyle('A13:H13')->getBorders()
            ->getTop()
            ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $activeSheet->setCellValue('A14', \Yii::t('app', 'Комментарий к заказу:'));
        $activeSheet->getStyle('A14')->applyFromArray(['font' => ['bold' => true]]);
        $activeSheet->mergeCells('A15:H15');
        $activeSheet->setCellValue('A15', $order->comment);
        $activeSheet->getStyle('A15')->getAlignment()->setWrapText(true);
        $activeSheet->getRowDimension(14)->setRowHeight(20);
        $activeSheet->getRowDimension(15)->setRowHeight(30);

        $activeSheet->setCellValue("G17", \Yii::t('message', 'frontend.views.order.grid_price') . " " . $order->currency->iso_code);

        $row = 18;
        $goods = $order->orderContent;
        $this->fillOrderContentRows($objPHPExcel, $goods, $row, $styleArray, $width);

        $row += 2;
        $row = $this->fillCellBottomData($objPHPExcel, $row, \Yii::t('app', 'Скидка:'), " " . $order->getFormattedDiscount());
        $row = $this->fillCellBottomData($objPHPExcel, $row, \Yii::t('app', 'Стоимость доставки:'), " " . $order->calculateDelivery() . " " . $order->currency->iso_code);
        $row = $this->fillCellBottomData($objPHPExcel, $row, \Yii::t('app', 'Итого:'), " " . $order->getTotalPriceWithOutDiscount() . " " . $order->currency->iso_code);
        $this->fillCellBottomData($objPHPExcel, $row, \Yii::t('message', 'frontend.views.order.total_price_all'), " " . $order->total_price . " " . $order->currency->iso_code, true);

        $activeSheet->getSheetView()->setZoomScale(70);
        // Set Orientation, size and scaling
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $activeSheet->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $activeSheet->getPageSetup()->setFitToPage(true);
        $activeSheet->getPageSetup()->setFitToWidth(1);
        $activeSheet->getPageSetup()->setFitToHeight(0);

        return $objPHPExcel;

    }

    /**
     * Заполение содержимого заказа
     *
     * @param \PHPExcel $objPHPExcel
     * @param array     $goods
     * @param int       $row
     * @param array     $styleArray
     * @param int       $width
     * @throws \PHPExcel_Exception
     */
    private function fillOrderContentRows(\PHPExcel $objPHPExcel, array $goods, int &$row, array $styleArray, int $width)
    {
        $activeSheet = $objPHPExcel->getActiveSheet();

        $activeSheet->getStyle('B17')->getAlignment()->setWrapText(true);
        $activeSheet->getStyle('C17')->getAlignment()->setWrapText(true);

        $this->fillCellHeaderData($objPHPExcel, 'A', '№ п/п');
        $this->fillCellHeaderData($objPHPExcel, 'B', 'Наименование товара');
        $this->fillCellHeaderData($objPHPExcel, 'C', 'Комментарий');
        $this->fillCellHeaderData($objPHPExcel, 'D', 'Артикул');
        $this->fillCellHeaderData($objPHPExcel, 'E', 'Ед. измерения');
        $this->fillCellHeaderData($objPHPExcel, 'F', 'Кол-во');

        $activeSheet->getStyle('A17:H17')->applyFromArray($styleArray);
        $activeSheet->getStyle("G17")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $activeSheet->getStyle("G17")->applyFromArray(['font' => ['bold' => true]])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $this->fillCellHeaderData($objPHPExcel, 'H', 'frontend.widgets.cart.views.sum_two');

        $activeSheet->getRowDimension(17)->setRowHeight(25);

        $i = 0;
        foreach ($goods as $good) {
            $i++;
            $activeSheet->getRowDimension($row)->setRowHeight(-1);
            $activeSheet->setCellValue("A$row", ($row - 17));
            $activeSheet->getStyle("A$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $activeSheet->setCellValue('B' . $row, Html::decode($good->product_name));
            $activeSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);

            $activeSheet->setCellValue('C' . $row, Html::decode($good->comment));
            $activeSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $activeSheet->getStyle("C$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $activeSheet->setCellValueExplicit('D' . $row, $good->article, \PHPExcel_Cell_DataType::TYPE_STRING);
            $activeSheet->getStyle("D$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $activeSheet->setCellValue('E' . $row, \Yii::t('app', $good->product->ed));
            $activeSheet->getStyle("E$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $activeSheet->setCellValueExplicit('F' . $row, number_format($good->quantity, 3, '.', ''), \PHPExcel_Cell_DataType::TYPE_STRING);
            $activeSheet->getStyle("F$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $activeSheet->setCellValueExplicit('G' . $row, number_format($good->price, 2, '.', ''), \PHPExcel_Cell_DataType::TYPE_STRING);
            $activeSheet->getStyle("G$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $activeSheet->setCellValueExplicit('H' . $row, number_format($good->quantity * $good->price, 2, '.', ''), \PHPExcel_Cell_DataType::TYPE_STRING);
            $activeSheet->getStyle("H$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $activeSheet->getStyle("A$row:H$row")->applyFromArray($styleArray);

            $activeSheet->getStyle("B$row")->getAlignment()->setWrapText(true);
            $activeSheet->getStyle("C$row")->getAlignment()->setWrapText(true);

            $height = 19;
            $product_name_length = mb_strlen($good->product_name);
            $comment_length = mb_strlen($good->comment);
            if ($product_name_length > $width || $comment_length > $width) {
                if ($product_name_length > $comment_length) {
                    $i = ceil((float)$product_name_length / $width);
                } else {
                    $i = ceil((float)$comment_length / $width);
                }
                $height *= $i;
            }
            $activeSheet->getRowDimension($row)->setRowHeight($height);
            $row++;
        }

        $activeSheet->getStyle("A1:H$row")->applyFromArray(['font' => ['size' => 11]]);
        $activeSheet->getStyle('A1:H1')->applyFromArray(['font' => ['bold' => true, 'size' => 18]]);
        $activeSheet->getStyle('A2:H3')->applyFromArray(['font' => ['size' => 14]]);
        $activeSheet->getStyle('A6:H6')->applyFromArray(['font' => ['size' => 16]]);
        $activeSheet->getStyle('A7:H11')->applyFromArray(['font' => ['size' => 14]]);
        $activeSheet->getStyle('A14:H14')->applyFromArray(['font' => ['size' => 16]]);
        $activeSheet->getStyle('A15:H15')->applyFromArray(['font' => ['size' => 12]]);
        $activeSheet->getStyle('A2:H3')->applyFromArray(['font' => ['size' => 14]]);
    }

    /**
     * Заполнение
     *
     * @param \PHPExcel $objPHPExcel
     * @param int       $row
     * @param string    $client_string
     * @param string    $vendor_string
     * @throws \PHPExcel_Exception
     */
    private function fillCellData(\PHPExcel $objPHPExcel, int $row, string $client_string, string $vendor_string): void
    {
        $objPHPExcel->getActiveSheet()->mergeCells("A$row:D$row");
        $objPHPExcel->getActiveSheet()->setCellValue("A$row", $client_string);
        $objPHPExcel->getActiveSheet()->mergeCells("E$row:H$row");
        $objPHPExcel->getActiveSheet()->setCellValue("E$row", $vendor_string);
        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
    }

    /**
     * @param \PHPExcel $objPHPExcel
     * @param string    $column
     * @param string    $data
     * @throws \PHPExcel_Exception
     */
    private function fillCellHeaderData(\PHPExcel $objPHPExcel, string $column, string $data): void
    {
        $objPHPExcel->getActiveSheet()->setCellValue($column . "17", \Yii::t('app', $data));
        $objPHPExcel->getActiveSheet()->getStyle($column . "17")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($column . "17")->applyFromArray(['font' => ['bold' => true]])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

    /**
     * @param \PHPExcel $objPHPExcel
     * @param int       $row
     * @param string    $leftData
     * @param string    $rightData
     * @param bool      $bold
     * @return int
     * @throws \PHPExcel_Exception
     */
    private function fillCellBottomData(\PHPExcel $objPHPExcel, int $row, string $leftData, string $rightData, bool $bold = false): int
    {
        $objPHPExcel->getActiveSheet()->mergeCells("E$row:G$row");
        $objPHPExcel->getActiveSheet()->setCellValue("E$row", $leftData);
        $objPHPExcel->getActiveSheet()->getStyle("E$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $objPHPExcel->getActiveSheet()->setCellValue("H$row", " " . $rightData);
        $objPHPExcel->getActiveSheet()->getStyle("H$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objPHPExcel->getActiveSheet()->getStyle("E$row:H$row")->applyFromArray(['font' => ['size' => 16]]);
        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(25);
        if ($bold) {
            $objPHPExcel->getActiveSheet()->getStyle("E$row")->applyFromArray(['font' => ['bold' => true]]);
            $objPHPExcel->getActiveSheet()->getStyle("H$row")->applyFromArray(['font' => ['bold' => true]]);
        }
        $row++;
        return $row;
    }
}
