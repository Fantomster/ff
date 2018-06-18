<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\widgets\ListView;
use kartik\grid\GridView;
use kartik\editable\Editable;
use api\common\models\RkAccess;


?>


<style>
.bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с внешними системами 
        <small>Обменивайтесь номенклатурой и приходными документами с Вашей учетной системой автоматически</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Интеграция',
        ],
    ])
    ?>
</section>
<?php
$user = Yii::$app->user->identity;
$licenses = $user->organization->getLicenseList();
//print "<pre>";
//print_r($licenses);
//print "</pre>";
$timestamp_now=time();
($licenses['rkws']->status_id==1) && ($timestamp_now<=(time($licenses['rkws']->td))) ? $rk_us=1 : $rk_us=0;
($licenses['rkws_ucs'][0]['status_id']==1) && ($timestamp_now<=(time($licenses['rkws_ucs'][0]['td']))) ? $rk_lic=1 : $rk_lic=0;
($licenses['iiko']->status_id==2) && ($timestamp_now<=(time($licenses['iiko']->td))) ? $lic_iiko=1 : $lic_iiko=0;
($licenses['mercury']->status_id==2) && ($timestamp_now<=(time($licenses['mercury']->td))) ? $lic_merc=1 : $lic_merc=0;
?>
<section class="content">
<div class="catalog-index">
    	<div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Партнёры по интеграции</h3>
            </div>
            <?php if(isset($licenses['rkws'])): ?>
            <div class="box-body">
                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-7 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">R-Keeper</h4>', ['rkws/default']) ?>
                            <p class="small">Интеграция с R-keeper STORE HOUSE через White Server (облачная версия)</p>
                        </div>
                        <div class="col-md-3 text-left">
                            <?php if ($rk_us==1) {
                                print "<p class=\"small\"> Лицензия MixCart: ID ".$licenses['rkws']->id." <strong><span style=\"color:#6ea262\">Активна </span></strong>";
                                print 'по '.$licenses['rkws']->td."</br>";
                            } else {
                                print "<p class=\"small\"> Лицензия MixCart: <strong><span style=\"color:#dd4b39\">Не активна. </span></strong></br>";
                                print "Пожалуйста, обратитесь к вашему менеджеру MixCart.</p></br>";
                            }
                            if ($rk_lic==1) {
                                print "<p class=\"small\"> Лицензия UCS: ID ".$licenses['rkws_ucs'][0]['code']." <strong><span style=\"color:#6ea262\">Активна </span></strong>";
                                print 'по '.$licenses['rkws_ucs'][0]['td'];
                            } else {
                                print "<p class=\"small\"> Лицензия MixCart: <strong><span style=\"color:#dd4b39\">Не активна. </span></strong></br>";
                                print "Пожалуйста, обратитесь к вашему дилеру UCS.</p>";
                            }
                            ?>
                        </div>
                        <div class="col-md-2 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'],['class'=>'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if(isset($licenses['iiko'])): ?>
            <div class="box-body">
                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">iiko Office</h4>', ['iiko/default']) ?>
                            <p class="small">Интеграция с iiko Office</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'],['class'=>'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php // if(isset($licenses['email'])): ?>
            <div class="box-body">
                <div class="hpanel" >
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info">Накладные поставщика</h4>', ['email/default']) ?>
                            <p class="small">Загрузка накладных из 1С с помощью EMAIL</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> Документация', ['#'],['class'=>'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
             </div>
            <?php // endif; ?>
            <?php if(isset($licenses['mercury'])): ?>
             <div class="box-body">
                <div class="hpanel" >
                    <div class="panel-body">
                        <div class="col-md-6 text-left">
                            <?= Html::a('<h4 class="m-b-xs text-info"> '.Yii::t('message', 'frontend.client.integration.mercury.title', ['ru'=>'ВЕТИС "Меркурий"']).'</h4>', ['merc/settings']) ?>
                            <p class="small"><?= Yii::t('message', 'frontend.client.integration.mercury', ['ru'=>'Интеграция с системой ВЕТИС "Меркурий"']) ?></p>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= Html::a('<i class="fa fa-pencil" aria-hidden="true"></i> '.Yii::t('message', 'frontend.client.integration.mercury.documentation', ['ru'=>'Документация']), ['#'],['class'=>'btn btn-default btn-sm m-t']) ?>
                        </div>
                    </div>
                </div>
          </div>
          <?php endif; ?>
</div>
</section>

