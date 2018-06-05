<?= '<?xml version="1.0" encoding="utf-8"?>'; ?>
<RECADV>
    <NUMBER><?= $order->id ?></NUMBER>
    <DATE><?= $dateArray['created_at'] ?></DATE>
    <DELIVERYDATE><?= $dateArray['requested_delivery_date'] ?></DELIVERYDATE>
    <ORDERNUMBER><?= $order->id ?></ORDERNUMBER>
    <ORDERDATE><?= $dateArray['created_at'] ?></ORDERDATE>
    <DELIVERYNOTENUMBER><?= $order->ediOrder->invoice_number ?? $order->id ?></DELIVERYNOTENUMBER>
    <DELIVERYNOTEDATE><?= $order->ediOrder->invoice_date ?? $dateArray['requested_delivery_date'] ?></DELIVERYNOTEDATE>
    <WAYBILLNUMBER><?= $order->id ?></WAYBILLNUMBER>
    <WAYBILLDATE><?= $dateArray['requested_delivery_date'] ?></WAYBILLDATE>
    <HEAD>
        <SUPPLIER><?= $vendor->ediOrganization->gln_code ?></SUPPLIER>
        <BUYER><?= $client->ediOrganization->gln_code ?></BUYER>
        <DELIVERYPLACE><?= $client->ediOrganization->gln_code ?></DELIVERYPLACE>
        <SENDER><?= $client->ediOrganization->gln_code ?></SENDER>
        <RECIPIENT><?= $vendor->ediOrganization->gln_code ?></RECIPIENT>
        <PACKINGSEQUENCE>
            <HIERARCHICALID><?= $order->id ?></HIERARCHICALID>
            <?php
            $i = 1;
            foreach ($orderContent as $position): ?>
                <?php $product = \common\models\CatalogBaseGoods::findOne(['id' => $position['product_id']]);
                $barcode = $product->barcode;
                $edi_supplier_article = $product->edi_supplier_article ?? $position['id'];
                $article = $product->article ?? $position['id'];
                if (!$barcode) continue;
                ?>
                <POSITION>
                    <POSITIONNUMBER><?= $i++ ?></POSITIONNUMBER>
                    <PRODUCT><?= $barcode ?></PRODUCT>
                    <PRODUCTIDBUYER><?= $article ?></PRODUCTIDBUYER>
                    <PRODUCTIDSUPPLIER><?= $edi_supplier_article ?></PRODUCTIDSUPPLIER>
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
        </PACKINGSEQUENCE>
    </HEAD>
</RECADV>