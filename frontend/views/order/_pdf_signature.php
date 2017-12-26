<?php
    $width_line = 15;
    $line = str_pad('', $width_line, '_');
?>
<div style="width:350px;float: right;">
    <table>
        <tr>
            <td style="font-size: 12px;">
                <?= Yii::t('message', 'frontend.views.order.signature') ?>: <?=$line?>
            </td>
            <td width="50">

            </td>
            <td style="font-size: 12px;">
                <?= Yii::t('message', 'frontend.views.order.current_date') ?>: <?=$line?>
            </td>
        </tr>
    </table>
</div>
