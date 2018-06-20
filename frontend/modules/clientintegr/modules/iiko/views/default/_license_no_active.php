<?php

use yii\helpers\Html;

//echo '<strong>Активна</strong> ID: ' . $lic->code . ' (с ' . date("d-m-Y H:i:s", strtotime($lic->fd)) . ' по ' . date("d-m-Y H:i:s", strtotime($lic->td)) . ') ';
$timestamp_now=time();
($lic->status_id==1) && ($timestamp_now<=(time($lic->td))) ? $lic_iiko=1 : $lic_iiko=0;
if ($lic_iiko==0) {
    ?>
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding">
                    <p>
                        Состояние лицензии:
                        <?php
                        print " Лицензия IIKO: ID ".$lic->id." <strong><span style=\"color:#dd4b39\">Не активна. </span></strong>";
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
