<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use kartik\grid\GridView;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Просмотр ВСД
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr/default'],
            ],
            [
                'label' => 'Интеграция с системой ВЕТИС "Меркурий"',
                'url' => ['/clientintegr/merc/default'],
            ],
            'Просмотр ВСД',
        ],
    ])
    ?>
</section>

<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding">
                    <p>
                        Состояние лицензии:
                        <?php echo '<strong>Активна</strong> ID: ' . $lic->code . ' (с ' . date("d-m-Y H:i:s", strtotime($lic->fd)) . ' по ' . date("d-m-Y H:i:s", strtotime($lic->td)) . ') '; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">



                </div>
            </div>
        </div>
    </div>
</section>