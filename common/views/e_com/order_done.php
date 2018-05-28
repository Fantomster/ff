<?= '<?xml version="1.0" encoding="utf-8"?>'; ?>
<RECADV>
    <NUMBER><?= $order->id ?></NUMBER>
    <DATE><?= $dateArray['created_at'] ?></DATE>
    <DELIVERYDATE><?= $dateArray['requested_delivery_date'] ?></DELIVERYDATE>
    <ORDERNUMBER><?= $order->id ?></ORDERNUMBER>
    <ORDERDATE><?= $dateArray['created_at'] ?></ORDERDATE>
    <DELIVERYNOTENUMBER><?= $order->invoice_number ?? $order->id ?></DELIVERYNOTENUMBER>
    <DELIVERYNOTEDATE><?= $order->invoice_date ?? $dateArray['requested_delivery_date'] ?></DELIVERYNOTEDATE>
    <WAYBILLNUMBER><?= $order->id ?></WAYBILLNUMBER>
    <WAYBILLDATE><?= $dateArray['requested_delivery_date'] ?></WAYBILLDATE>
    <HEAD>
        <SUPPLIER><?= $vendor->gln_code ?></SUPPLIER>
        <BUYER><?= $client->gln_code ?></BUYER>
        <DELIVERYPLACE><?= $client->gln_code ?></DELIVERYPLACE>
        <SENDER><?= $client->gln_code ?></SENDER>
        <RECIPIENT><?= $vendor->gln_code ?></RECIPIENT>
        <HIERARCHICALID><?= $order->id ?></HIERARCHICALID>
        <?php
        $i = 1;
        foreach ($orderContent as $position): ?>
            <?php $product = \common\models\CatalogBaseGoods::findOne(['id' => $position['product_id']]);
            $barcode = $product->barcode;
            if (!$barcode)continue;
            ?>
            <POSITION>
                <POSITIONNUMBER><?= $i++ ?></POSITIONNUMBER>
                <PRODUCT><?= $barcode ?></PRODUCT>
                <PRODUCTIDBUYER><?= $position['id'] ?></PRODUCTIDBUYER>
                <PRODUCTIDSUPPLIER><?= $position['edi_supplier_article'] ?? $position['id'] ?></PRODUCTIDSUPPLIER>
                <DELIVEREDQUANTITY><?= $position['quantity'] ?></DELIVEREDQUANTITY>
                <ORDEREDQUANTITY><?= $position['quantity'] ?></ORDEREDQUANTITY>
                <DELIVEREDUNIT><?= $position['units'] ?></DELIVEREDUNIT>
                <ORDERUNIT><?= $position['units'] ?></ORDERUNIT>
                <EGAISCODE><?= $position['id'] ?></EGAISCODE>
                <EGAISQUANTITY><?= $position['quantity'] ?></EGAISQUANTITY>
                <PRICE><?= $position['price'] ?></PRICE>
                <PRICEWITHVAT><?= $position['price'] ?></PRICEWITHVAT>
                <TAXRATE>0</TAXRATE>
            </POSITION>
        <?php endforeach; ?>
    </HEAD>
</RECADV>