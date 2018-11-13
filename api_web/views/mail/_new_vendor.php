<?php 
    $client = $order->client;
    $manager = $order->createdBy;
?>
<table width="225" style="max-width: 225px;">
    <tr>
        <td valign="top" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;font-weight: 700;color: #000000;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 24px;font-weight: 700;color: #000000;">Заказчик</span>
        </td>
    </tr>
    <tr>
        <td align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 700;color: #000000;padding: 14px 0;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 700;color: #000000;"><?= $client->name ?></span>
        </td>
    </tr>
    <tr>
        <td align="left" valign="top" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;"><?= isset($manager->profile->full_name) ?  $manager->profile->full_name : ""?></span>
        </td>
    </tr>
    <tr>
        <td align="left" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;"><?= isset($manager->profile->phone) ?  $manager->profile->phone : ""?></span>
        </td>
    </tr>
    <tr>
        <td align="left" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;"><?= isset($manager->email) ?  $manager->email : ""?></span>
        </td>
    </tr>
    <tr>
        <td align="left" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;">
            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;color: #8c8f8d;"><?= $client->formatted_address ?></span>
        </td>
    </tr>
</table>