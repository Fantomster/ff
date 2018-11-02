<?= '<?xml version="1.0" encoding="utf-8"?>'; ?>
<RECADV>
    <NUMBER><?= $order->id ?></NUMBER>
    <DOCACTION><?= Yii::$app->params['edi_api_data']['edi_api_recadv_document_id'] ?></DOCACTION>
    <DATE><?= $dateArray['created_at'] ?></DATE>
    <DELIVERYDATE><?= $dateArray['requested_delivery_date'] ?></DELIVERYDATE>
    <ORDERNUMBER><?= $order->id ?></ORDERNUMBER>
    <ORDERDATE><?= $dateArray['created_at'] ?></ORDERDATE>
    <?php $oneContent = \common\models\OrderContent::findOne(['order_id' => $order->id]) ?>
    <DELIVERYNOTENUMBER><?= $oneContent->edi_number ?></DELIVERYNOTENUMBER>
    <DELIVERYNOTEDATE><?= $order->edi_doc_date ?? $dateArray['requested_delivery_date'] ?></DELIVERYNOTEDATE>
    <WAYBILLNUMBER><?= $order->id ?></WAYBILLNUMBER>
    <WAYBILLDATE><?= $dateArray['requested_delivery_date'] ?></WAYBILLDATE>
    <RECEPTIONDATE><?= $dateArray['requested_delivery_date'] ?? '' ?></RECEPTIONDATE>
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
                $measure = $product->ed ?? 'шт';
                $catalogGood = \common\models\CatalogGoods::findOne(['base_goods_id' => $product->id]);
                $barcode = $product->barcode;
                $vat = isset($catalogGood->vat) ? $catalogGood->vat : 0;
                $priceWithVat = $position['price'] + ($position['price'] * $vat / 100);
                $edi_supplier_article = (isset($product->edi_supplier_article) && $product->edi_supplier_article != '') ? $product->edi_supplier_article : $position['id'];
                $article = (isset($product->article) && $product->article != '') ? $product->article : $position['id'];
                if (!$barcode) continue;
                ?>
                <POSITION>
                    <POSITIONNUMBER><?= $i++ ?></POSITIONNUMBER>
                    <PRODUCT><?= $barcode ?></PRODUCT>
                    <PRODUCTIDBUYER><?= $position['id'] ?></PRODUCTIDBUYER>
                    <PRODUCTIDSUPPLIER><?= $edi_supplier_article ?></PRODUCTIDSUPPLIER>
                    <DELIVEREDQUANTITY><?= $position['quantity'] ?></DELIVEREDQUANTITY>
                    <ORDEREDQUANTITY><?= $position['plan_quantity'] ?></ORDEREDQUANTITY>
                    <ACCEPTEDQUANTITY><?= $position['edi_shipment_quantity'] ?></ACCEPTEDQUANTITY>
                    <ORDERUNIT><?= $measure ?></ORDERUNIT>
                    <EGAISCODE><?= $position['id'] ?></EGAISCODE>
                    <EGAISQUANTITY><?= $position['quantity'] ?></EGAISQUANTITY>
                    <PRICE><?= $position['price'] ?></PRICE>
                    <PRICEWITHVAT><?= $priceWithVat ?></PRICEWITHVAT>
                    <TAXRATE><?= $vat ?></TAXRATE>
                    <BUYERPARTNUMBER><?= $article ?? '' ?></BUYERPARTNUMBER>
                    <DESCRIPTION><?= $position['product_name'] ?></DESCRIPTION>
                </POSITION>
            <?php endforeach; ?>
        </PACKINGSEQUENCE>
    </HEAD>
</RECADV>
