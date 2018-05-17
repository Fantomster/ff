<?php

use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use kartik\grid\GridView;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с системой ВЕТИС "Меркурий"
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
            'Интеграция с iiko Office',
        ],
    ])
    ?>
</section>

<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
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
    СПРАВОЧНИКИ:
</section>
<section class="content-header">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="panel-body">
                <div class="box-body table-responsive no-padding grid-category">
                    <?php
                    Pjax::begin(['id' => 'pjax-messages-list', 'enablePushState' => true,'timeout' => 15000, 'scrollTo' => true]);
                    echo GridView::widget([
                        'id' => 'vetDocumentsList',
                        'dataProvider' => $dataProvider,
                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                        //'filterModel' => $searchModel,
                        'filterPosition' => false,
                        'summary' => '',
                        'options' => ['class' => 'table-responsive'],
                        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                        'columns' => [
                            /*[
                                'attribute' => 'number',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['number'];
                                },
                            ],*/
                            [
                                'attribute' => 'date_doc',
                                'header' => 'Дата',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['date_doc'];
                                },
                            ],
                            [
                                'attribute' => 'type',
                                'header' => 'Тип ВСД',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['type'];
                                },
                            ],
                            [
                                'attribute' => 'product_name',
                                'header' => 'Наименование продукции',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['product_name'];
                                },
                            ],
                            [
                                'attribute' => 'amount',
                                'header' => 'Объем',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['amount'];
                                },
                            ],
                            [
                                'attribute' => 'production_date',
                                'header' => 'Дата выработки',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['production_date'];
                                },
                            ],
                            [
                                'attribute' => 'recipient_name',
                                'header' => ' 	Фирма-отправитель',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return $data['recipient_name'];
                                },
                            ],
                        ],
                    ]);
                    Pjax::end();
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
$js = <<< JS
    $(function () {
        $('.grid-category').on('click', '.get-content-sync', function () {
            var url = $(this).data('url');
            var id = $(this).data('id');
            swal({
                title: 'Выполнить загрузку данных?',
                type: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Загрузить',
                cancelButtonText: 'Отмена',
            }).then((result) => {
                if(result.value)
                {
                    swal({
                        title: 'Синхронизация',
                        text: 'Подождите пока закончится загрузка...',
                        onOpen: () => {
                            swal.showLoading();
                            $.post(url, {id:id}, function (data) {
                                if (data.success === true) {
                                    swal.close();
                                    swal('Готово', '', 'success')
                                } else {
                                    console.log(data);
                                    swal(
                                        'Ошибка',
                                        'Обратитесь в службу поддержки.',
                                        'error'
                                    )
                                }
                                $.pjax.reload({container:"#dics_pjax", timeout:2000});
                            });
                        }
                    })
                }
            })
        });
    });
JS;

$this->registerJs($js);
?>
