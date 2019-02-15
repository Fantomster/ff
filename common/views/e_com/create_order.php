<?= '<?xml version="1.0" encoding="utf-8"?>'; ?>
<ORDER>
    <DOCUMENTNAME><?= Yii::$app->params['edi_api_data']['edi_api_order_document_id'] ?></DOCUMENTNAME>
    <NUMBER><?= $order->id ?></NUMBER>
    <DATE><?= $dateArray['created_at'] ?></DATE>
    <DELIVERYDATE><?= $dateArray['requested_delivery_date'] ?></DELIVERYDATE>
    <CURRENCY><?= $order->currency->iso_code ?></CURRENCY>
    <SUPORDER><?= $order->id ?></SUPORDER>
    <DOCTYPE><?= $order->getEdiOrderDocType() ?></DOCTYPE>
    <CAMPAIGNNUMBER><?= $order->id ?></CAMPAIGNNUMBER>
    <ORDRTYPE>ORIGINAL</ORDRTYPE>
    <INFO><?= $order->comment ?? "" ?></INFO>
    <HEAD>
        <SUPPLIER><?= $glnArray['vendor_gln'] ?></SUPPLIER>
        <BUYER><?= $glnArray['client_gln'] ?></BUYER>
        <DELIVERYPLACE><?= $glnArray['client_gln'] ?></DELIVERYPLACE>
        <SENDER><?= $glnArray['client_gln'] ?></SENDER>
        <RECIPIENT><?= $glnArray['vendor_gln'] ?></RECIPIENT>
        <EDIINTERCHANGEID><?= $order->id ?></EDIINTERCHANGEID>
        <?php
        $i = 1;
        foreach ($orderContent as $position): ?>
            <?php $product = \common\models\CatalogBaseGoods::findOne(['id' => $position['product_id']]);
            $measure = $product->ed ?? 'шт';
            $measure = \common\models\OuterUnit::getOuterName($measure, \api_web\components\Registry::EDI_SERVICE_ID);
            $catalogGood = \common\models\CatalogGoods::findOne(['base_goods_id' => $product->id]);
            $barcode = $product->barcode;
            $vat = isset($catalogGood->vat) ? $catalogGood->vat : 0;
            $edi_supplier_article = (isset($product->edi_supplier_article) && $product->edi_supplier_article != '') ? $product->edi_supplier_article : $position['id'];
            $article = (isset($product->article) && $product->article != '') ? $product->article : $position['id'];
            if (!$barcode) {
                continue;
            }
            ?>
            <POSITION>
                <POSITIONNUMBER><?= $i++ ?></POSITIONNUMBER>
                <PRODUCT><?= $barcode ?></PRODUCT>
                <PRODUCTIDBUYER><?= $product['id'] ?></PRODUCTIDBUYER>
                <PRODUCTIDSUPPLIER><?= $edi_supplier_article ?? '' ?></PRODUCTIDSUPPLIER>
                <ORDEREDQUANTITY><?= $position['quantity'] ?></ORDEREDQUANTITY>
                <ORDERUNIT><?= $measure ?></ORDERUNIT>
                <ORDERPRICE><?= $position['price'] ?></ORDERPRICE>
                <VAT><?= $vat ?></VAT>
                <INFO><?= $position['comment'] ?? "" ?></INFO>
                <CHARACTERISTIC>
                    <DESCRIPTION><?= $position['product_name'] ?></DESCRIPTION>
                </CHARACTERISTIC>
            </POSITION>
        <?php endforeach; ?>
    </HEAD>
</ORDER>
