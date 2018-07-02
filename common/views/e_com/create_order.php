<?= '<?xml version="1.0" encoding="utf-8"?>'; ?>
<ORDER>
    <DOCUMENTNAME>220</DOCUMENTNAME>
    <NUMBER><?= $order->id ?></NUMBER>
    <DATE><?= $dateArray['created_at'] ?></DATE>
    <DELIVERYDATE><?= $dateArray['requested_delivery_date'] ?></DELIVERYDATE>
    <CURRENCY><?= $order->currency->iso_code ?></CURRENCY>
    <SUPORDER><?= $order->id ?></SUPORDER>
    <DOCTYPE>O</DOCTYPE>
    <CAMPAIGNNUMBER><?= $order->id ?></CAMPAIGNNUMBER>
    <ORDRTYPE>ORIGINAL</ORDRTYPE>
    <HEAD>
        <SUPPLIER><?= $vendor->ediOrganization->gln_code ?></SUPPLIER>
        <BUYER><?= $client->ediOrganization->gln_code ?></BUYER>
        <DELIVERYPLACE><?= $client->ediOrganization->gln_code ?></DELIVERYPLACE>
        <SENDER><?= $client->ediOrganization->gln_code ?></SENDER>
        <RECIPIENT><?= $vendor->ediOrganization->gln_code ?></RECIPIENT>
        <EDIINTERCHANGEID><?= $order->id ?></EDIINTERCHANGEID>
        <?php
        $i = 1;
        foreach ($orderContent as $position): ?>
            <?php $product = \common\models\CatalogBaseGoods::findOne(['id' => $position['product_id']]);
                $barcode = $product->barcode;
                $edi_supplier_article = $product->edi_supplier_article ?? $position['id'];
                $article = $product->article ?? $position['id'];
            if (!$barcode)continue;
            ?>
            <POSITION>
                <POSITIONNUMBER><?= $i++ ?></POSITIONNUMBER>
                <PRODUCT><?= $barcode ?></PRODUCT>
                <PRODUCTIDBUYER><?= $article ?></PRODUCTIDBUYER>
                <PRODUCTIDSUPPLIER><?= $edi_supplier_article ?></PRODUCTIDSUPPLIER>
                <ORDEREDQUANTITY><?= $position['quantity']  ?></ORDEREDQUANTITY>
                <ORDERUNIT><?= $position['units']  ?></ORDERUNIT>
                <ORDERPRICE><?= $position['price']  ?></ORDERPRICE>
                <CHARACTERISTIC>
                    <DESCRIPTION><?= $position['product_name']  ?></DESCRIPTION>
                </CHARACTERISTIC>
            </POSITION>
        <?php endforeach; ?>
    </HEAD>
</ORDER>