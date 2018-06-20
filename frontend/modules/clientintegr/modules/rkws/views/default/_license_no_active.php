<?php

use yii\helpers\Html;

/*echo '<strong>Активна</strong> ID: ' . $lic->code . ' (с ' . date("d-m-Y H:i:s", strtotime($lic->fd)) . ' по ' . date("d-m-Y H:i:s", strtotime($lic->td)) . ') ';*/
$timestamp_now=time();
($licucs->status_id==1) && ($timestamp_now<=(time($licucs->td))) ? $lic_rkws_ucs=1 : $lic_rkws_ucs=0;
if ($lic_rkws_ucs==0) {
    ?>
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding">
                    <p>
                        Состояние лицензии:
                        <?php
                        print " Лицензия UCS: ID ".$licucs->code." <strong><span style=\"color:#dd4b39\">Не активна. </span></strong>";
                        print "Пожалуйста, обратитесь к вашему <a href=\"https://www.ucs.ru/dealers/\" target=\"_blanc\">дилеру UCS</a>.";
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php
}
//echo '<strong>Активна</strong> ID: ' . $lic->code . ' (с ' . date("d-m-Y H:i:s", strtotime($lic->fd)) . ' по ' . date("d-m-Y H:i:s", strtotime($lic->td)) . ') ';
$timestamp_now=time();
(($lic->status_id==1) && ($timestamp_now<=(time($lic->td)))) ? $lic_rkws=1 : $lic_rkws=0;
if ($lic_rkws==0) {
    ?>
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding">
                    <p>
                        Состояние лицензии:
                        <?php
                        print " Услуга Mixcart: ID ".$lic->id." <strong><span style=\"color:#dd4b39\">Не активна. </span></strong>";
                        print "Пожалуйста, обратитесь к вашему менеджеру MixCart.";
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
