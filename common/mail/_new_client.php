<?php
    $vendor = $order->vendor;
?>
<table width="225" style="max-width: 225px;">
    <tr>
        <td valign="top" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;font-weight: 700;color: #000000;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;font-weight: 700;color: #000000;"><?= Yii::t('app', 'common.mail.order_processing.vendor', ['ru' => 'Поставщик']) ?></span>
        </td>
    </tr>
    <tr>
        <td align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 700;color: #000000;padding: 20px 0;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 700;color: #000000;"><?= $vendor->name ?></span>
        </td>
    </tr>
    <tr>
        <td align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;"></span>
        </td>
    </tr>
    <tr>
        <td align="left" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;"><?= $vendor->phone ?></span>
        </td>
    </tr>
    <tr>
        <td align="left" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;"><?= $vendor->email ?></span>
        </td>
    </tr>
    <tr>
        <td align="left" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;"><?= $vendor->formatted_address ?></span>
        </td>
    </tr>
</table>