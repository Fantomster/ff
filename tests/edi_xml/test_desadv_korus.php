<?= '<?xml version="1.0" encoding="utf-8"?>'; ?>
<DESADV>
   <NUMBER><?= $order->id ?></NUMBER>
   <DATE>2018-11-02</DATE>
   <DELIVERYDATE>2018-11-02</DELIVERYDATE>
   <ORDERNUMBER><?= $order->id ?></ORDERNUMBER>
   <ORDERDATE>2018-11-02</ORDERDATE>
   <DELIVERYNOTENUMBER>99999999</DELIVERYNOTENUMBER>
   <DELIVERYNOTEDATE>2018-11-08</DELIVERYNOTEDATE>
   <HEAD>
      <SUPPLIER>2000000000776</SUPPLIER>
      <BUYER>2000000000777</BUYER>
      <DELIVERYPLACE>2000000000777</DELIVERYPLACE>
      <SENDER>2000000000776</SENDER>
      <RECIPIENT>2000000000777</RECIPIENT>
      <PACKINGSEQUENCE>
         <HIERARCHICALID>1</HIERARCHICALID>
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
                  <DELIVEREDQUANTITY>51</DELIVEREDQUANTITY>
                  <DELIVEREDUNIT>PR</DELIVEREDUNIT>
                  <ORDEREDQUANTITY>5</ORDEREDQUANTITY>
                  <COUNTRYORIGIN/>
                  <CUSTOMSTARIFFNUMBER/>
                  <PRICE>1.00</PRICE>
              </POSITION>
          <?php endforeach; ?>
      </PACKINGSEQUENCE>
   </HEAD>
</DESADV>