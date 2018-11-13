<?php

use yii\widgets\Breadcrumbs;

$script = <<< JS
$("document").ready(function() {
    setInterval(function() {     
       $.pjax.reload({container:"#dics_pjax",timeout: 16000});
    }, 10000); 
});
JS;
$this->registerJs($script);
?>

<style>
    .bg-default {
        background: #555
    }

    p {
        margin: 0;
    }

    #map {
        width: 100%;
        height: 200px;
    }
</style>

<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с Tillypad
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links'   => [
            [
                'label' => 'Интеграция',
                'url'   => ['/clientintegr/default'],
            ],
            'Интеграция с Tillypad',
        ],
    ])
    ?>
</section>
<section class="content-header">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Панель управления</h3>
            </div>
            <div class="box-body">

                <div class="hpanel">
                    <div class="panel-body">
                        <div class="col-md-8 text-left">
                            <span style="color:red;">Лицензия не активна.</span><br> Обратитесь к менеджерам по
                            сопровождению для получения дополнительной информации!
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>

