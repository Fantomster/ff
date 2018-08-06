<?php

namespace frontend\controllers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\MercVsd;
use api\common\models\merc\search\mercVSDSearch;
use api_web\classes\CartWebApi;
use api_web\classes\RkeeperWebApi;
use api_web\modules\integration\modules\rkeeper\models\rkeeperOrder;
use api_web\modules\integration\modules\rkeeper\models\rkeeperStore;
use common\models\Cart;
use common\models\search\OrderProductsSearch;
use Yii;
use yii\db\Expression;
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
use common\models\OrderAttachment;
use common\models\guides\Guide;
use common\models\search\GuideSearch;
use common\models\guides\GuideProduct;
use common\models\search\GuideProductsSearch;
use common\models\search\BaseProductSearch;
use common\models\search\VendorSearch;
use common\components\AccessRule;
use kartik\mpdf\Pdf;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\helpers\Url;

class OrderController extends DefaultController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
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
                            'order-to-xls',
                            'grid-report',
                            'ajax-show-products',
                            'ajax-add-to-order',
                            'save-selected-orders',
                            'upload-attachment',
                            'get-attachment',
                            'delete-attachment',
                            'ajax-get-vsd-list',
                            'ajax-add-good-quantity-to-session',
                            'ajax-clear-session'
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_ONE_S_INTEGRATION,
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
                            'ajax-order-update-waybill',
                            'complete-obsolete',
                            'pjax-cart',
                        ],
                        'allow' => true,
                        // Allow restaurant managers
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_ONE_S_INTEGRATION,
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

    public function actionExportToXls()
    {
        $this->actionSaveSelectedOrders();
        $selected = Yii::$app->session->get('selected', []);
        if (!empty($selected)) {

            $res = [];
            foreach ($selected as $page)
                if (count($page) > 0)
                    $res = array_merge($res, $page);

            $selected = implode(',', $res);

            /* $count = -1 * (strlen($selected) - stripos($selected, ','));

              $selected = ($selected[strlen($selected)-1] == ',') ? substr($selected, 0, $count) : $selected; */

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
            exit();
        }
    }

    public function actionOrderToXls(int $id): void
    {
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
            ->setTitle("order_" . $id);

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
        $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);;
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

        $this->fillCellHeaderData($objPHPExcel, 'H', 'frontend.widgets.cart.views.sum_two');

        $objPHPExcel->getActiveSheet()->getRowDimension(17)->setRowHeight(25);

        $row = 18;
        $goods = $order->orderContent;
        $i = 0;
        foreach ($goods as $good) {
            $i++;
            $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(-1);
            $objPHPExcel->getActiveSheet()->setCellValue("A$row", ($row - 17));
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
                    $i = ceil((float)$product_name_length / $width);
                } else {
                    $i = ceil((float)$comment_length / $width);
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

        $objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(70);
        // Set Orientation, size and scaling
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        header('Content-Type: application/vnd.ms-excel');
        $filename = "order_" . $id . ".xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    private function fillCellData(\PHPExcel $objPHPExcel, int $row, string $client_string, string $vendor_string): void
    {
        $objPHPExcel->getActiveSheet()->mergeCells("A$row:D$row");
        $objPHPExcel->getActiveSheet()->setCellValue("A$row", $client_string);
        $objPHPExcel->getActiveSheet()->mergeCells("E$row:H$row");
        $objPHPExcel->getActiveSheet()->setCellValue("E$row", $vendor_string);
        $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
    }

    private function fillCellHeaderData(\PHPExcel $objPHPExcel, string $column, string $data): void
    {
        $objPHPExcel->getActiveSheet()->setCellValue($column . "17", Yii::t('app', $data));
        $objPHPExcel->getActiveSheet()->getStyle($column . "17")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($column . "17")->applyFromArray(['font' => ['bold' => true]])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

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

    public function actionCreate()
    {
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
            $selectedVendor = !empty($params['OrderCatalogSearch']['selectedVendor']) ? (int)$params['OrderCatalogSearch']['selectedVendor'] : null;
        }
        $vendors = $client->getSuppliers($selectedCategory);
        $catalogs = $vendors ? $client->getCatalogs($selectedVendor, $selectedCategory) : "(0)";

        $searchModel->client = $client;
        $searchModel->catalogs = $catalogs;

        $params['OrderCatalogSearch'] = $session['orderCatalogSearch'];
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->params['OrderCatalogSearch[searchString]'] = isset($params['OrderCatalogSearch']['searchString']) ? $params['OrderCatalogSearch']['searchString'] : null;
        $dataProvider->pagination->params['OrderCatalogSearch[selectedVendor]'] = $selectedVendor;
        $dataProvider->pagination->params['OrderCatalogSearch[selectedCategory]'] = $selectedCategory;

        $cart = Cart::findOne(['organization_id' => $client->id]);
        $cartItems = (new \yii\db\Query)
                ->select('product_id')
                ->from(\common\models\CartContent::tableName())
                ->where(['cart_id' => $cart->id])
                ->createCommand()
                ->queryColumn();
        //Вывод по 10
        $dataProvider->pagination->pageSize = 10;

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('create', compact('dataProvider', 'searchModel', 'cartItems', 'client', 'vendors', 'selectedVendor'));
        } else {
            return $this->render('create', compact('dataProvider', 'searchModel', 'cartItems', 'client', 'vendors', 'selectedVendor'));
        }
    }

    public function actionGuides(): String
    {
        $client = $this->currentUser->organization;
        $searchModel = new GuideSearch();
        $params = Yii::$app->request->getQueryParams();
        $params['GuideSearch'] = Yii::$app->request->get("GuideSearch");

        $dataProvider = $searchModel->search($params, $client->id);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('guides', compact('dataProvider', 'searchModel'));
        } else {
            return $this->render('guides', compact('dataProvider', 'searchModel'));
        }
    }

    public function actionAjaxDeleteGuide(int $id)
    {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);
        if (isset($guide)) {
            $guide->delete();
            return true;
        }
        return false;
    }

    public function actionAjaxCreateGuide($name)
    {
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

    public function actionAjaxRenameGuide()
    {
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


    public function actionAjaxGetVsdList()
    {
        $guid = mercDicconst::getSetting('enterprise_guid');
        $mercVSDs = MercVsd::find()->where("guid = '$guid'")->groupBy('consignor')->asArray()->all();
        return $this->renderPartial('_vds_list', compact('mercVSDs'));
    }


    public function actionEditGuide(int $id)
    {
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

        if (!count($session['guideProductList']))
            $params['show_sorting'] = false;
        if (is_iterable($session['guideProductList'])) {
            foreach ($session['guideProductList'] as $one) {
                if (gettype($one) == "integer") {
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

        $session['sort'] = $params['sort'] = Yii::$app->request->get("sort") ?? $session['sort'] ?? '';

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

    public function actionSaveGuide($id)
    {
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

    public function actionResetGuide()
    {
        $session = Yii::$app->session;
        unset($session['guideProductList']);
        unset($session['selectedVendor']);
        unset($session['currentGuide']);
        return $this->redirect(['order/guides']);
    }

    public function actionAjaxShowGuide(int $id): String
    {
        $client = $this->currentUser->organization;
        $guide = Guide::findOne(['id' => $id, 'client_id' => $client->id]);

        $params = Yii::$app->request->getQueryParams();
        $session = Yii::$app->session;
        $session['sort'] = $params['sort'] = Yii::$app->request->get("sort") ?? $session['sort'] ?? '';

        $guideSearchModel = new GuideProductsSearch();
        $params['GuideProductsSearch'] = Yii::$app->request->post("GuideProductsSearch");
        $guideDataProvider = $guideSearchModel->search($params, $guide->id, $this->currentUser->organization_id);
        $guideDataProvider->pagination = false; //['pageSize' => 8];
        if (!Yii::$app->request->isPjax) {
            foreach ($_SESSION as $key => &$item) {
                if (strpos($key, 'uideProductCount')) {
                    unset($_SESSION[$key]);
                }
            }
        }
        $cart = $client->_getCart();
        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('/order/guides/_view', compact('guideSearchModel', 'guideDataProvider', 'guide', 'params', 'cart'));
        } else {
            return $this->renderAjax('/order/guides/_view', compact('guideSearchModel', 'guideDataProvider', 'guide', 'params', 'cart'));
        }
    }

    public function actionAjaxSelectVendor($id)
    {
        $session = Yii::$app->session;
        $session['selectedVendor'] = $id;
        return true;
    }

    public function actionAjaxAddToGuide($id)
    {
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

    public function actionAjaxRemoveFromGuide($id)
    {
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

    public function actionAjaxAddGuideToCart($id)
    {
        $client = $this->currentUser->organization;
        $guideProducts = Yii::$app->request->post("GuideProduct");
        $data = [];
        $totalQuantity = 0;
        foreach ($guideProducts as $productId => $quantity) {
            $totalQuantity += $quantity;
            if ($quantity <= 0) {
                continue;
            }

            $guideProduct = GuideProduct::findOne(['id' => $productId, 'guide_id' => $id]);
            $data[] = ['product_id' => $guideProduct->cbg_id, 'quantity' => $quantity];
        }
        if ($totalQuantity == 0) {
            return false;
        }

        try {
            (new CartWebApi())->add($data);
        } catch (\Exception $e) {
            return false;
        }

        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);

        return true; //$this->renderPartial('_orders', compact('orders'));
    }

    public function actionFavorites()
    {
        $client = $this->currentUser->organization;

        $params = Yii::$app->request->getQueryParams();
        $params['FavoriteSearch'] = Yii::$app->request->post("FavoriteSearch");

        $searchModel = new \common\models\search\FavoriteSearch();
        $dataProvider = $searchModel->search($params, $client->id);

        return $this->render('favorites', compact('searchModel', 'dataProvider', 'client'));
    }

    public function actionPjaxCart()
    {
        if (Yii::$app->request->isPjax) {
            $carts = (new CartWebApi())->items();
            return $this->renderPartial('_pjax-cart', compact('carts'));
        } else {
            return $this->redirect('/order/checkout');
        }
    }

    public function actionAjaxAddToCart()
    {
        $post = Yii::$app->request->post();
        $quantity = $post['quantity'];
        if ($quantity < 0) {
            return false;
        }

        $product = ['product_id' => $post['id'], 'quantity' => $quantity];

        try {
            (new CartWebApi())->add($product);
        } catch (\Exception $e) {
            return false;
        }

        $client = $this->currentUser->organization;
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);

        return $post['id'];
    }

    public function actionAjaxShowDetails()
    {
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

    public function actionAjaxRemovePosition($product_id)
    {

        $client = $this->currentUser->organization;
        $data = ['product_id' => $product_id, 'quantity' => 0];
        $items = (new CartWebApi())->add($data);
        $cartCount = $client->getCartCount();
        $this->sendCartChange($client, $cartCount);

        return $product_id;
    }

    public function actionAjaxChangeQuantity($vendor_id = null, $product_id = null)
    {

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

    public function actionAjaxSetComment($vendor_id)
    {
        if (Yii::$app->request->post()) {
            $comment = Yii::$app->request->post('comment');
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'order_comment_' . $vendor_id,
                'value' => $comment,
                'expire' => time() + (60 * 60),
            ]));
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ["title" => Yii::t('message', 'frontend.controllers.order.comment_added', ['ru' => "Комментарий добавлен"]), "comment" => $comment, "type" => "success"];
        }
        return false;
    }

    public function actionAjaxCancelOrder($order_id = null)
    {

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

    public function actionAjaxSetNote($product_id)
    {

        if (Yii::$app->request->post()) {
            $data['product_id'] = $product_id;
            $data['comment'] = Yii::$app->request->post('comment');
            try {
                (new CartWebApi())->productComment($data);
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                $result = ["title" => Yii::t('message', 'frontend.controllers.order.comment', ['ru' => "Комментарий к товару добавлен"]), "comment" => $data['comment'], "type" => "success"];
                return $result;
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function actionAjaxMakeOrder()
    {
        $cart = (new CartWebApi())->items();
        $cartCount = count($cart);

        if (!$cartCount) {
            return false;
        }

        if (Yii::$app->request->post()) {
            $content = Yii::$app->request->post('CartContent');
            $this->saveCartChanges($content);
            $err = 0;
            if (Yii::$app->request->post('all')) {
                $data = [];
                foreach ($cart as $item) {
                    $vendor_id = $item['id'];
                    $delivery_date = Yii::$app->request->cookies->getValue('requested_delivery_' . $vendor_id, date('Y-m-d H:i:s'));
                    if ($delivery_date != null) {
                        $data[] = ['id' => $vendor_id,
                            'delivery_date' => isset($delivery_date) ? date('d.m.Y', strtotime($delivery_date)) : null,
                            'comment' => Yii::$app->request->cookies->getValue('order_comment_' . $vendor_id, null)];
                    } else
                        $err++;
                }
            } else {
                $vendor_id = Yii::$app->request->post('id');
                $delivery_date = Yii::$app->request->cookies->getValue('requested_delivery_' . $vendor_id, date('Y-m-d H:i:s'));
                if ($delivery_date != null) {
                    $data[] = ['id' => $vendor_id,
                        'delivery_date' => isset($delivery_date) ? date('d.m.Y', strtotime($delivery_date)) : null,
                        'comment' => Yii::$app->request->cookies->getValue('order_comment_' . $vendor_id, null)];
                } else
                    $err++;
            }

            $res = [
                'success' => 0,
                'error' => 0,
                'message' => '',
            ];

            if (!empty($data))
                $res = (new CartWebApi())->registration($data);


            $res['error'] += $err;

            $title = Yii::t('message', 'frontend.views.order.orders_complete_count_success', ['ru' => 'Заказы {success} из {count} успешно оформлены.', 'success' => $res['success'], 'count' => $cartCount]) . "</br>";
            $description = ($res['error'] != 0) ? $description = Yii::t('message', 'frontend.views.order.orders_registration_delivery_date_error', ['ru' => 'Не указана дата доставки!']) . "</br>" . $res['message'] : "";

            $type = ($res['error'] == 0) ? $type = "success" : $type = "error";

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ["title" => $title, "description" => $description, "type" => $type];
        }
        return false;
    }

    public function actionAjaxCalculateTotal($id)
    {
        if (Yii::$app->request->post()) {
            $content = Yii::$app->request->post('CartContent');
            $carts = (new CartWebApi())->items();

            foreach ($carts as $item)
                if ($item['id'] == $id) {
                    $cart = $item;
                    break;
                }

            $rawPrice = 0;
            $vendor_id = $id;
            $expectedPositions = [];
            $currencySymbol = $cart['currency'];

            foreach ($cart['items'] as $item) {
                if (isset($content[$item['id']])) {
                    $rawPrice += $item['price'] * $content[$item['id']]["in_basket"];
                    $position = $item;
                    $position['in_basket'] = $content[$item['id']]["in_basket"];
                    $expectedPositions[] = [
                        "id" => $position['id'],
                        "price" => $this->renderPartial("_checkout-position-price", compact("position")),
                    ];
                }
            }
            $cartModel = new Cart();
            $forMinCartPrice = $cartModel->forMinCartPrice($vendor_id, $rawPrice);
            $forFreeDelivery = $cartModel->forFreeDelivery($vendor_id, $rawPrice);
            $cart['total_price'] = $cartModel->calculateTotalPrice($vendor_id, $rawPrice);
            $cart['for_min_cart_price'] = $forMinCartPrice;
            $cart['for_free_delivery'] = $forFreeDelivery;
            $result = [
                "total" => $this->renderPartial("_checkout-total", compact('cart')),
                "expectedPositions" => $expectedPositions,
                "button" => $this->renderPartial("_checkout-position-button", compact("cart")),
            ];
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $result;
        }
    }

    public function actionAjaxDeleteOrder($vendor_id = null)
    {
        $client = $this->currentUser->organization;
        $data = ['vendor_id' => $vendor_id];
        $items = (new CartWebApi())->clear($data);
        $cartCount = count($items);
        $this->sendCartChange($client, $cartCount);
        return true;
    }

    public function actionAjaxSetDelivery()
    {
        if (Yii::$app->request->post()) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $vendor_id = Yii::$app->request->post('vendor_id');
            $delivery_date = Yii::$app->request->post('delivery_date');
            $oldDateSet = Yii::$app->request->cookies->getValue('requested_delivery_' . $vendor_id, null);
            if (!empty($delivery_date)) {
                $nowTS = time();
                $requestedTS = strtotime($delivery_date . ' 19:00:00');

                $timestamp = date('Y-m-d H:i:s', strtotime($delivery_date . ' 19:00:00'));

                if ($nowTS < $requestedTS) {
                    Yii::$app->response->cookies->add(new \yii\web\Cookie([
                        'name' => 'requested_delivery_' . $vendor_id,
                        'value' => $timestamp,
                        'expire' => time() + (60 * 60),
                    ]));
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
                Yii::$app->response->cookies->remove('requested_delivery_' . $vendor_id, true);
                $result = ["title" => Yii::t('message', 'frontend.controllers.order.seted_date', ['ru' => "Дата доставки удалена"]), "type" => "success"];
                return $result;
            }
        }
    }

    public function actionRefreshCart()
    {
        $client = $this->currentUser->organization;
        $orders = $client->getCart();
        return $this->renderAjax('_cart', compact('orders'));
    }

    public function actionIndex()
    {
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
        $page = (array_key_exists('page', $params)) ? $params['page'] : 1;
        $selected = $session = Yii::$app->session->get('selected', []);
        $selected = (array_key_exists($page, $selected)) ? $selected[$page] : [];

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', compact('searchModel', 'dataProvider', 'organization', 'newCount', 'processingCount', 'fulfilledCount', 'totalPrice', 'selected'));
        } else {
            return $this->render('index', compact('searchModel', 'dataProvider', 'organization', 'newCount', 'processingCount', 'fulfilledCount', 'totalPrice', 'selected'));
        }
    }

    public function actionView($id)
    {
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
                        if ($position['quantity'] == -1) {
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
                    if ($product->quantity == -1) {
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
                $order->status = $order->status == Order::STATUS_PROCESSING;
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

    public function actionEdit($id)
    {
        $user = $this->currentUser;
        $user->organization->markViewed($id);

        $editableOrders = [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_PROCESSING,
            Order::STATUS_DONE,
        ];
        if ($user->organization->type_id == Organization::TYPE_SUPPLIER) {
            $order = $this->findOrder([Order::tableName() . '.id' => $id, Order::tableName() . '.status' => $editableOrders], Yii::$app->user->can('manage'));
        } else {
            $order = Order::findOne(['id' => $id, Order::tableName() . '.status' => $editableOrders]);
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
        $changed = [];
        $deleted = [];

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
                        if ($position['quantity'] == -1) {
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
                    if ($product->quantity == -1) {
                        $deleted[$product->id] = $product;
                        $product->delete();
                    } else {
                        $changed[$product->id] = $product;
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
                $this->sendOrderChange($order->client, $order, $changed, $deleted);
            } elseif (($orderChanged > 0) && ($organizationType == Organization::TYPE_SUPPLIER)) {
                $order->accepted_by_id = $user->id;
                $order->calculateTotalPrice();
                $order->save();
                $this->sendSystemMessage($user, $order->id, $order->vendor->name . Yii::t('message', 'frontend.controllers.order.change_details_four', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
                $this->sendOrderChange($order->vendor, $order, $changed, $deleted);
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

    public function actionPdf($id)
    {
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

    public function actionCheckout()
    {
        $totalCart = 0;

        if (Yii::$app->request->post('action') && Yii::$app->request->post('action') == "save") {
            $content = Yii::$app->request->post('CartContent');
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $this->saveCartChanges($content);
            return ["title" => Yii::t('message', 'frontend.controllers.order.changes_saved', ['ru' => "Изменения сохранены!"]), "type" => "success"];
        }

        $carts = (new CartWebApi())->items();
        foreach ($carts as $cart) {
            $totalCart += $cart['total_price'];
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('checkout', compact('carts', 'totalCart'));
        } else {
            return $this->render('checkout', compact('carts', 'totalCart'));
        }
    }

    public function actionAjaxOrderGrid($id)
    {
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

    public function actionAjaxOrderAction()
    {
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
                    } elseif (($organizationType == Organization::TYPE_SUPPLIER) && ($order->status == Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR || $order->status == Order::STATUS_PROCESSING)) {
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

    public function actionCompleteObsolete($id)
    {
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

    public function actionSendMessage()
    {
        $user = $this->currentUser;
        if (Yii::$app->request->post() && Yii::$app->request->post('message')) {
            $message = Yii::$app->request->post('message');
            $order_id = Yii::$app->request->post('order_id');
            $this->sendChatMessage($user, $order_id, $message);
        }
    }

    public function actionAjaxRefreshButtons()
    {
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

    public function actionAjaxRefreshVendors()
    {
        if (Yii::$app->request->post()) {
            $client = $this->currentUser->organization;
            $selectedCategory = Yii::$app->request->post("selectedCategory");
            $vendors = $client->getSuppliers($selectedCategory);
            return \yii\helpers\Html::dropDownList('OrderCatalogSearch[selectedVendor]', null, $vendors, ['id' => 'selectedVendor', "class" => "form-control"]);
        }
    }

    public function actionAjaxRefreshStats($setMessagesRead = 0, $setNotificationsRead = 0)
    {
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

    public function actionRepeat($id)
    {
        $order = Order::findOne(['id' => $id]);

        $newContent = [];
        foreach ($order->orderContent as $position) {
            $attributes = $position->copyIfPossible();
            if ($attributes) {
                $newContent[] = ['product_id' => $position->product_id, 'quantity' => $position->quantity];
            }
        }
        if ($newContent) {
            (new CartWebApi())->add($newContent);
        }
        $this->redirect(['order/checkout']);
    }

    private function sendChatMessage($user, $order_id, $message)
    {
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

    public function sendSystemMessage($user, $order_id, $message, $danger = false)
    {
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
        $body = Yii::$app->controller->renderPartial('@frontend/views/order/_chat-message', [
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

    private function sendCartChange($client, $cartCount)
    {
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

    private function sendNewOrder($vendor)
    {
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
     * @param OrderContent[] $changed
     * @param OrderContent[] $deleted
     */
    private function sendOrderChange($senderOrg, $order, $changed = [], $deleted = [])
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = Yii::t('message', 'frontend.controllers.order.change_in_order', ['ru' => "Измененения в заказе №"]) . $order->id;

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;
        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_changed) {
                        $result = $mailer->compose('orderChange', compact("subject", "senderOrg", "order", "dataProvider", "recipient", "changed", "deleted"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }
                $notification = $recipient->getSmsNotification($org);
                if ($notification)
                    if ($recipient->profile->phone && $notification->order_changed) {
                        $text = Yii::$app->sms->prepareText('sms.order_changed', [
                            'name' => $senderOrg->name,
                            'url' => $order->getUrlForUser($recipient)
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    }
            }
        }
    }

    /**
     * Sends mail informing both sides that order is delivered and accepted
     *
     * @param User $sender
     * @param Order $order
     */
    private function sendOrderDone($sender, $order)
    {
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
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;
        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_done) {
                        $result = $mailer->compose('orderDone', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }

                $notification = $recipient->getSmsNotification($org);
                if ($notification)
                    if (!empty($recipient->profile->phone) && $notification->order_done) {
                        $text = Yii::$app->sms->prepareText('sms.order_done', [
                            'name' => $order->vendor->name,
                            'url' => $order->getUrlForUser($recipient)
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    }
            }
        }
    }

    /**
     * Sends mail informing both sides about new order
     *
     * @param Organization $sender
     * @param Order $order
     */
    private function sendOrderCreated($sender, $order)
    {
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
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_created) {
                        $result = $mailer->compose('orderCreated', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }
                $notification = $recipient->getSmsNotification($org);
                if ($notification)
                    if ($recipient->profile->phone && $notification->order_created) {
                        $text = Yii::$app->sms->prepareText('sms.order_new', [
                            'name' => $senderOrg->name,
                            'url' => $order->getUrlForUser($recipient)
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    }
            }
        }
    }

    /**
     * Sends mail informing both sides that vendor confirmed order
     *
     * @param Organization $senderOrg
     * @param Order $order
     */
    public function sendOrderProcessing($senderOrg, $order)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = Yii::t('message', 'frontend.controllers.order.accepted_order', ['ru' => "Заказ № {order_id} подтвержден!", 'order_id' => $order->id]);

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_processing) {
                        $result = $mailer->compose('orderProcessing', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }
                $notification = $recipient->getSmsNotification($org);
                if ($notification)
                    if ($recipient->profile->phone && $notification->order_processing) {
                        $text = Yii::$app->sms->prepareText('sms.order_processing', [
                            'vendor_name' => $order->vendor->name,
                            'url' => $order->getUrlForUser($recipient)
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    }
            }
        }
    }

    /**
     * Sends mail informing both sides about cancellation of order
     *
     * @param Organization $senderOrg
     * @param Order $order
     */
    public function sendOrderCanceled($senderOrg, $order)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        $mailer = Yii::$app->mailer;
        // send email
        $subject = Yii::t('message', 'frontend.controllers.order.cancelled_order_six', ['ru' => "Заказ № {order_id} отменен!", 'order_id' => $order->id]);

        $searchModel = new OrderContentSearch();
        $params['OrderContentSearch']['order_id'] = $order->id;
        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination = false;
        $orgs[] = $order->vendor_id;
        $orgs[] = $order->client_id;

        foreach ($order->recipientsList as $recipient) {
            $email = $recipient->email;
            foreach ($orgs as $org) {
                $notification = $recipient->getEmailNotification($org);
                if ($notification)
                    if ($notification->order_canceled) {
                        $notification = $mailer->compose('orderCanceled', compact("subject", "senderOrg", "order", "dataProvider", "recipient"))
                            ->setTo($email)
                            ->setSubject($subject)
                            ->send();
                    }
                $notification = $recipient->getSmsNotification($org);
                if ($notification)
                    if ($recipient->profile->phone && $notification->order_canceled) {
                        $text = Yii::$app->sms->prepareText('sms.order_canceled', [
                            'name' => $senderOrg->name,
                            'url' => $order->getUrlForUser($recipient)
                        ]);
                        Yii::$app->sms->send($text, $recipient->profile->phone);
                    }
            }
        }
    }

    private function saveCartChanges($content)
    {
        $data = [];
        foreach ($content as $key => $row)
            if (is_array(($row)))
                $data[] = ['product_id' => $key, 'quantity' => $row['in_basket']];

        try {
            (new CartWebApi())->add($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function findOrder($condition, $canManage = false)
    {
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

    public function actionAjaxShowProducts($order_id)
    {
        $order = Order::findOne(['id' => $order_id]);

        $params = Yii::$app->request->getQueryParams();

        $productsSearchModel = new OrderProductsSearch();
        $params['OrderProductsSearch'] = (Yii::$app->request->isPost) ? Yii::$app->request->post("OrderProductsSearch") : Yii::$app->request->get("OrderProductsSearch");
        $productsDataProvider = $productsSearchModel->search($params, $order);

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('/order/add-position/_view', compact('productsSearchModel', 'productsDataProvider', 'order'));
        } else {
            return $this->renderAjax('/order/add-position/_view', compact('productsSearchModel', 'productsDataProvider', 'order'));
        }
    }

    public function actionAjaxAddToOrder()
    {
        $post = Yii::$app->request->post();

        if (OrderContent::findOne(['order_id' => $post['order_id'], 'product_id' => $post['product_id']]) != null)
            throw new BadRequestHttpException('This product already exists');

        $product = CatalogGoods::findOne(['base_goods_id' => $post['product_id'], 'cat_id' => $post['cat_id']]);

        if ($product) {
            $product_id = $product->baseProduct->id;
            $price = $product->price;
            $product_name = $product->baseProduct->product;
            $vendor = $product->organization;
            $units = $product->baseProduct->units;
            $article = $product->baseProduct->article;
        } else {
            $product = CatalogBaseGoods::findOne(['id' => $post['product_id'], 'cat_id' => $post['cat_id']]);
            if ($product == null) {
                throw new BadRequestHttpException('This product not found');
            }
            $product_id = $product->id;
            $product_name = $product->product;
            $price = $product->price;
            $units = $product->units;
            $article = $product->article;
        }

        $position = new OrderContent();
        $position->order_id = $post['order_id'];
        $position->product_id = $product_id;
        $position->quantity = $post['quantity'];
        $position->price = $price;
        $position->product_name = $product_name;
        $position->units = $units;
        $position->article = $article;

        $order = $position->order;
        if ($order->status == 6)
            throw new BadRequestHttpException('Access denied');

        if (!$position->save(false))
            throw new BadRequestHttpException('SaveError');

        $message = Yii::t('message', 'frontend.controllers.order.add_position', ['ru' => "<br/>добавил {prod} {quantity} {ed} по цене {productPrice} {currencySymbol}/{ed} ",
            'prod' => $position->product_name, 'productPrice' => $position->price, 'currencySymbol' => $order->currency->symbol, 'ed' => $position->product->ed, 'quantity' => $position->quantity]);

        $user = Yii::$app->user->getIdentity();
        $organizationType = $user->organization->type_id;

        if ($organizationType == Organization::TYPE_RESTAURANT) {
            $this->sendSystemMessage($user, $order->id, $order->client->name . Yii::t('message', 'frontend.controllers.order.change_details_four', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
            $subject = $order->client->name . ' изменил детали заказа №' . $order->id . ":" . str_replace('<br/>', ' ', $message);
            foreach ($order->recipientsList as $recipient) {
                $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);
                if (($recipient->organization_id == $order->vendor_id) && $profile->phone && $recipient->smsNotification->order_changed) {
                    $text = $subject;
                    $target = $profile->phone;
                    Yii::$app->sms->send($text, $target);
                }
            }
            $order->calculateTotalPrice();
            $order->save();
            $this->sendOrderChange($order->client, $order);
        } elseif ($organizationType == Organization::TYPE_SUPPLIER) {
            $order->calculateTotalPrice();
            $order->save();
            $this->sendSystemMessage($user, $order->id, $order->vendor->name . Yii::t('message', 'frontend.controllers.order.change_details_four', ['ru' => ' изменил детали заказа №']) . $order->id . ":$message");
            $this->sendOrderChange($order->vendor, $order);
            $subject = $order->vendor->name . ' изменил детали заказа №' . $order->id . ":" . str_replace('<br/>', ' ', $message);
            foreach ($order->client->users as $recipient) {
                $profile = \common\models\Profile::findOne(['user_id' => $recipient->id]);
                if ($profile->phone && $recipient->smsNotification->order_changed) {
                    $text = $subject;
                    $target = $profile->phone;
                    Yii::$app->sms->send($text, $target);
                }
            }
        }
        return true;
    }

    public function actionGridReport()
    {
        $this->actionSaveSelectedOrders();
        $selected = Yii::$app->session->get('selected', []);
        if (empty($selected))
            exit();

        $res = [];
        foreach ($selected as $page)
            if (count($page) > 0)
                $res = array_merge($res, $page);

        $selected = implode(',', $res);

        $sql = "SELECT org.id as id, org.parent_id as parent_id, concat_ws(', ',org.name, org.city, org.address) as client_name 
                    FROM `order` 
                    left join organization as org on org.id = `order`.client_id
                    where `order`.id in ($selected) group by `order`.client_id order by org.parent_id";

        $orgs = \Yii::$app->db->createCommand($sql)->queryAll();
        $sql = "SELECT cbg.product as '" . Yii::t('message', 'frontend.controllers.order.good', ['ru' => 'Наименование товара']) . "', cbg.ed as '" . Yii::t('message', 'frontend.controllers.order.mea', ['ru' => 'Ед.изм']) . "', ";
        $sql_ext = "SELECT `" . Yii::t('message', 'frontend.controllers.order.good', ['ru' => 'Наименование товара']) . "`, `" . Yii::t('message', 'frontend.controllers.order.mea', ['ru' => 'Ед.изм']) . "`, ";
        foreach ($orgs as $org) {
            $sql .= "IF(SUM(IF (`order`.client_id = " . $org['id'] . ", oc.quantity, 0)) = 0, '', CAST(SUM(IF (`order`.client_id = " . $org['id'] . ", oc.quantity, 0))as CHAR(10))) as '" . $org['client_name'] . "',";
            $sql_ext .= "SUM(`" . $org['client_name'] . "`) as '" . $org['client_name'] . "',";
        }

        $sql = substr($sql, 0, -1);
        $sql_ext = substr($sql_ext, 0, -1);

        $sql .= " from `order`
                    left join order_content as oc on oc.order_id = `order`.id
                    left join catalog_base_goods as cbg on cbg.id = oc.product_id
                    left join organization as org on org.id = `order`.client_id
                    where `order`.id in ($selected) and cbg.product is not null group by client_id, product_id  order by org.parent_id";

        $sql_ext .= " from ( " . $sql . " ) ww group by `" . Yii::t('message', 'frontend.controllers.order.good', ['ru' => 'Наименование товара']) . "`";

        $report = \Yii::$app->db->createCommand($sql_ext)->queryAll();

        $objPHPExcel = new \PHPExcel();
        $sheet = 0;
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->setTitle(Yii::t('message', 'frontend.controllers.order.grid-report', ['ru' => 'Cеточный отчет']));
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(60);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("B1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(135);

        $parent = 0;
        $col = 'C';
        $last_col = 'C';
        $start_grid_col = 'C';
        $grid = 1;
        foreach ($orgs as $org) {

            if ($org['parent_id'] != 0) {
                $start_grid_col = $col;
                $parent = $org['parent_id'];
            }
            if ($parent <> $org['parent_id']) {
                $parent = 0;
                $objPHPExcel->getActiveSheet()->mergeCells($start_grid_col . '2:' . $last_col . '2');
                $objPHPExcel->getActiveSheet()->setCellValue($start_grid_col . '2', Yii::t('message', 'frontend.controllers.order.org_grid', ['ru' => 'Сеть']) . " " . $grid);
                $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

                $objPHPExcel->getActiveSheet()->getStyle($start_grid_col . '2')->applyFromArray(
                    [
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => $color]
                        ]
                    ]
                );

                $grid++;
            }
            $last_col = $col;
            $col++;
        }

        $col = 'A';
        $row_data = 2;
        $last_col = 'A';

        foreach ($report[0] as $key => $data) {
            $last_col = $col;
            $objPHPExcel->getActiveSheet()->setCellValue($col . '1', $key);
            $col++;
            if ($data == null)
                ;
            $row_data = 3;
        }

        $objPHPExcel->getActiveSheet()->setCellValue($col . '1', Yii::t('message', 'frontend.controllers.order.grid-report.total-count', ['ru' => 'ОБЩЕЕ КОЛИЧЕСТВО']));
        $objPHPExcel->getActiveSheet()->getStyle($col . '1')->getFont()->setBold(true);

        for ($i = $row_data; $i <= (count($report) + 2); $i++) {
            $objPHPExcel->getActiveSheet()->setCellValue($col . $i, '=SUM(C' . $i . ':' . $last_col . $i . ')');
        }

        $objPHPExcel->getActiveSheet()->fromArray($report, NULL, 'A' . $row_data);

        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $col . '1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $col . (count($report) + 2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $col . (count($report) + 2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:A' . (count($report) + 2))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getStyle('A1:' . $col . (count($report) + 2))->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        $last_col++;
        $i--;

        $objPHPExcel->getActiveSheet()->getPageSetup()->setPrintArea('A1:' . $last_col . $i);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setScale(60);

        header('Content-Type: application/vnd.ms-excel');
        $filename = date("d-m-Y") . "_Grid_report.xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    public function actionSaveSelectedOrders()
    {
        $selected = Yii::$app->request->get('selected');
        $page = Yii::$app->request->get('page') + 1;

        $session = Yii::$app->session;
        $list = $session->get('selected', []);
        $list[$page] = !empty($selected) ? explode(",", $selected) : [];

        $session->set('selected', $list);
    }

    public function actionUploadAttachment($id)
    {
        $user = $this->currentUser;
        $order = Order::findOne(['id' => $id]);
        if (empty($order) || !(($order->client_id == $user->organization_id) || ($order->vendor_id == $user->organization_id))) {
            return '';
        }

        $attachment = new OrderAttachment;
        $uploadedFile = \yii\web\UploadedFile::getInstanceByName('attachment');
        //$uploadedFile->load(Yii::$app->request->post());

        $attachment->order_id = $id;
        $attachment->file = $uploadedFile;

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($attachment && $attachment->validate() && isset($attachment->dirtyAttributes['file']) && $attachment->file) {
            $attachment->save();
            return ['files' => [
                [
                    'name' => $uploadedFile->name,
                    'size' => $uploadedFile->size,
                    'url' => Url::to(['order/get-attachment', 'id' => $attachment->id], true),
                    'deleteUrl' => Url::to(['order/delete-attachment', 'id' => $attachment->id]),
                    'deleteType' => 'POST',
                ],
            ],
            ];
        }

        $files = [];
        foreach ($order->attachments as $attachment) {
            $files[] = [
                'name' => $attachment->file,
                'size' => $attachment->size,
                'url' => Url::to(['order/get-attachment', 'id' => $attachment->id], true),
                'deleteUrl' => Url::to(['order/delete-attachment', 'id' => $attachment->id]),
                'deleteType' => 'POST',
            ];
        }
        return ['files' => $files];
    }

    public function actionGetAttachment($id)
    {
        $attachment = OrderAttachment::findOne(['id' => $id]);
        $attachment->getFile();
    }

    public function actionDeleteAttachment($id)
    {
        $attachment = OrderAttachment::findOne(['id' => $id]);
        return $attachment->delete();
    }


    public function actionAjaxAddGoodQuantityToSession()
    {
        $key = str_replace('GuideProduct[', '', str_replace(']', '', Yii::$app->request->get('name')));
        $value = Yii::$app->request->get('quantity');
        $session = Yii::$app->session;
        $session['GuideProductCount.' . $key] = $value;
    }


    public function actionAjaxOrderUpdateWaybill()
    {
        $waybillNumber = Yii::$app->request->post('waybill_number') ?? null;
        $orderID = Yii::$app->request->post('order_id') ?? null;

        if (!$orderID)
            return 0;

        $order = Order::findOne(['id' => $orderID]);
        $order->waybill_number = $waybillNumber;
        $order->save();
        return $waybillNumber;
    }


    public function actionAjaxClearSession()
    {
        foreach ($_SESSION as $key => &$item) {
            if (strpos($key, 'uideProductCount')) {
                unset($_SESSION[$key]);
            }
        }
    }

}
