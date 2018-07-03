<?php

use yii\helpers\Html;

//echo '<strong>Активна</strong> ID: ' . $lic->code . ' (с ' . date("d-m-Y H:i:s", strtotime($lic->fd)) . ' по ' . date("d-m-Y H:i:s", strtotime($lic->td)) . ') ';
$timestamp_now=time();
$sub0 = explode(' ',$lic->td);
$sub1 = explode('-',$sub0[0]);
$lic->td = $sub1[2].'.'.$sub1[1].'.'.$sub1[0];
$lic_iiko = 0;
if ($lic->status_id==0) $lic_iiko=0;
if (($lic->status_id==1) and ($timestamp_now<=(strtotime($lic->td)))) $lic_iiko=3;
if (($lic->status_id==1) and (($timestamp_now+14*86400)>(strtotime($lic->td)))) $lic_iiko=2;
if (($lic->status_id==1) and ($timestamp_now>(strtotime($lic->td)))) $lic_iiko=1;
if ($lic_iiko!=3) {
    ?>
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding">
                    <p>
                        Состояние лицензии:
                        <?php
                        switch($lic_iiko) {
                            case 0: print "Лицензия 1С: ID ".$lic->id." <strong><span style=\"color:#dd4b39\">Не активна</span></strong>.</br>";
                                print "Пожалуйста, обратитесь к вашему менеджеру MixCart."; break;
                            case 1: print "Лицензия 1С: ID ".$lic->id." <strong><span style=\"color:#dd4b39\">Не активна </span></strong>с ".$lic->td.".</br>";
                                print "Пожалуйста, обратитесь к вашему менеджеру MixCart."; break;
                            case 2: print "Лицензия 1С: ID ".$lic->id." <strong><span style=\"color:#dd4b39\">Истекает срок </span></strong>(по ".$lic->td."). </br>";
                                print "Пожалуйста, обратитесь к вашему менеджеру MixCart."; break;
                        }
                        //print " Лицензия IIKO: ID ".$lic->id." <strong><span style=\"color:#dd4b39\">Не активна. </span></strong>";
                        //print "Пожалуйста, обратитесь к вашему менеджеру MixCart.";
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
