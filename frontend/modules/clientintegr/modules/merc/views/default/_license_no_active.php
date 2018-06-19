<?php

use yii\helpers\Html;

//echo '<strong>Активна</strong> ID: ' . $lic->code . ' (с ' . date("d-m-Y H:i:s", strtotime($lic->fd)) . ' по ' . date("d-m-Y H:i:s", strtotime($lic->td)) . ') ';
$timestamp_now=time();
($license->status_id==1) && ($timestamp_now<=(time($license->td))) ? $lic_merc=1 : $lic_merc=0;
if ($lic_merc==0) {
    ?>
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding">
                    <p>
                        Состояние лицензии:
                        <?php
                        print " Лицензия ВЕТИС Меркурий: ID ".$license->id." <strong><span style=\"color:#dd4b39\">Не активна. </span></strong>";
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
