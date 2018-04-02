<?php

namespace frontend\controllers;

use Yii;
use yii\helpers\Json;
use yii\helpers\Html;
use common\models\search\OrderCatalogSearch;
use common\models\CatalogGoods;
use common\models\CatalogBaseGoods;
use common\models\Order;
use common\models\Role;
use common\models\OrderContent;
use common\models\Organization;
use common\models\search\OrderSearch;
use common\models\search\OrderContentSearch;
use common\models\ManagerAssociate;
use common\models\OrderChat;
use common\models\guides\Guide;
use common\models\search\GuideSearch;
use common\models\guides\GuideProduct;
use common\models\search\GuideProductsSearch;
use common\models\search\BaseProductSearch;
use common\models\search\VendorSearch;
use common\components\AccessRule;
use kartik\mpdf\Pdf;
use yii\filters\AccessControl;

class OrderController extends DefaultController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // We will override the default rule config with the new AccessRule class
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'edit',
                            'send-message',
                            'ajax-order-action',
                            'ajax-cancel-order',
                            'ajax-refresh-buttons',
                            'ajax-order-grid',
                            'ajax-refresh-stats',
                            'ajax-set-comment',
                            'ajax-calculate-total',
                            'pdf',
                            'export-to-xls',
                            'order-to-xls'
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                    [
                        'actions' => [
                            'create',
                            'guides',
                            'favorites',
                            'edit-guide',
                            'reset-guide',
                            'save-guide',
                            'checkout',
                            'repeat',
                            'refresh-cart',
                            'ajax-add-to-cart',
                            'ajax-add-guide-to-cart',
                            'ajax-delete-order',
                            'ajax-make-order',
                            'ajax-change-quantity',
                            'ajax-remove-position',
                            'ajax-show-details',
                            'ajax-refresh-vendors',
                            'ajax-set-note',
                            'ajax-set-delivery',
                            'ajax-show-details',
                            'ajax-create-guide',
                            'ajax-rename-guide',
                            'ajax-add-to-guide',
                            'ajax-delete-guide',
                            'ajax-remove-from-guide',
                            'ajax-show-guide',
                            'ajax-select-vendor',
                            'complete-obsolete',
                            'pjax-cart',
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                ],
//                'denyCallback' => function($rule, $action) {
//                    throw new HttpException(404, 'Нет здесь ничего такого, проходите, гражданин');
//                }
            ],
        ];
    }

    public function actionExportToXls() {
        $selected = Yii::$app->request->get('selected');
        if (!empty($selected)) {
            $model = \Yii::$app->db->createCommand("
                select 
                    cbg.article,
                    cbg.product as product, 
                    sum(quantity) as total_quantity,
                    cbg.ed
                from `order_content` oc 
                left join `catalog_base_goods` cbg on oc.`product_id` = cbg.`id`
                where oc.order_id in ($selected)
                group by cbg.id")->queryAll();

            $objPHPExcel = new \PHPExcel();
            $sheet = 0;
            $objPHPExcel->setActiveSheetIndex($sheet);
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->setTitle(Yii::t('message', 'frontend.controllers.order.rep', ['ru' => 'отчет']))
                    ->setCellValue('A1', Yii::t('message', 'frontend.controllers.order.art', ['ru' => 'Артикул']))
                    ->setCellValue('B1', Yii::t('message', 'frontend.controllers.order.good', ['ru' => 'Наименование товара']))
                    ->setCellValue('C1', Yii::t('message', 'frontend.controllers.order.amo', ['ru' => 'Кол-во']))
                    ->setCellValue('D1', Yii::t('message', 'frontend.controllers.order.mea', ['ru' => 'Ед.изм']));
            $row = 2;
            foreach ($model as $foo) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $foo['article']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, Html::decode(Html::decode(Html::decode($foo['product']))));
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $foo['total_quantity']);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $foo['ed']);
                $row++;
            }
            header('Content-Type: application/vnd.ms-excel');
            $filename = "otchet_" . date("d-m-Y-His") . ".xls";
            header('Content-Disposition: attachment;filename=' . $filename . ' ');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
        }
    }

    public function actionOrderToXls($id) {
        $order = Order::findOne($id);
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
                ->setTitle("otchet_zakaz_" . date("d-m-Y-His"));

        $sheet = 0;
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth($width);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth($width);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);


        $objPHPExcel->getActiveSheet()->mergeCells('A1:H1');
        $objPHPExcel->getActiveSheet()->setTitle(Yii::t('message', 'frontend.controllers.order.rep', ['ru' => 'отчет']))
                ->setCellValue('A1', Yii::t('message', 'frontend.views.order.order_number', ['ru' => 'Заказ №']) . " " . $id);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);

        $objPHPExcel->getActiveSheet()->mergeCells('A2:H2');
        $objPHPExcel->getActiveSheet()->setCellValue('A2', Yii::t('app', 'от') . " " . Yii::$app->formatter->asDate($order->created_at, "dd.MM.yyyy, HH:mm"));
        $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        ;
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(18);

        $requestedDelivery = isset($order->requested_delivery) ? " " . Yii::$app->formatter->asDate($order->requested_delivery, 'dd.MM.yyyy') . " " . Yii::t('app', 'frontend.excel.year') : "";
        $objPHPExcel->getActiveSheet()->mergeCells('A3:H3');
        $objPHPExcel->getActiveSheet()->setCellValue('A3', Yii::t('app', 'common.mail.bill.delivery_date') . $requestedDelivery);
        $objPHPExcel->getActiveSheet()->getStyle('A3:H3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(18);

        $objPHPExcel->getActiveSheet()->getRowDimension(4)->setRowHeight(5);
        $objPHPExcel->getActiveSheet()->getStyle('A5:H5')->getBorders()
                ->getTop()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->mergeCells('A6:D6');
        $objPHPExcel->getActiveSheet()->setCellValue('A6', Yii::t('message', 'frontend.views.order.customer'));
        $objPHPExcel->getActiveSheet()->getStyle('A6:D6')->applyFromArray(['font' => ['bold' => true]]);
        $objPHPExcel->getActiveSheet()->mergeCells('E6:H6');
        $objPHPExcel->getActiveSheet()->setCellValue('E6', Yii::t('app', 'Поставщик'));
        $objPHPExcel->getActiveSheet()->getStyle('E6:H6')->applyFromArray(['font' => ['bold' => true]]);
        $objPHPExcel->getActiveSheet()->getRowDimension(6)->setRowHeight(22);

        $clientName = (!empty($order->client->legal_entity)) ? $order->client->name . " (" . $order->client->legal_entity . ")" : $order->client->name;
        $vendorName = (!empty($order->vendor->legal_entity)) ? $order->vendor->name . " (" . $order->vendor->legal_entity . ")" : $order->vendor->name;
        $objPHPExcel->getActiveSheet()->mergeCells('A7:D7');
        $objPHPExcel->getActiveSheet()->setCellValue('A7', $clientName);
        $objPHPExcel->getActiveSheet()->getStyle('A7:D7')->applyFromArray(['font' => ['bold' => true]])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->getActiveSheet()->mergeCells('E7:H7');
        $objPHPExcel->getActiveSheet()->setCellValue('E7', $vendorName);
        $objPHPExcel->getActiveSheet()->getStyle('E7:H7')->applyFromArray(['font' => ['bold' => true]])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);
        $objPHPExcel->getActiveSheet()->getRowDimension(7)->setRowHeight(25);

        $acceptedName = isset($order->acceptedBy->profile->full_name) ? $order->acceptedBy->profile->full_name : '';
        $this->fillCellData($objPHPExcel, 8, Yii::t('message', 'frontend.views.order.phone_four') . " " . $order->client->phone, Yii::t('message', 'frontend.views.order.phone_four') . " " . $order->vendor->phone);
        $this->fillCellData($objPHPExcel, 9, 'E-mail: ' . $order->client->email, 'E-mail: ' . $order->vendor->email);
        $this->fillCellData($objPHPExcel, 10, Yii::t('app', 'Заказ создал:') . " " . $order->createdBy->profile->full_name, Yii::t('app', 'Заказ принял:') . " " . $acceptedName);
        $this->fillCellData($objPHPExcel, 11, Yii::t('message', 'market.views.site.supplier.address') . " " . $order->client->locality . " " . $order->client->address, Yii::t('message', 'market.views.site.supplier.address') . " " . $order->vendor->locality . " " . $order->vendor->address);
        $objPHPExcel->getActiveSheet()->getStyle('A11')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('D11')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getRowDimension(11)->setRowHeight(50);

        $objPHPExcel->getActiveSheet()->getStyle('A13:H13')->getBorders()
                ->getTop()
                ->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $objPHPExcel->getActiveSheet()->setCellValue('A14', Yii::t('app', 'Комментарий к заказу:'));
        $objPHPExcel->getActiveSheet()->getStyle('A14')->applyFromArray(['font' => ['bold' => true]]);
        $objPHPExcel->getActiveSheet()->mergeCells('A15:H15');
        $objPHPExcel->getActiveSheet()->setCellValue('A15', $order->comment);
        $objPHPExcel->getActiveSheet()->getStyle('A15')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getRowDimension(14)->setRowHeight(20);
        $objPHPExcel->getActiveSheet()->getRowDimension(15)->setRowHeight(30);

        $objPHPExcel->getActiveSheet()->getStyle('B17')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('C17')->getAlignment()->setWrapText(true);

        $this->fillCellHeaderData($objPHPExcel, 'A', '№ п/п');
        $this->fillCellHeaderData($objPHPExcel, 'B', 'Наименование товара');
        $this->fillCellHeaderData($objPHPExcel, 'C', 'Комментарий');
        $this->fillCellHeaderData($objPHPExcel, 'D', 'Артикул');
        $this->fillCellHeaderData($objPHPExcel, 'E', 'Ед. измерения');
        $this->fillCellHeaderData($objPHPExcel, 'F', 'Кол-во');

        $objPHPExcel->getActiveSheet()->getStyle('A17:H17')->applyFromArray($styleArray);

        $objPHPExcel->getActiveSheet()->setCellValue("G17", Yii::t('message', 'frontend.views.order.grid_price') . " " . $order->currency->iso_code);
        $objPHPExcel->getActiveSheet()->getStyle("G17")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle("G17")->applyFromArray(['font' => ['bold' => true]])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ;

        $this->fillCellHeaderData($objPHPExcel, 'H', 'frontend.widgets.cart.views.sum_two');

        $objPHPExcel->getActiveSheet()->getRowDimension(17)->setRowHeight(25);

        $row = 18;
        $goods = $order->orderContent;
        $i = 0;
        foreach ($goods as $good) {
            $i++;
            //dd($good->quantity);
            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(-1);
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", ($row-17));
            $objPHPExcel->getActiveSheet()->getStyle("A$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, Html::decode($good->product_name));
            $objPHPExcel->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setWrapText(true);

            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, Html::decode($good->comment));
            $objPHPExcel->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle("C$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $row, $good->article, \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle("D$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, Yii::t('app', $good->product->ed));
            $objPHPExcel->getActiveSheet()->getStyle("E$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $row, number_format($good->quantity, 3, '.', ''), \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle("F$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $row, number_format($good->price, 2, '.', ''), \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle("G$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $objPHPExcel->getActiveSheet()->setCellValueExplicit('H' . $row, number_format($good->quantity * $good->price, 2, '.', ''), \PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->getStyle("H$row")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_BOTTOM)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $objPHPExcel->getActiveSheet()->getStyle("A$row:H$row")->applyFromArray($styleArray);

            $objPHPExcel->getActiveSheet()->getStyle("B$row")->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle("C$row")->getAlignment()->setWrapText(true);

            $height = 19;
            $product_name_length = mb_strlen($good->product_name);
            $comment_length = mb_strlen($good->comment);
            if ($product_name_length > $width || $comment_length > $width) {
                if ($product_name_length > $comment_length) {
                    $i = ceil((float) $product_name_length / $width);
                } else {
                    $i = ceil((float) $comment_length / $width);
                }
                $height *= $i;
            }
            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight($height);
            $row++;
        }

        $objPHPExcel->getActiveSheet()->getStyle("A1:H$row")->applyFromArray(['font' => ['size' => 11]]);
        $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(['font' => ['bold' => true, 'size' => 18]]);
        $objPHPExcel->getActiveSheet()->getStyle('A2:H3')->applyFromArray(['font' => ['size' => 14]]);
        $objPHPExcel->getActiveSheet()->getStyle('A6:H6')->applyFromArray(['font' => ['size' => 16]]);
        $objPHPExcel->getActiveSheet()->getStyle('A7:H11')->applyFromArray(['font' => ['size' => 14]]);
        $objPHPExcel->getActiveSheet()->getStyle('A14:H14')->applyFromArray(['font' => ['size' => 16]]);
        $objPHPExcel->getActiveSheet()->getStyle('A15:H15')->applyFromArray(['font' => ['size' => 12]]);
        $objPHPExcel->getActiveSheet()->getStyle('A2:H3')->applyFromArray(['font' => ['size' => 14]]);

        $row += 2;
        $row = $this->fillCellBottomData($objPHPExcel, $row, Yii::t('app', 'Скидка:'), " " . $order->getFormattedDiscount());
        $row = $this->fillCellBottomData($objPHPExcel, $row, Yii::t('app', 'Стоимость доставки:'), " " . $order->calculateDelivery() . " " . $order->currency->iso_code);
        $row = $this->fillCellBottomData($objPHPExcel, $row, Yii::t('app', 'Итого:'), " " . $order->getTotalPriceWithOutDiscount() . " " . $order->currency->iso_code);
        $row = $this->fillCellBottomData($objPHPExcel, $row, Yii::t('message', 'frontend.views.order.total_price_all'), " " . $order->total_price . " " . $order->currency->iso_code, true);

        //$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        //$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        //$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        //$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        //$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(1);
        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(70);
        //$objPHPExcel->getActiveSheet()->freezePane("H$row");

        header('Content-Type: application/vnd.ms-excel');
        $filename = "otchet_zakaz_" . date("d-m-Y-His") . ".xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    private function fillCellData($objPHPExcel, $row, $client_string, $vendor_string): void {
        $objPHPExcel->getActiveSheet()->mergeCells("A$row:D$row");
        $objPHPExcel->getActiveSheet()->setCellValue("A$row", $client_string);
        $objPHPExcel->getActiveSheet()->mergeCells("E$row:H$row");
        $objPHPExcel->getActiveSheet()->setCellValue("E$row", $vendor_string);
        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
    }

    private function fillCellHeaderData($objPHPExcel, $column, $data): void {
        $objPHPExcel->getActiveSheet()->setCellValue($column . "17", Yii::t('app', $data));
        $objPHPExcel->getActiveSheet()->getStyle($column . "17")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($column . "17")->applyFromArray(['font' => ['bold' => true]])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

    private function fillCellBottomData($objPHPExcel, $row, $leftData, $rightData, $bold = false): int {
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

    public function actionCreate() {
        $session = Yii::$app->session;
        $client = $this->currentUser->organization;
        $searchModel = new OrderCatalogSearch();
        $params = Yii::$app->request->getQueryParams();

        if (Yii::$app->request->post("OrderCatalogSearch")) {
            $params['OrderCatalogSearch'] = Yii::$app->request->post("OrderCatalogSearch");
            $session['orderCatalogSearch'] = Yii::$app->request->post("OrderCatalogSearch");
        }

        $selectedCategory = null;
        $selectedVendor = null;

        if (isset($params['OrderCatalogSearch'])) {
            $selectedVendor = !empty($params['OrderCatalogSearch']['selectedVendor']) ? (int) $params['OrderCatalogSearch']['selectedVendor'] : null;
        }
        $vendors = $client->getSuppliers($selectedCategory);
        $catalogs = $vendors ? $client->getCatalogs($selectedVendor, $selectedCategory) : "(0)";

        $searchModel->client = $client;
        $searchModel->catalogs = $catalogs;

        if (Yii::$app->request->post("OrderCatalogSearch")) {
            
        }
        $params['OrderCatalogSearch'] = $session['orderCatalogSearch'];
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->params['OrderCatalogSearch[searchString]'] = isset($params['OrderCatalogSearch']['searchString']) ? $params['OrderCatalogSearch']['searchString'] : null;
        $dataProvider->pagination->params['OrderCatalogSearch[selectedVendor]'] = $selectedVendor;
        $dataProvider->pagination->params['OrderCatalogSearch[selectedCategory]'] = $selectedCategory;

        $orders = $client->getCart();

        //Вывод по 10
        $dataProvider->pagination->pageSize = 10;

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('create', compact('dataProvider', 'searchModel', 'orders', 'client', 'vendors', 'selectedVendor'));
        } else {
            return $this->render('create', compact('dataProvider', 'searchModel', 'orders', 'client', 'vendors', 'selectedVendor'));
        }
    }

    public function actionGuides() {
        $client = $this->currentUser->organization;
        $searchModel = new GuideSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['GuideSearch'] = Yii::$app->request->post("GuideSearch");

        $dataProvider = $searchModel->search($params, $client->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('guides', compact('dataProvider', 'searchModel'));
        } else {
            return $this->render('guides', compact('dataProvider', 'searchModel'));
        }
    }

    public function actionAjaxDeleteGuide($id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);
        if (isset($guide)) {
            $guide->delete();
            return true;
        }
        return false;
    }

    public function actionAjaxCreateGuide($name) {
        $client = $this->currentUser->organization;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if ($name && $client->type_id === Organization::TYPE_RESTAURANT) {
            $guide = new Guide();
            $guide->client_id = $client->id;
            $guide->name = $name;
            $guide->type = Guide::TYPE_GUIDE;
            $guide->save();
            return ['type' => 'success', 'url' => \yii\helpers\Url::to(['order/edit-guide', 'id' => $guide->id])];
        } else {
            return ['type' => 'fail'];
        }
    }

    public function actionAjaxRenameGuide() {
        if (Yii::$app->request->isAjax):
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $id = Yii::$app->request->post('id');
            $name = Yii::$app->request->post('name');

            $model = Guide::findOne($id);
            $model->name = $name;

            if ($model->validate() and $model->save()) {
                return ['type' => 'success'];
            } else {
                return ['type' => 'fail'];
            }
        endif;
    }


    public function actionEditGuide(int $id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);
        $params['show_sorting'] = true;

        if (empty($guide)) {
            return $this->redirect(['order/guides']);
        }

        $session = Yii::$app->session;

        if (isset($session['currentGuide']) && $id != $session['currentGuide']) {
            unset($session['guideProductList']);
            unset($session['selectedVendor']);
        }
        $session['currentGuide'] = $id;

        $guideProductList = isset($session['guideProductList']) ? $session['guideProductList'] : $guide->guideProductsIds;
        $session['guideProductList'] = $guideProductList;

        if(!count($session['guideProductList']))$params['show_sorting'] = false;
        if (is_iterable($session['guideProductList'])){
            foreach ($session['guideProductList'] as $one){
                if(gettype($one) == "integer"){
                    $params['show_sorting'] = false;
                    break;
                }
            }
        }

        $vendorSearchModel = new VendorSearch();
        if (Yii::$app->request->post("VendorSearch")) {
            $session['vendorSearchString'] = Yii::$app->request->post("VendorSearch");
        }
        $params['VendorSearch'] = $session['vendorSearchString'];
        $params['guide_id'] = $id;

        if (Yii::$app->request->get("sort")){
            $params['sort'] = Yii::$app->request->get("sort");
            if(isset($session['sort'])){
                unset($session['sort']);
            }
            $session['sort'] = $params['sort'] = Yii::$app->request->get("sort");
        }
        $vendorDataProvider = $vendorSearchModel->search($params, $client->id);
        $vendorDataProvider->pagination = ['pageSize' => 8];

        $productSearchModel = new OrderCatalogSearch();
        $vendors = $client->getSuppliers(null, false);
        $selectedVendor = $session['selectedVendor'];
        if (empty($selectedVendor)) {
            $selectedVendor = isset(array_keys($vendors)[0]) ? array_keys($vendors)[0] : null;
        }

        $catalogs = $vendors ? $client->getCatalogs($selectedVendor, null) : "(0)";
        $productSearchModel->client = $client;
        $productSearchModel->catalogs = $catalogs;
        if (Yii::$app->request->post("OrderCatalogSearch")) {
            $session['orderCatalogSearchString'] = Yii::$app->request->post("OrderCatalogSearch");
        }
        $params['OrderCatalogSearch'] = $session['orderCatalogSearchString'];
        $productDataProvider = $productSearchModel->search($params);
        $productDataProvider->pagination = ['pageSize' => 8];

        $guideSearchModel = new BaseProductSearch();
        if (Yii::$app->request->post("BaseProductSearch")) {
            $session['baseProductSearchString'] = Yii::$app->request->post("BaseProductSearch");
        }
        $params['BaseProductSearch'] = $session['baseProductSearchString'];
        $guideDataProvider = $guideSearchModel->search($params, $guideProductList);
        $guideDataProvider->pagination = ['pageSize' => 7];

        $pjax = Yii::$app->request->get("_pjax");
        if (Yii::$app->request->isPjax && $pjax == '#vendorList') {
            return $this->renderPartial('guides/_vendor-list', compact('vendorDataProvider', 'selectedVendor', 'session', 'params'));
        } elseif (Yii::$app->request->isPjax && $pjax == '#productList') {
            return $this->renderPartial('guides/_product-list', compact('productDataProvider', 'guideProductList', 'session', 'params'));
        } elseif (Yii::$app->request->isPjax && $pjax == '#guideProductList') {
            return $this->renderPartial('guides/_guide-product-list', compact('guideDataProvider', 'guideProductList', 'session', 'params'));
        } else {
            return $this->render('guides/edit-guide', compact('guide', 'selectedVendor', 'guideProductList', 'guideProductList', 'vendorSearchModel', 'vendorDataProvider', 'productSearchModel', 'productDataProvider', 'guideSearchModel', 'guideDataProvider', 'session', 'params'));
        }
    }


    public function actionSaveGuide($id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);
        $session = Yii::$app->session;

        if (isset($session['currentGuide']) && $id != $session['currentGuide']) {
            return $this->redirect(['order/guides']);
        }

        $guideProductList = isset($session['guideProductList']) ? $session['guideProductList'] : [];

        foreach ($guide->guideProducts as $guideProduct) {
            if (!in_array($guideProduct->cbg_id, $guideProductList)) {
                $guideProduct->delete();
            } else {
                $position = array_search($guideProduct->cbg_id, $guideProductList);
                if ($position !== false) {
                    unset($guideProductList[$position]);
                }
            }
        }

        foreach ($guideProductList as $newProductId) {
            $newProduct = new GuideProduct();
            $newProduct->guide_id = $id;
            $newProduct->cbg_id = $newProductId;
            $newProduct->save();
        }

        unset($session['guideProductList']);
        unset($session['selectedVendor']);
        unset($session['currentGuide']);

        return $this->redirect(['order/guides']);
    }

    public function actionResetGuide() {
        $session = Yii::$app->session;
        unset($session['guideProductList']);
        unset($session['selectedVendor']);
        unset($session['currentGuide']);
        return $this->redirect(['order/guides']);
    }

    public function actionAjaxShowGuide($id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);

        $params = Yii::$app->request->getQueryParams();

        $guideSearchModel = new GuideProductsSearch();
        $params['GuideProductsSearch'] = Yii::$app->request->post("GuideProductsSearch");
        $guideDataProvider = $guideSearchModel->search($params, $guide->id, $this->currentUser->organization_id);
        $guideDataProvider->pagination = false; //['pageSize' => 8];

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('/order/guides/_view', compact('guideSearchModel', 'guideDataProvider', 'guide'));
        } else {
            return $this->renderAjax('/order/guides/_view', compact('guideSearchModel', 'guideDataProvider', 'guide'));
        }
    }

    public function actionAjaxSelectVendor($id) {
        $session = Yii::$app->session;
        $session['selectedVendor'] = $id;
        return true;
    }

    public function actionAjaxAddToGuide($id) {
        $client = $this->currentUser->organization;
        $session = Yii::$app->session;

        $product = $client->getProductIfAvailable($id);

        if ($product) {
            $guideProductList = isset($session['guideProductList']) ? $session['guideProductList'] : [];
            if (!in_array($product->id, $guideProductList)) {
                $guideProductList[] = $product->id;
                $session['guideProductList'] = $guideProductList;
            }
        }

        return isset($product);
    }

    public function actionAjaxRemoveFromGuide($id) {
        $client = $this->currentUser->organization;
        $session = Yii::$app->session;
        $guideProductList = $session['guideProductList'];
        $positionInGuide = array_search($id, $guideProductList);
        if ($positionInGuide !== FALSE) {
            unset($guideProductList[$positionInGuide]);
            $session['guideProductList'] = $guideProductList;
            return true;
        }
        return false;
    }

    public function actionAjaxAddGuideToCart($id) {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);

        $guideProducts = Yii::$app->request->post("GuideProduct");

        foreach ($guideProducts as $productId => $quantity) {

            if ($quantity <= 0) {
                continue;
            }

            $guideProduct = GuideProduct::findOne(['id' => $productId, 'guide_id' => $id]);

            $orders = $client->getCart();

            $product_id = $guideProduct->cbg_id;
            $price = $guideProduct->price;
            $product_name = $guideProduct->baseProduct->product;
            $vendor = $guideProduct->baseProduct->vendor;
            $units = $guideProduct->baseProduct->units;
            $article = $guideProduct->baseProduct->article;
            //$quantity = $guideProduct->baseProduct->units ? $guideProduct->baseProduct->units : 1;
            $isNewOrder = true;

            foreach ($orders as $order) {
                if ($order->vendor_id == $vendor->id) {
                    $isNewOrder = false;
                    $alteringOrder = $order;
                }
            }
            if ($isNewOrder) {
                $newOrder = new Order();
                $newOrder->client_id = $client->id;
                $newOrder->vendor_id = $vendor->id;
                $newOrder->status = Order::STATUS_FORMING;
                $newOrder->save();
                $alteringOrder = $newOrder;
            }

            $isNewPosition = true;
            foreach ($alteringOrder->orderContent as $position) {
                if ($position->product_id == $product_id) {
                    $position->quantity += $quantity;
                    $position->save();
                    $isNewPosition = false;
                }
            }
            if ($isNewPosition) {
                $position = new OrderContent();
                $position->order_id = $alteringOrder->id;
                $position->product_id = $product_id;
                $position->quantity = $quantity;
                $position->price = $price;
                $position->product_name = $product_name;
                $position->units = $units;
                $position->article = $article;
                $position->save();
            }
            $alteringOrder->calculateTotalPrice();
        }
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);

        return true; //$this->renderPartial('_orders', compact('orders'));
    }

    public function actionFavorites() {
        $client = $this->currentUser->organization;

        $params = Yii::$app->request->getQueryParams();
        $params['FavoriteSearch'] = Yii::$app->request->post("FavoriteSearch");

        $searchModel = new \common\models\search\FavoriteSearch();
        $dataProvider = $searchModel->search($params, $client->id);

        return $this->render('favorites', compact('searchModel', 'dataProvider', 'client'));
    }

    public function actionPjaxCart() {
        if (Yii::$app->request->isPjax) {
            $client = $this->currentUser->organization;
            $orders = $client->getCart();
            return $this->renderPartial('_pjax-cart', compact('orders'));
        } else {
            return $this->redirect('/order/checkout');
        }
    }

    public function actionAjaxAddToCart() {
        $post = Yii::$app->request->post();
        $quantity = $post['quantity'];
        if ($quantity <= 0) {
            return false;
        }
        $client = $this->currentUser->organization;
        $orders = $client->getCart();

        $product = CatalogGoods::findOne(['base_goods_id' => $post['id'], 'cat_id' => $post['cat_id']]);

        if ($product) {
            $product_id = $product->baseProduct->id;
            $price = $product->price;
            $product_name = $product->baseProduct->product;
            $vendor = $product->organization;
            $units = $product->baseProduct->units;
            $article = $product->baseProduct->article;
        } else {
            $product = CatalogBaseGoods::findOne(['id' => $post['id'], 'cat_id' => $post['cat_id']]);
            if (!$product) {
                return true; //$this->renderAjax('_orders', compact('orders'));
            }
            $product_id = $product->id;
            $product_name = $product->product;
            $price = $product->price;
            $vendor = $product->vendor;
            $units = $product->units;
            $article = $product->article;
        }
        $isNewOrder = true;

        foreach ($orders as $order) {
            if ($order->vendor_id == $vendor->id) {
                $isNewOrder = false;
                $alteringOrder = $order;
            }
        }
        if ($isNewOrder) {
            $newOrder = new Order();
            $newOrder->client_id = $client->id;
            $newOrder->vendor_id = $vendor->id;
            $newOrder->status = Order::STATUS_FORMING;
            $newOrder->currency_id = $product->catalog->currency_id;
            $newOrder->save();
            $alteringOrder = $newOrder;
        }

        $isNewPosition = true;
        foreach ($alteringOrder->orderContent as $position) {
            if ($position->product_id == $product_id) {
                $position->quantity += $quantity;
                $position->save();
                $isNewPosition = false;
            }
        }
        if ($isNewPosition) {
            $position = new OrderContent();
            $position->order_id = $alteringOrder->id;
            $position->product_id = $product_id;
            $position->quantity = $quantity;
            $position->price = $price;
            $position->product_name = $product_name;
            $position->units = $units;
            $position->article = $article;
            $position->save();
        }
        $alteringOrder->calculateTotalPrice();
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);

        return $post['id']; //$this->renderPartial('_orders', compact('orders'));
    }

    public function actionAjaxShowDetails() {
        $get = Yii::$app->request->get();
        $productId = $get['id'];
        $catId = $get['cat_id'];
        $product = CatalogGoods::findOne(['base_goods_id' => $productId, 'cat_id' => $catId]);

        if ($product) {
            $baseProduct = $product->baseProduct;
            $price = $product->price;
            $currencySymbol = $product->catalog->currency->symbol;
        } else {
            $baseProduct = CatalogBaseGoods::findOne(['id' => $get['id'], 'cat_id' => $get['cat_id']]);
            $price = $baseProduct->price;
            $currencySymbol = $baseProduct->catalog->currency->symbol;
        }
        $vendor = $baseProduct->vendor;


        return $this->renderAjax("_order-details", compact('baseProduct', 'price', 'vendor', 'productId', 'catId', 'currencySymbol'));
    }

    public function actionAjaxRemovePosition($vendor_id, $product_id) {

        $client = $this->currentUser->organization;

        $orderDeleted = false;
        $order = Order::find()->where(['vendor_id' => $vendor_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
        foreach ($order->orderContent as $position) {
            if ($position->product_id == $product_id) {
                $position->delete();
            }
            if (!($order->positionCount)) {
                $orderDeleted = $order->delete();
            }
        }
        if (!$orderDeleted) {
            $order->calculateTotalPrice();
        }
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);

        return $product_id;
    }

    public function actionAjaxChangeQuantity($vendor_id = null, $product_id = null) {

        $client = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
            $quantity = Yii::$app->request->post('quantity');
            $product_id = Yii::$app->request->post('product_id');
            $vendor_id = Yii::$app->request->post('vendor_id');
            $order = Order::find()->where(['vendor_id' => Yii::$app->request->post('vendor_id'), 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
            foreach ($order->orderContent as $position) {
                if ($position->product_id == $product_id) {
                    $position->quantity = $quantity;
                    $position->save();
                }
            }
            $order->calculateTotalPrice();
            return true;
        }

        if (Yii::$app->request->get()) {
            $order = Order::findOne(['vendor_id' => $vendor_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            $vendor_name = $order->vendor->name;
            foreach ($order->orderContent as $position) {
                if ($position->product_id == $product_id) {
                    $quantity = $position->quantity;
                    $product_name = $position->product_name;
                    $units = $position->units;
                }
            }
            return $this->renderAjax('_change-quantity', compact('vendor_id', 'product_id', 'quantity', 'product_name', 'vendor_name', 'units'));
        }
    }

    public function actionAjaxSetComment($order_id) {

        $client = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
//            $order_id = Yii::$app->request->post('order_id');
            $order = Order::find()->where(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING])->one();
            if ($order) {
                $order->comment = Yii::$app->request->post('comment');
                $order->save();
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ["title" => Yii::t('message', 'frontend.controllers.order.comment_added', ['ru' => "Комментарий добавлен"]), "comment" => $order->comment, "type" => "success"]; //$this->successNotify("Комментарий добавлен");
            }
            return false;
        }
    }

    public function actionAjaxCancelOrder($order_id = null) {

        $initiator = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
            switch ($initiator->type_id) {
                case Organization::TYPE_RESTAURANT:
                    $order = Order::find()->where(['id' => $order_id, 'client_id' => $initiator->id])->one();
                    break;
                case Organization::TYPE_SUPPLIER:
                    $order = $this->findOrder([Order::tableName() . '.id' => $order_id, 'vendor_id' => $initiator->id], Yii::$app->user->can('manage'));
                    break;
            }
            if ($order) {
                if (Yii::$app->request->post("comment")) {
                    $order->comment = Yii::$app->request->post("comment");
                }
                $order->status = ($initiator->type_id == Organization::TYPE_RESTAURANT) ? Order::STATUS_CANCELLED : Order::STATUS_REJECTED;
                $systemMessage = $initiator->name . Yii::t('message', 'frontend.controllers.order.cancelled_order', ['ru' => ' отменил заказ!']);
                $danger = true;
                $order->save();
                if ($initiator->type_id == Organization::TYPE_RESTAURANT) {
                    $this->sendOrderCanceled($order->client, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order);
                }
                $this->sendSystemMessage($this->currentUser, $order->id, $systemMessage, $danger);
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ["title" => Yii::t('message', 'frontend.controllers.order.order_cancelled', ['ru' => "Заказ успешно отменен!"]), "type" => "success"];
            }
            return false;
        }
    }

    /* public function actionAjaxSetNote($product_id) {

      $client = $this->currentUser->organization;

      if (Yii::$app->request->post()) {
      $note = GoodsNotes::findOne(['catalog_base_goods_id' => $product_id, 'rest_org_id' => $client->id]);
      if (!$note) {
      $note = new GoodsNotes();
      $note->rest_org_id = $client->id;
      $note->catalog_base_goods_id = $product_id;
      }
      $note->note = Yii::$app->request->post("comment");
      $note->save();
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      $result = ["title" => Yii::t('message', 'frontend.controllers.order.comment', ['ru'=>"Комментарий к товару добавлен"]), "comment" => $note->note, "type" => "success"];
      return $result;
      }

      return false;
      } */

    public function actionAjaxSetNote($order_content_id) {

        $client = $this->currentUser->organization;

        if (Yii::$app->request->post()) {
            $orderContent = OrderContent::find()->where(['id' => $order_content_id])->one();
            if ($orderContent) {
                $order = $orderContent->order;

                if ($order && $order->client_id == $client->id && $order->status == Order::STATUS_FORMING) {
                    $orderContent->comment = Yii::$app->request->post('comment');
                    $orderContent->save();
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    $result = ["title" => Yii::t('message', 'frontend.controllers.order.comment', ['ru' => "Комментарий к товару добавлен"]), "comment" => $orderContent->comment, "type" => "success"];
                    return $result;
                }
            }
            return false;
        }

        return false;
    }

    public function actionAjaxMakeOrder() {
        $client = $this->currentUser->organization;
        $cartCount = $client->getCartCount();

        if (!$cartCount) {
            return false;
        }

        if (Yii::$app->request->post()) {
            $content = Yii::$app->request->post('OrderContent');
            $this->saveCartChanges($content);
            if (!Yii::$app->request->post('all')) {
                $order_id = Yii::$app->request->post('id');
                $orders[] = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            } else {
                $orders = Order::findAll(['client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            }
            foreach ($orders as $order) {
                $order->status = Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $order->created_by_id = $this->currentUser->id;
                $order->created_at = gmdate("Y-m-d H:i:s");
                $order->calculateTotalPrice(); //also saves order
                $this->sendNewOrder($order->vendor);
                $this->sendOrderCreated($this->currentUser, $order);
            }
            $cartCount = $client->getCartCount();
            $this->sendCartChange($client, $cartCount);
            return true;
        }

        return false;
    }

    public function actionAjaxCalculateTotal($id) {
        if (Yii::$app->request->post()) {
            $content = Yii::$app->request->post('OrderContent');
            $order = Order::findOne(['id' => $id, 'client_id' => $this->currentUser->organization_id, 'status' => Order::STATUS_FORMING]);
            $currencySymbol = $order->currency->symbol;
            $rawPrice = 0;
            $vendor_id = $order->vendor_id;
            $expectedPositions = [];
            foreach ($order->orderContent as $key => $item) {
                if (isset($content[$item->id])) {
                    $rawPrice += $order->orderContent[$key]->price * $content[$item->id]["quantity"];
                    $position = $order->orderContent[$key];
                    $position->quantity = $content[$item->id]["quantity"];
                    $expectedPositions[] = [
                        "id" => $position->id,
                        "price" => $this->renderPartial("_checkout-position-price", compact("position", "currencySymbol", "vendor_id")),
                    ];
                }
            }
            $forMinOrderPrice = $order->forMinOrderPrice($rawPrice);
            $forFreeDelivery = $order->forFreeDelivery($rawPrice);
            $order->calculateTotalPrice(false, $rawPrice);
            $result = [
                "total" => $this->renderPartial("_checkout-total", compact('order', 'currencySymbol', 'forMinOrderPrice', 'forFreeDelivery')),
                "expectedPositions" => $expectedPositions,
                "button" => $this->renderPartial("_checkout-position-button", compact("order", "currencySymbol", "forMinOrderPrice")),
            ];
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $result;
        }
    }

    public function actionAjaxDeleteOrder($all, $order_id = null) {
        $client = $this->currentUser->organization;

        if (!$all) {
            $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            if ($order) {
                OrderContent::deleteAll(['order_id' => $order->id]);
                $order->delete();
            }
        } else {
            $orders = Order::findAll(['client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            foreach ($orders as $order) {
                OrderContent::deleteAll(['order_id' => $order->id]);
                $order->delete();
            }
        }
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);
        return true;
    }

    public function actionAjaxSetDelivery() {
        if (Yii::$app->request->post()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $client = $this->currentUser->organization;
            $order_id = Yii::$app->request->post('order_id');
            $delivery_date = Yii::$app->request->post('delivery_date');
            $order = Order::findOne(['id' => $order_id, 'client_id' => $client->id, 'status' => Order::STATUS_FORMING]);
            $oldDateSet = isset($order->requested_delivery);
            if ($order && !empty($delivery_date)) {

                $nowTS = time();
                $requestedTS = strtotime($delivery_date . ' 19:00:00');

                $timestamp = date('Y-m-d H:i:s', strtotime($delivery_date . ' 19:00:00'));

                if ($nowTS < $requestedTS) {
                    $order->requested_delivery = $timestamp;
                    $order->save();
                } else {
                    $result = ["title" => Yii::t('message', 'frontend.controllers.order.uncorrect_date', ['ru' => "Некорректная дата"]), "type" => "error"];
                    return $result;
                }
            }
            if ($oldDateSet && !empty($delivery_date)) {
                $result = ["title" => Yii::t('message', 'frontend.controllers.order.date_changed', ['ru' => "Дата доставки изменена"]), "type" => "success"];
                return $result;
            }
            if (!$oldDateSet && !empty($delivery_date)) {
                $result = ["title" => Yii::t('message', 'frontend.controllers.order.date_set', ['ru' => "Дата доставки установлена"]), "type" => "success"];
                return $result;
            }
            if (empty($delivery_date)) {
                $order->requested_delivery = null;
                $order->save();
                $result = ["title" => Yii::t('message', 'frontend.controllers.order.seted_date', ['ru' => "Дата доставки удалена"]), "type" => "success"];
                return $result;
            }
        }
    }

    public function actionRefreshCart() {
        $client = $this->currentUser->organization;
        $orders = $client->getCart();
        return $this->renderAjax('_cart', compact('orders'));
    }

    public function actionIndex() {
        $organization = $this->currentUser->organization;
        $searchModel = new OrderSearch();
        $today = new \DateTime();
        $searchModel->date_to = $today->format('d.m.Y');
        $searchModel->date_from = Yii::$app->formatter->asTime($organization->getEarliestOrderDate(), "php:d.m.Y");

        $params = Yii::$app->request->getQueryParams();
        if ($organization->type_id == Organization::TYPE_RESTAURANT) {
            $params['OrderSearch']['client_search_id'] = $this->currentUser->organization_id;
            $params['OrderSearch']['client_id'] = $this->currentUser->organization_id;
            $newCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])->count();
            $processingCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => Order::STATUS_PROCESSING])->count();
            $fulfilledCount = Order::find()->where(['client_id' => $organization->id])->andWhere(['status' => Order::STATUS_DONE])->count();
            $query = Yii::$app->db->createCommand('select sum(total_price) as total from `order` where status=' . Order::STATUS_DONE . ' and client_id=' . $organization->id)->queryOne();
            $totalPrice = $query['total'];
        } else {
            $params['OrderSearch']['vendor_search_id'] = $this->currentUser->organization_id;
            $params['OrderSearch']['vendor_id'] = $this->currentUser->organization_id;
            $canManage = Yii::$app->user->can('manage');
            if ($canManage) {
                $newCount = Order::find()->where(['vendor_id' => $organization->id])->andWhere(['status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])->count();
                $processingCount = Order::find()->where(['vendor_id' => $organization->id])->andWhere(['status' => Order::STATUS_PROCESSING])->count();
                $fulfilledCount = Order::find()->where(['vendor_id' => $organization->id])->andWhere(['status' => Order::STATUS_DONE])->count();
                $totalPrice = Order::find()->where(['status' => Order::STATUS_DONE, 'vendor_id' => $organization->id])->sum("total_price");
            } else {
                $params['OrderSearch']['manager_id'] = $this->currentUser->id;
                $orderTable = Order::tableName();
                $maTable = ManagerAssociate::tableName();
                $newCount = Order::find()
                        ->leftJoin("$maTable", "$maTable.organization_id = `$orderTable`.client_id")
                        ->where([
                            'vendor_id' => $organization->id,
                            "$maTable.manager_id" => $this->currentUser->id,
                            'status' => [Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT, Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR]])
                        ->count();
                $processingCount = Order::find()
                        ->leftJoin("$maTable", "$maTable.organization_id = `$orderTable`.client_id")
                        ->where([
                            'vendor_id' => $organization->id,
                            "$maTable.manager_id" => $this->currentUser->id,
                            'status' => Order::STATUS_PROCESSING])
                        ->count();
                $fulfilledCount = Order::find()
                        ->leftJoin("$maTable", "$maTable.organization_id = `$orderTable`.client_id")
                        ->where([
                            'vendor_id' => $organization->id,
                            "$maTable.manager_id" => $this->currentUser->id,
                            'status' => Order::STATUS_DONE])
                        ->count();
                $totalPrice = Order::find()
                        ->leftJoin("$maTable", "$maTable.organization_id = `$orderTable`.client_id")
                        ->where([
                            'status' => Order::STATUS_DONE,
                            "$maTable.manager_id" => $this->currentUser->id,
                            'vendor_id' => $organization->id])
                        ->sum("total_price");
            }
        }
        $dataProvider = $searchModel->search($params);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', compact('searchModel', 'dataProvider', 'organization', 'newCount', 'processingCount', 'fulfilledCount', 'totalPrice'));
        } else {
            return $this->render('index', compact('searchModel', 'dataProvider', 'organization', 'newCount', 'processingCount', 'fulfilledCount', 'totalPrice'));
        }
    }

    public function actionView($id) {
        $user = $this->currentUser;
        $user->organization->markViewed($id);

        if ($user->organization->type_id == Organization::TYPE_SUPPLIER) {
            $order = $this->findOrder([Order::tableName() . '.id' => $id], Yii::$app->user->can('manage'));
        } else {
            $order = Order::findOne(['id' => $id]);
        }

        if (empty($order) || !(($order->client_id == $user->organization_id) || ($order->vendor_id == $user->organization_id))) {
            throw new \yii\web\HttpException(404, Yii::t('message', 'frontend.controllers.order.get_out', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_SUPPLIER)) {
            $this->redirect(['/order/index']);
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_RESTAURANT)) {
            $this->redirect(['/order/checkout']);
        }
        $organizationType = $user->organization->type_id;
        $initiator = ($organizationType == Organization::TYPE_RESTAURANT) ? $order->client->name : $order->vendor->name;
        $message = "";

        if (Yii::$app->request->post()) {
            $orderChanged = 0;
            $content = Yii::$app->request->post('OrderContent');
            $discount = Yii::$app->request->post('Order');
            foreach ($content as $position) {
                $product = OrderContent::findOne(['id' => $position['id']]);
                $initialQuantity = $product->initial_quantity;
                $allowedStatuses = [
                    Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                    Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                    Order::STATUS_PROCESSING
                ];
                $quantityChanged = ($position['quantity'] != $product->quantity);
                $priceChanged = isset($position['price']) ? ($position['price'] != $product->price) : false;
                if (in_array($order->status, $allowedStatuses) && ($quantityChanged || $priceChanged)) {
                    $orderChanged = ($orderChanged || $quantityChanged || $priceChanged);
                    if ($quantityChanged) {
                        $ed = isset($product->product->ed) ? ' ' . $product->product->ed : '';
                        if ($position['quantity'] == 0) {
                            $message .= Yii::t('message', 'frontend.controllers.order.del', ['ru' => "<br/>удалил {prod} из заказа", 'prod' => $product->product_name]);
                        } else {
                            $oldQuantity = $product->quantity + 0;
                            $newQuantity = $position["quantity"] + 0;
                            $message .= Yii::t('message', 'frontend.controllers.order.change', ['ru' => "<br/>изменил количество {prod} с {oldQuan} {ed} на ", 'prod' => $product->product_name, 'oldQuan' => $oldQuantity, 'ed' => $ed]) . " $newQuantity" . $ed;
                        }
                        $product->quantity = $position['quantity'];
                    }
                    if ($priceChanged) {
                        $message .= Yii::t('message', 'frontend.controllers.order.change_price', ['ru' => "<br/>изменил цену {prod} с {productPrice} руб на ", 'prod' => $product->product_name, 'productPrice' => $product->price]) . $position['price'] . " руб";
                        $product->price = $position['price'];
                    }
                    if ($quantityChanged && ($order->status == Order::STATUS_PROCESSING) && !isset($product->initial_quantity)) {
                        $product->initial_quantity = $initialQuantity;
                    }
                    if ($product->quantity == 0) {
                        $product->delete();
                    } else {
                        $product->save();
                    }
                }
            }
            if ($order->positionCount == 0 && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = Order::STATUS_REJECTED;
                $orderChanged = -1;
            }
            if ($order->positionCount == 0 && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = Order::STATUS_CANCELLED;
                $orderChanged = -1;
            }
            if ($orderChanged < 0) {
                $systemMessage = $initiator . Yii::t('message', 'frontend.controllers.order.cancelled_order_two', ['ru' => ' отменил заказ!']);
                $this->sendSystemMessage($user, $order->id, $systemMessage, true);
                if ($organizationType == Organization::TYPE_RESTAURANT) {
                    $this->sendOrderCanceled($order->client, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order);
                }
            }
            if (($discount['discount_type']) && ($discount['discount'])) {
                $discountChanged = (($order->discount_type != $discount['discount_type']) || ($order->discount != $discount['discount']));
                if ($discountChanged) {
                    $order->discount_type = $discount['discount_type'];
                    $order->discount = $order->discount_type ? abs($discount['discount']) : null;
                    if ($order->discount_type == Order::DISCOUNT_FIXED) {
                        $message = $order->discount . Yii::t('message', 'frontend.controllers.order.rouble', ['ru' => " руб"]);
                    } else {
                        $message = $order->discount . "%";
                    }
                    $this->sendSystemMessage($user, $order->id, $order->vendor->name . Yii::t('message', 'frontend.controllers.order.discount', ['ru' => ' сделал скидку на заказ №']) . $order->id . Yii::t('message', 'frontend.controllers.order.value', ['ru' => " в размере: "]) . $message);
                }
            } else {
                $order->discount_type = Order::DISCOUNT_NO_DISCOUNT;
                $order->discount = null;
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . Yii::t('message', 'frontend.controllers.order.cancel_discount', ['ru' => ' отменил скидку на заказ №']) . $order->id);
            }
            if (($orderChanged > 0) && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = ($order->status === Order::STATUS_PROCESSING) ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                $this->sendSystemMessage($user, $order->id, $order->client->name . Yii::t('message', 'frontend.controllers.order.change_details', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
                $this->sendOrderChange($order->client, $order);
            } elseif (($orderChanged > 0) && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = $order->status == Order::STATUS_PROCESSING ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
                $order->accepted_by_id = $user->id;
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . Yii::t('message', 'frontend.controllers.order.change_details_two', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
                $this->sendOrderChange($order->vendor, $order);
            }

            if (Yii::$app->request->post('orderAction') && (Yii::$app->request->post('orderAction') == 'confirm')) {
                if (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                    $systemMessage = $order->client->name . Yii::t('message', 'frontend.controllers.order.receive_order', ['ru' => ' получил заказ!']);
                    $order->status = Order::STATUS_DONE;
                    $this->sendSystemMessage($user, $order->id, $systemMessage);
                    $this->sendOrderDone($order->acceptedBy, $order);
                }
            }
        }

        $order->calculateTotalPrice();
        $order->save();
        $searchModel = new OrderContentSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('view', compact('order', 'searchModel', 'dataProvider', 'organizationType', 'user'));
        } else {
            return $this->render('view', compact('order', 'searchModel', 'dataProvider', 'organizationType', 'user'));
        }
    }

    public function actionEdit($id) {
        $user = $this->currentUser;
        $user->organization->markViewed($id);

        if ($user->organization->type_id == Organization::TYPE_SUPPLIER) {
            $order = $this->findOrder([Order::tableName() . '.id' => $id], Yii::$app->user->can('manage'));
        } else {
            $order = Order::findOne(['id' => $id]);
        }

        if (empty($order) || !(($order->client_id == $user->organization_id) || ($order->vendor_id == $user->organization_id))) {
            throw new \yii\web\HttpException(404, Yii::t('message', 'frontend.controllers.order.get_out_two', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_SUPPLIER)) {
            $this->redirect(['/order/index']);
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_RESTAURANT)) {
            $this->redirect(['/order/checkout']);
        }
        $organizationType = $user->organization->type_id;
        $initiator = ($organizationType == Organization::TYPE_RESTAURANT) ? $order->client->name : $order->vendor->name;
        $message = "";
        $orderChanged = 0;
        $currencySymbol = $order->currency->symbol;

        if (Yii::$app->request->post()) {
            $content = Yii::$app->request->post('OrderContent');
            $discount = Yii::$app->request->post('Order');
            foreach ($content as $position) {
                $product = OrderContent::findOne(['id' => $position['id']]);
                $initialQuantity = $product->initial_quantity;
                $allowedStatuses = [
                    Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
                    Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
                    Order::STATUS_PROCESSING
                ];
                $quantityChanged = ($position['quantity'] != $product->quantity);
                $priceChanged = isset($position['price']) ? ($position['price'] != $product->price) : false;
                if (($organizationType == Organization::TYPE_RESTAURANT || in_array($order->status, $allowedStatuses)) && ($quantityChanged || $priceChanged)) {
                    $orderChanged = ($orderChanged || $quantityChanged || $priceChanged);
                    if ($quantityChanged) {
                        $ed = isset($product->product->ed) ? ' ' . $product->product->ed : '';
                        if ($position['quantity'] == 0) {
                            $message .= Yii::t('message', 'frontend.controllers.del_two', ['ru' => '<br/> удалил {prod} из заказа', 'prod' => $product->product_name]);
                        } else {
                            $oldQuantity = $product->quantity + 0;
                            $newQuantity = $position["quantity"] + 0;
                            $message .= Yii::t('message', 'frontend.controllers.order.change_three', ['ru' => "<br/>изменил количество {prod} с {oldQuan} {ed} на ", 'prod' => $product->product_name, 'oldQuan' => $oldQuantity, 'ed' => $ed]) . " $newQuantity" . $ed;
                        }
                        $product->quantity = $position['quantity'];
                    }
                    if ($priceChanged) {
                        $message .= Yii::t('message', 'frontend.controllers.order.change_price', ['ru' => "<br/>изменил цену {prod} с {productPrice} {currencySymbol} на ", 'prod' => $product->product_name, 'productPrice' => $product->price, 'currencySymbol' => $currencySymbol]) . $position['price'] . $currencySymbol;
                        $product->price = $position['price'];
                        if ($user->organization->type_id == Organization::TYPE_RESTAURANT && !$order->vendor->hasActiveUsers()) {
                            $prodFromCat = $product->getProductFromCatalog();
                            if (!empty($prodFromCat)) {
                                $prodFromCat->price = $product->price;
                                $prodFromCat->baseProduct->price = $product->price;
                                $prodFromCat->save();
                                $prodFromCat->baseProduct->save();
                            }
                        }
                    }
                    if ($quantityChanged && ($order->status == Order::STATUS_PROCESSING) && !isset($product->initial_quantity)) {
                        $product->initial_quantity = $initialQuantity;
                    }
                    if ($product->quantity == 0) {
                        $product->delete();
                    } else {
                        $product->save();
                    }
                }
            }
            if ($order->positionCount == 0 && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = Order::STATUS_REJECTED;
                $orderChanged = -1;
            }
            if ($order->positionCount == 0 && ($organizationType == Organization::TYPE_RESTAURANT)) {
                $order->status = Order::STATUS_CANCELLED;
                $orderChanged = -1;
            }
            if ($orderChanged < 0) {
                $systemMessage = $initiator . Yii::t('message', 'frontend.controllers.order.cancelled_order_three', ['ru' => ' отменил заказ!']);
                $this->sendSystemMessage($user, $order->id, $systemMessage, true);
                if ($organizationType == Organization::TYPE_RESTAURANT) {
                    $this->sendOrderCanceled($order->client, $order);
                } else {
                    $this->sendOrderCanceled($order->vendor, $order);
                }
            }
            if (($discount['discount_type']) && ($discount['discount'])) {
                $discountChanged = (($order->discount_type != $discount['discount_type']) || ($order->discount != $discount['discount']));
                if ($discountChanged) {
                    $order->discount_type = $discount['discount_type'];
                    $order->discount = null;
                    $order->calculateTotalPrice();
                    if ($order->discount_type == Order::DISCOUNT_FIXED) {
                        $order->discount = round($discount['discount'], 2);
                        $discountValue = $order->discount . " $currencySymbol";
                    } else {
                        $order->discount = abs($discount['discount']);
                        $discountValue = $order->discount . "%";
                    }
                    $message .= Yii::t('message', 'frontend.controllers.order.made_discount', ['ru' => "<br/> сделал скидку на заказ № {order_id} в размере:", 'order_id' => $order->id]) . $discountValue;
                    $orderChanged = 1;
                } else {
                    $message .= Yii::t('app', 'frontend.controllers.order.not_changed', ['ru' => "<br/> изначальная скидка сохранена для новых условий заказа № "]) . $order->id;
                }
            } else {
                if ($order->discount > 0) {
                    //$message .= Yii::t('message', 'frontend.controllers.order.cancelled_order_four', ['ru'=>"<br/> отменил скидку на заказ № "]) . $order->id;
                    $message .= Yii::t('app', 'frontend.controllers.order.not_changed', ['ru' => "<br/> изначальная скидка сохранена для новых условий заказа № "]) . $order->id;
                    $orderChanged = 1;
                }
                //$order->discount_type = Order::DISCOUNT_NO_DISCOUNT;
                //$order->discount = null;
                $order->calculateTotalPrice();
            }
            if (($orderChanged > 0) && ($organizationType == Organization::TYPE_RESTAURANT)) {
                if ($order->status != Order::STATUS_DONE) {
                    $order->status = ($order->status === Order::STATUS_PROCESSING) ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR;
                }
                $this->sendSystemMessage($user, $order->id, $order->client->name . Yii::t('message', 'frontend.controllers.order.change_details_three', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
                $order->calculateTotalPrice();
                $order->save();
                $this->sendOrderChange($order->client, $order);
            } elseif (($orderChanged > 0) && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->status = $order->status == Order::STATUS_PROCESSING ? Order::STATUS_PROCESSING : Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT;
                $order->accepted_by_id = $user->id;
                $order->calculateTotalPrice();
                $order->save();
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . Yii::t('message', 'frontend.controllers.order.change_details_four', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
                $this->sendOrderChange($order->vendor, $order);
            }

            if (Yii::$app->request->post('orderAction') && (Yii::$app->request->post('orderAction') == 'confirm')) {
                if (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                    $systemMessage = $order->client->name . Yii::t('message', 'frontend.controllers.order.receive_order_two', ['ru' => ' получил заказ!']);
                    $order->status = Order::STATUS_DONE;
                    $this->sendSystemMessage($user, $order->id, $systemMessage);
                    $this->sendOrderDone($order->acceptedBy, $order);
                }
            }
            $order->save();

//        if ($orderChanged) {
            return $this->redirect(["order/view", "id" => $order->id]);
            //      }
        }


        $searchModel = new OrderContentSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('edit', compact('order', 'searchModel', 'dataProvider', 'organizationType', 'user'));
        } else {
            return $this->render('edit', compact('order', 'searchModel', 'dataProvider', 'organizationType', 'user'));
        }
    }

    public function actionPdf($id) {
        $order = Order::findOne(['id' => $id]);
        $user = $this->currentUser;

        if (!(($order->client_id == $user->organization_id) || ($order->vendor_id == $user->organization_id))) {
            throw new \yii\web\HttpException(404, Yii::t('message', 'frontend.controllers.order.get_out_three', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_SUPPLIER)) {
            $this->redirect(['/order/index']);
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_RESTAURANT)) {
            $this->redirect(['/order/checkout']);
        }
        $organizationType = $user->organization->type_id;

        $order->calculateTotalPrice();
        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        //return $this->renderPartial('_pdf_order', compact('dataProvider', 'order'));
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8, // leaner size using standard fonts
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            'content' => $this->renderPartial('_pdf_order', compact('dataProvider', 'order')),
            'options' => [
                'defaultfooterline' => false,
                'defaultfooterfontstyle' => false,
//                'title' => 'Privacy Policy - Krajee.com',
//                'subject' => 'Generating PDF files via yii2-mpdf extension has never been easy'
//            'showImageErrors' => true,
            ],
            'methods' => [
//                'SetHeader' => ['Generated By: Krajee Pdf Component||Generated On: ' . date("r")],
                'SetFooter' => $this->renderPartial('_pdf_signature'),
            ],
            'cssFile' => '../web/css/pdf_styles.css'
        ]);
        return $pdf->render();
    }

    public function actionCheckout() {
        $client = $this->currentUser->organization;
        $totalCart = 0;

        if (Yii::$app->request->post('action') && Yii::$app->request->post('action') == "save") {
            $content = Yii::$app->request->post('OrderContent');
            $this->saveCartChanges($content);
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ["title" => Yii::t('message', 'frontend.controllers.order.changes_saved', ['ru' => "Изменения сохранены!"]), "type" => "success"];
        }

        $orders = $client->getCart();
        foreach ($orders as $order) {
            $order->calculateTotalPrice();
            $totalCart += $order->total_price;
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('checkout', compact('orders', 'totalCart'));
        } else {
            return $this->render('checkout', compact('orders', 'totalCart'));
        }
    }

    public function actionAjaxOrderGrid($id) {
        $order = Order::findOne(['id' => $id]);
        $user = $this->currentUser;
        if (!(($order->client_id == $user->organization_id) || ($order->vendor_id == $user->organization_id))) {
            throw new \yii\web\HttpException(404, Yii::t('message', 'frontend.controllers.order.get_out_four', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_SUPPLIER)) {
            $this->redirect(['/order/index']);
        }
        if (($order->status == Order::STATUS_FORMING) && ($user->organization->type_id == Organization::TYPE_RESTAURANT)) {
            $this->redirect(['/order/checkout']);
        }
        $organizationType = $user->organization->type_id;

        $order->calculateTotalPrice();
        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        return $this->renderPartial('_view-grid', compact('dataProvider', 'order'));
    }

    public function actionAjaxOrderAction() {
        if (Yii::$app->request->post()) {
            $user_id = $this->currentUser->id;
            $order = Order::findOne(['id' => Yii::$app->request->post('order_id')]);
            $organizationType = $this->currentUser->organization->type_id;
            $danger = false;
            $edit = false;
            $systemMessage = '';
            switch (Yii::$app->request->post('action')) {
                case 'cancel':
                    $order->status = ($organizationType == Organization::TYPE_RESTAURANT) ? Order::STATUS_CANCELLED : Order::STATUS_REJECTED;
                    $initiator = ($organizationType == Organization::TYPE_RESTAURANT) ? $order->client->name : $order->vendor->name;
                    $systemMessage = $initiator . Yii::t('message', 'frontend.controllers.order.cancelled_order_five', ['ru' => ' отменил заказ!']);
                    $danger = true;
                    if ($organizationType == Organization::TYPE_RESTAURANT) {
                        $this->sendOrderCanceled($order->client, $order);
                    } else {
                        $this->sendOrderCanceled($order->vendor, $order);
                    }
                    break;
                case 'confirm':
                    if ($order->isObsolete) {
                        $systemMessage = $order->client->name . Yii::t('message', 'frontend.controllers.order.receive_order_three', ['ru' => ' получил заказ!']);
                        $order->status = Order::STATUS_DONE;
                        $order->actual_delivery = gmdate("Y-m-d H:i:s");
                        $this->sendOrderDone($order->createdBy, $order);
                    } elseif (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT)) {
                        $order->status = Order::STATUS_PROCESSING;
                        $systemMessage = $order->client->name . Yii::t('message', 'frontend.controllers.order.confirm_order', ['ru' => ' подтвердил заказ!']);
                        $this->sendOrderProcessing($order->client, $order);
                        $edit = true;
                    } elseif (($organizationType == Organization::TYPE_SUPPLIER) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR)) {
                        $systemMessage = $order->vendor->name . Yii::t('message', 'frontend.controllers.order.confirm_order_two', ['ru' => ' подтвердил заказ!']);
                        $order->accepted_by_id = $user_id;
                        $order->status = Order::STATUS_PROCESSING;
                        $edit = true;
                        $this->sendOrderProcessing($order->vendor, $order);
                    } elseif (($organizationType == Organization::TYPE_RESTAURANT) && ($order->status == Order::STATUS_PROCESSING)) {
                        $systemMessage = $order->client->name . Yii::t('message', 'frontend.controllers.order.receive_order_four', ['ru' => ' получил заказ!']);
                        $order->status = Order::STATUS_DONE;
                        $order->actual_delivery = gmdate("Y-m-d H:i:s");
                        $this->sendOrderDone($order->createdBy, $order);
                    }
                    break;
            }
            if ($order->save()) {
                $this->sendSystemMessage($this->currentUser, $order->id, $systemMessage, $danger);
                return $this->renderPartial('_order-buttons', compact('order', 'organizationType', 'edit'));
            }
        }
    }

    public function actionCompleteObsolete($id) {
        $currentOrganization = $this->currentUser->organization;
        if ($currentOrganization->type_id === Organization::TYPE_RESTAURANT) {
            $order = Order::findOne(['id' => $id, 'client_id' => $currentOrganization->id]);
        } else {
            $order = Order::findOne(['id' => $id, 'vendor_id' => $currentOrganization->id]);
        }
        if (!isset($order) || !$order->isObsolete) {
            throw new \yii\web\HttpException(404, Yii::t('message', 'frontend.controllers.order.get_out_five', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }

        $systemMessage = $order->client->name . Yii::t('message', 'frontend.controllers.order.receive_order_five', ['ru' => ' получил заказ!']);
        $order->status = Order::STATUS_DONE;
        $order->actual_delivery = gmdate("Y-m-d H:i:s");
        $this->sendOrderDone($order->createdBy, $order);
        if ($order->save()) {
            $this->sendSystemMessage($this->currentUser, $order->id, $systemMessage, false);
            $this->redirect(['order/view', 'id' => $id]);
        }
    }

    public function actionSendMessage() {
        $user = $this->currentUser;
        if (Yii::$app->request->post() && Yii::$app->request->post('message')) {
            $message = Yii::$app->request->post('message');
            $order_id = Yii::$app->request->post('order_id');
            $this->sendChatMessage($user, $order_id, $message);
        }
    }

    public function actionAjaxRefreshButtons() {
        if (Yii::$app->request->post()) {
            $order = Order::findOne(['id' => Yii::$app->request->post('order_id')]);
            $organizationType = $this->currentUser->organization->type_id;
            $edit = false;
            $canRepeatOrder = false;
            if ($organizationType == Organization::TYPE_RESTAURANT) {
                switch ($order->status) {
                    case Order::STATUS_DONE:
                    case Order::STATUS_REJECTED:
                    case Order::STATUS_CANCELLED:
                        $canRepeatOrder = true;
                        break;
                }
            }
            return $this->renderPartial('_order-buttons', compact('order', 'organizationType', 'edit', 'canRepeatOrder'));
        }
    }

    public function actionAjaxRefreshVendors() {
        if (Yii::$app->request->post()) {
            $client = $this->currentUser->organization;
            $selectedCategory = Yii::$app->request->post("selectedCategory");
            $vendors = $client->getSuppliers($selectedCategory);
            return \yii\helpers\Html::dropDownList('OrderCatalogSearch[selectedVendor]', null, $vendors, ['id' => 'selectedVendor', "class" => "form-control"]);
        }
    }

    public function actionAjaxRefreshStats($setMessagesRead = 0, $setNotificationsRead = 0) {
        $organization = $this->currentUser->organization;
        $newOrdersCount = $organization->getNewOrdersCount();

        $unreadMessagesHtml = '';
        if ($setMessagesRead) {
            $unreadMessages = [];
            $organization->setMessagesRead();
        } else {
            $unreadMessages = $organization->unreadMessages;
            foreach ($unreadMessages as $message) {
                $unreadMessagesHtml .= $this->renderPartial('/order/_header-message', compact('message'));
            }
        }

        $unreadNotificationsHtml = '';
        if ($setNotificationsRead) {
            $unreadNotifications = [];
            $organization->setNotificationsRead();
        } else {
            $unreadNotifications = $organization->unreadNotifications;
            foreach ($unreadNotifications as $message) {
                $unreadNotificationsHtml .= $this->renderPartial('/order/_header-message', compact('message'));
            }
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'newOrdersCount' => $newOrdersCount,
            'unreadMessagesCount' => count($unreadMessages),
            'unreadNotificationsCount' => count($unreadNotifications),
            'unreadMessages' => $unreadMessagesHtml,
            'unreadNotifications' => $unreadNotificationsHtml,
        ];
    }

    public function actionRepeat($id) {
        $order = Order::findOne(['id' => $id]);

        if ($order->client_id !== $this->currentUser->organization_id) {
            throw new \yii\web\HttpException(404, Yii::t('message', 'frontend.controllers.order.get_out_six', ['ru' => 'Нет здесь ничего такого, проходите, гражданин']));
        }

        $newOrder = new Order([
            'client_id' => $order->client_id,
            'vendor_id' => $order->vendor_id,
            'created_by_id' => $order->created_by_id,
            'status' => Order::STATUS_FORMING,
        ]);
        $newContent = [];
        foreach ($order->orderContent as $position) {
            $attributes = $position->copyIfPossible();
            if ($attributes) {
                $newContent[] = new OrderContent($attributes);
            }
        }
        if ($newContent) {
            $currentOrder = Order::findOne([
                        'client_id' => $order->client_id,
                        'vendor_id' => $order->vendor_id,
                        'created_by_id' => $order->created_by_id,
                        'status' => Order::STATUS_FORMING,
            ]);
            if (!$currentOrder) {
                $currentOrder = $newOrder;
                $currentOrder->save();
            }
            foreach ($newContent as $position) {
                $samePosition = OrderContent::findOne([
                            'order_id' => $currentOrder->id,
                            'product_id' => $position->product_id,
                ]);
                if ($samePosition) {
                    $samePosition->quantity += $position->quantity;
                    $samePosition->save();
                } else {
                    $position->order_id = $currentOrder->id;
                    $position->save();
                }
            }
            $currentOrder->calculateTotalPrice();
        }
        $this->redirect(['order/checkout']);
    }

    private function sendChatMessage($user, $order_id, $message) {
        $order = Order::findOne(['id' => $order_id]);

        $newMessage = new OrderChat(['scenario' => 'userSent']);
        $newMessage->order_id = $order_id;
        $newMessage->sent_by_id = $user->id;
        $newMessage->message = $message;
        if ($order->client_id == $user->organization_id) {
            $newMessage->recipient_id = $order->vendor_id;
        } else {
            $newMessage->recipient_id = $order->client_id;
        }
        $newMessage->save();

        $name = $user->profile->full_name;

        $body = $this->renderPartial('_chat-message', [
            'id' => $newMessage->id,
            'name' => $name,
            'message' => $newMessage->message,
            'time' => $newMessage->created_at,
            'isSystem' => 0,
            'sender_id' => $user->id,
            'ajax' => 1,
        ]);

        $clientUsers = $order->client->users;
        $vendorUsers = $order->vendor->users;

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body' => $body,
                    'channel' => $channel,
                    'isSystem' => 0,
                    'id' => $newMessage->id,
                    'sender_id' => $user->id,
                    'order_id' => $order_id,
                ])
            ]);
        }
        foreach ($vendorUsers as $vendorUser) {
            $channel = 'user' . $vendorUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body' => $body,
                    'channel' => $channel,
                    'isSystem' => 0,
                    'id' => $newMessage->id,
                    'sender_id' => $user->id,
                    'order_id' => $order_id,
                ])
            ]);
        }

        return true;
    }

    private function sendSystemMessage($user, $order_id, $message, $danger = false) {
        $order = Order::findOne(['id' => $order_id]);

        $newMessage = new OrderChat();
        $newMessage->order_id = $order_id;
        $newMessage->message = $message;
        $newMessage->is_system = 1;
        $newMessage->sent_by_id = $user->id;
        $newMessage->danger = $danger;
        if ($order->client_id == $user->organization_id) {
            $newMessage->recipient_id = $order->vendor_id;
        } else {
            $newMessage->recipient_id = $order->client_id;
        }
        $newMessage->save();
        $body = $this->renderPartial('_chat-message', [
            'name' => '',
            'message' => $newMessage->message,
            'time' => $newMessage->created_at,
            'isSystem' => 1,
            'sender_id' => $user->id,
            'ajax' => 1,
            'danger' => $danger,
        ]);

        $clientUsers = $order->client->users;
        $vendorUsers = $order->vendor->users;

        foreach ($clientUsers as $clientUser) {
            $channel = 'user' . $clientUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body' => $body,
                    'channel' => $channel,
                    'isSystem' => 1,
                    'order_id' => $order_id,
                ])
            ]);
        }
        foreach ($vendorUsers as $vendorUser) {
            $channel = 'user' . $vendorUser->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode([
                    'body' => $body,
                    'channel' => $channel,
                    'isSystem' => 1,
                    'order_id' => $order_id,
                ])
            ]);
        }

        return true;
    }

    private function sendCartChange($client, $cartCount) {
        $clientUsers = $client->users;

        foreach ($clientUsers as $user) {
            $channel = 'user' . $user->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode(['body' => $cartCount, 'channel' => $channel, 'isSystem' => 2])
            ]);
        }

        return true;
    }

    private function sendNewOrder($vendor) {
        $vendorUsers = $vendor->users;

        foreach ($vendorUsers as $user) {
            $channel = 'user' . $user->id;
            Yii::$app->redis->executeCommand('PUBLISH', [
                'channel' => 'chat',
                'message' => Json::encode(['channel' => $channel, 'isSystem' => 3])
            ]);
        }

        return true;
    }

    /**
     * Sends email informing both sides about order change details
     *
     * @param Organization $senderOrg
     * @param Order $order
     */
    private function sendOrderChange($senderOrg, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = Yii::t('message', 'frontend.controllers.order.change_in_order', ['ru' => "Измененения в заказе №"]) . $order->id;

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            $notification = ($recipient->getEmailNotification($order->vendor_id)) ? $recipient->getEmailNotification($order->vendor_id) : $recipient->getEmailNotification($order->client_id);
            if ($notification)
                if($notification->order_changed)
                {
                $result = $mailer->compose('orderChange', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            $notification = ($recipient->getSmsNotification($order->vendor_id)) ? $recipient->getSmsNotification($order->vendor_id) : $recipient->getSmsNotification($order->client_id);
            if ($notification)
                if($recipient->profile->phone && $notification->order_changed)
                {
                $text = Yii::$app->sms->prepareText('sms.order_changed', [
                    'name' => $senderOrg->name,
                    'url' => $order->getUrlForUser($recipient)
                ]);
                Yii::$app->sms->send($text, $recipient->profile->phone);
            }
        }
    }

    /**
     * Sends mail informing both sides that order is delivered and accepted
     *
     * @param User $sender
     * @param Order $order
     */
    private function sendOrderDone($sender, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $subject = Yii::t('message', 'frontend.controllers.order.complete', ['ru' => "Заказ № {order_id} выполнен!", 'order_id' => $order->id]);

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            $notification = ($recipient->getEmailNotification($order->vendor_id)) ? $recipient->getEmailNotification($order->vendor_id) : $recipient->getEmailNotification($order->client_id);
            if ($notification)
                if($notification->order_done)
                {
                $result = $mailer->compose('orderDone', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }

            $notification = ($recipient->getSmsNotification($order->vendor_id)) ? $recipient->getSmsNotification($order->vendor_id) : $recipient->getSmsNotification($order->client_id);
            if ($notification)
                if($recipient->profile->phone && $notification->order_done)
                {
                $text = Yii::$app->sms->prepareText('sms.order_done', [
                    'name' => $order->vendor->name,
                    'url' => $order->getUrlForUser($recipient)
                ]);
                Yii::$app->sms->send($text, $recipient->profile->phone);
            }
        }
    }

    /**
     * Sends mail informing both sides about new order
     *
     * @param Organization $sender
     * @param Order $order
     */
    private function sendOrderCreated($sender, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $senderOrg = $sender->organization;
        $subject = Yii::t('message', 'frontend.controllers.order.new_order', ['ru' => "Новый заказ №"]) . $order->id . "!";

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        $test = $order->recipientsList;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            $notification = ($recipient->getEmailNotification($order->vendor_id)) ? $recipient->getEmailNotification($order->vendor_id) : $recipient->getEmailNotification($order->client_id);
            if ($notification)
                if($notification->order_created)
                {
                $result = $mailer->compose('orderCreated', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            $notification = ($recipient->getSmsNotification($order->vendor_id)) ? $recipient->getSmsNotification($order->vendor_id) : $recipient->getSmsNotification($order->client_id);
            if ($notification)
                if($recipient->profile->phone && $notification->order_created)
                {
                $text = Yii::$app->sms->prepareText('sms.order_new', [
                    'name' => $senderOrg->name,
                    'url' => $order->getUrlForUser($recipient)
                ]);
                Yii::$app->sms->send($text, $recipient->profile->phone);
            }
        }
    }

    /**
     * Sends mail informing both sides that vendor confirmed order
     *
     * @param Organization $senderOrg
     * @param Order $order
     */
    private function sendOrderProcessing($senderOrg, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = Yii::t('message', 'frontend.controllers.order.accepted_order', ['ru' => "Заказ № {order_id} подтвержден!", 'order_id' => $order->id]);

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            $notification = ($recipient->getEmailNotification($order->vendor_id)) ? $recipient->getEmailNotification($order->vendor_id) : $recipient->getEmailNotification($order->client_id);
            if ($notification)
                if($notification->order_processing)
                {
                $result = $mailer->compose('orderProcessing', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            $notification = ($recipient->getSmsNotification($order->vendor_id)) ? $recipient->getSmsNotification($order->vendor_id) : $recipient->getSmsNotification($order->client_id);
            if ($notification)
                if($recipient->profile->phone && $notification->order_processing)
                {
                $text = Yii::$app->sms->prepareText('sms.order_processing', [
                    'vendor_name' => $order->vendor->name,
                    'url' => $order->getUrlForUser($recipient)
                ]);
                Yii::$app->sms->send($text, $recipient->profile->phone);
            }
        }
    }

    /**
     * Sends mail informing both sides about cancellation of order
     *
     * @param Organization $senderOrg
     * @param Order $order
     */
    private function sendOrderCanceled($senderOrg, $order) {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = Yii::t('message', 'frontend.controllers.order.cancelled_order_six', ['ru' => "Заказ № {order_id} отменен!", 'order_id' => $order->id]);

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            $notification = ($recipient->getEmailNotification($order->vendor_id)) ? $recipient->getEmailNotification($order->vendor_id) : $recipient->getEmailNotification($order->client_id);
            if ($notification)
                if($notification->order_canceled)
                {
                $notification = $mailer->compose('orderCanceled', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                        ->setTo($email)
                        ->setSubject($subject)
                        ->send();
            }
            $notification = ($recipient->getSmsNotification($order->vendor_id)) ? $recipient->getSmsNotification($order->vendor_id) : $recipient->getSmsNotification($order->client_id);
            if ($notification)
                if($recipient->profile->phone && $notification->order_canceled)
                {
                $text = Yii::$app->sms->prepareText('sms.order_canceled', [
                    'name' => $senderOrg->name,
                    'url' => $order->getUrlForUser($recipient)
                ]);
                Yii::$app->sms->send($text, $recipient->profile->phone);
            }
        }
    }

    private function saveCartChanges($content) {
        foreach ($content as $position) {
            $product = OrderContent::findOne(['id' => $position['id']]);
            if ($product->quantity == 0) {
                $product->delete();
            } else {
                $product->quantity = $position['quantity'];
                $product->save();
            }
        }
    }

    private function findOrder($condition, $canManage = false) {
        if ($canManage) {
            $order = Order::find()->where($condition)->one();
        } else {
            $maTable = ManagerAssociate::tableName();
            $orderTable = Order::tableName();
            $order = Order::find()
                    ->leftJoin("$maTable", "$maTable.organization_id = $orderTable.client_id")
                    ->where($condition)
                    ->andWhere(["$maTable.manager_id" => $this->currentUser->id])
                    ->one();
        }
        return $order;
    }

}
