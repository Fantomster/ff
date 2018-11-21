<?= '<?xml version="1.0" encoding="utf-8"?>'; ?>
<ORDRSP>
    <NUMBER><?= $order->id ?></NUMBER>
    <DATE>2018-11-01</DATE>
    <ORDERNUMBER><?= $order->id ?></ORDERNUMBER>
    <ORDERDATE>2018-11-01</ORDERDATE>
    <DELIVERYDATE>2018-11-01</DELIVERYDATE>
    <ACTION>4</ACTION>
    <HEAD>
        <BUYER>9864232240006</BUYER>
        <SUPPLIER>9864232239956</SUPPLIER>
        <DELIVERYPLACE>9864232240006</DELIVERYPLACE>
        <SENDER>9864232239956</SENDER>
        <RECIPIENT>9864232240006</RECIPIENT>
        <?php
        $i = 1;
        foreach ($order->orderContent as $position): ?>
            <?php $product = \common\models\CatalogBaseGoods::findOne(['id' => $position['product_id']]);
            $catalogGood = \common\models\CatalogGoods::findOne(['base_goods_id' => $product->id]);
            $barcode = $product->barcode;
            $edi_supplier_article = (isset($product->edi_supplier_article) && $product->edi_supplier_article != '') ? $product->edi_supplier_article : $position['id'];
            $article = (isset($product->article) && $product->article != '') ? $product->article : $position['id'];
            if (!$barcode) continue;
            ?>
            <POSITION>
                <POSITIONNUMBER><?= $i++ ?></POSITIONNUMBER>
                <PRODUCT><?= $barcode ?></PRODUCT>
                <PRODUCTIDBUYER><?= $position['id'] ?></PRODUCTIDBUYER>
                <PRODUCTIDSUPPLIER><?= $edi_supplier_article ?? '' ?></PRODUCTIDSUPPLIER>
                <ORDRSPUNIT>KGM</ORDRSPUNIT>
                <DESCRIPTION><?= $position['product_name'] ?></DESCRIPTION>
                <PRICE>57.00000</PRICE>
                <PRICEWITHVAT>5.00000</PRICEWITHVAT>
                <VAT>10</VAT>
                <PRODUCTTYPE>2</PRODUCTTYPE>
                <ORDEREDQUANTITY>5.000</ORDEREDQUANTITY>
                <ACCEPTEDQUANTITY>75.000</ACCEPTEDQUANTITY>
            </POSITION>
        <?php endforeach; ?>
    </HEAD>
</ORDRSP>
