<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\widgets\DatePicker;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use dosamigos\chartjs\ChartJs;

$this->registerJs('
    $("document").ready(function(){
        var justSubmitted = false;
        $(document).on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#orderStatForm").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
    });
        ');
?>
<section class="content-header">
    <h1>
        <i class="fa fa-signal"></i> <?= Yii::t('app', 'Аналитика') ?>
        <small><?= Yii::t('app', 'Статистика поставщика') ?> <?= $vendor->name ?></small>
    </h1>
    <?= ''
//    Breadcrumbs::widget([
//        'options' => [
//            'class' => 'breadcrumb',
//        ],
//        'links' => [
//            'Аналитика'
//        ],
//    ])
    ?>
</section>
<section class="content">
<?php
    Pjax::begin(['enablePushState' => false, 'id' => 'orderStat',]);
    $form = ActiveForm::begin([
                'options' => [
                    'data-pjax' => true,
                    'id' => 'orderStatForm',
                ],
                'method' => 'post',
    ]);
    ?>
    <div class="box box-info order-history">
    <!-- /.box-header -->
    <div class="box-body">
        <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$headerStats["ordersCount"];?></span>
                    <span class="info-box-text"><?= Yii::t('app', 'Всего заказов') ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$headerStats["goodsCount"];?></span>
                    <span class="info-box-text"><?= Yii::t('app', 'Всего товаров') ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$headerStats["clientsCount"]?></span>
                    <span class="info-box-text"><?= Yii::t('app', 'Всего клиентов') ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$headerStats["totalTurnover"];?> <i class="fa fa-fw fa-rub"></i></span>
                    <span class="info-box-text"><?= Yii::t('app', 'Оборот') ?></span>
                </div>
            </div>
        </div>
        </div>
        <div class="col-lg-5 col-md-6 col-sm-6"> 
                    <?= Html::label(Yii::t('app', 'Начальная дата / Конечная дата'), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <div class="form-group" style="width: 300px; height: 44px;">
            <?=
            DatePicker::widget([
                'name' => 'date',
                'name2' => 'date2',
                'value' => $dateFilterFrom,
                'value2' => $dateFilterTo,
                'options' => ['placeholder' => Yii::t('app', 'Начальная Дата'), 'id' => 'dateFrom'],
                'options2' => ['placeholder' => Yii::t('app', 'Конечная дата'), 'id' => 'dateTo'],
                'separator' => '-',
                'type' => DatePicker::TYPE_RANGE,
                'pluginOptions' => [
                    'format' => 'dd.mm.yyyy', //'d M yyyy',//
                    'autoclose' => true,
                    'endDate' => "0d",
                ]
            ])
            ?>
                    </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
      <!-- AREA CHART -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><?= Yii::t('app', 'Объем заказов') ?></h3>
        </div>
        <div class="box-body" style="display: block;">
                    <?=
                    ChartJs::widget([
                        'type' => 'line',
                        'options' => [
                            'height' => 400,
                            'width' => 1200,
                        ],
                        'data' => [
                            'labels' => $dayLabels,
                            'datasets' => [
                                [
                                    'label' => Yii::t('app', 'Все заказы'),
                                    'backgroundColor' => "rgba(54,140,191,.2)",
                                    'borderColor' => "rgba(54,140,191,.8)",
                                    'pointBackgroundColor' => "rgba(54,140,191,1)",
                                    'pointBorderColor' => "rgba(54,140,191,1)",
                                    'pointHoverBackgroundColor' => "rgba(54,140,191,1)",
                                    'pointHoverBorderColor' => "rgba(54,140,191,1)",
                                    'data' => $dayTurnover,
                                    'spanGaps' => true,
                                    'borderJoinStyle' => 'miter',
                                    'fill' => false,
                                ],
                            ]
                        ],
                    ])
                    ?>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <div class="col-md-12">
      <!-- AREA CHART -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><?= Yii::t('app', 'Объем по клиентам') ?></h3>
        </div>
        <div class="box-body" style="display: block;">
                    <?=
                    ChartJs::widget([
                        'type' => 'bar',
                        'options' => [
                            'height' => 400,
                            'width' => 1200,
                        ],
                        'data' => [
                            'labels' => $clientsTurnover['labels'],
                            'datasets' => [
                                [
                                    'label' => Yii::t('app', 'Общий оборот'),
                                    'backgroundColor' => $clientsTurnover['colors'],
                                    'borderColor' => $clientsTurnover['colors'],
                                    'data' => $clientsTurnover['stats'],
                                ],
                            ]
                        ],
                    ])
                    ?>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <div class="col-md-12">
      <!-- AREA CHART -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><?= Yii::t('app', 'Статистика по товарам') ?></h3>

          <div class="box-tools pull-right">
            
            </button>
          </div>
        </div>
        <div class="box-body" style="display: block;">
             <?php 
            $columns = [
                [
                'attribute' => 'name',
                'label'=>Yii::t('app', 'Товар'),
                'value'=>'name',
//                'contentOptions' => ['style' => 'vertical-align:middle;'],
                ],
                [
                'attribute' => 'quantity',
                'label'=>Yii::t('app', 'Кол-во'),
                'value'=>'quantity',
//                'contentOptions' => ['style' => 'vertical-align:middle;width:18%'],
                ],
                [
                'attribute' => 'sum_spent',
                'format'=>'raw',
                'label'=>Yii::t('app', 'Итого'),
                'value'=>function ($data) { return (float)$data['sum_spent']."<i class=\"fa fa-fw fa-rub\"></i>";},
//                'contentOptions' => ['style' => 'vertical-align:middle;font-weight:bold;width:25%'],
                ]
            ];
            ?>
             <?=GridView::widget([
            'dataProvider' => $topGoodsDP,
            'filterPosition' => false,
            'columns' => $columns,
            'tableOptions' => ['class' => 'table no-margin'],
            'options' => ['class' => 'table-responsive'],
            'bordered' => false,
            'striped' => false,
            'condensed' => false,
           'resizableColumns'=>false,
            'responsive' => false,
            'hover' => true,
            'summary' => false,
            ]);
            ?> 
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <div class="col-md-12">
        <!-- AREA CHART -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Статистика по подключенным ресторанам') ?></h3>
            </div>
            <div class="box-body" style="display: block;">
                <?=
                ChartJs::widget([
                    'type' => 'line',
                    'options' => [
                        'height' => 400,
                        'width' => 1200,
                    ],
                    'data' => [
                        'labels' => $clientsDayLabels,
                        'datasets' => [
                            [
                                'label' => Yii::t('app', 'Все рестораны'),
                                'backgroundColor' => "rgba(54,140,191,.2)",
                                'borderColor' => "rgba(54,140,191,.8)",
                                'pointBackgroundColor' => "rgba(54,140,191,1)",
                                'pointBorderColor' => "rgba(54,140,191,1)",
                                'pointHoverBackgroundColor' => "rgba(54,140,191,1)",
                                'pointHoverBorderColor' => "rgba(54,140,191,1)",
                                'data' => $clientsDayTurnover,
                                'spanGaps' => true,
                                'borderJoinStyle' => 'miter',
                                'fill' => false,
                            ],
                        ]
                    ],
                ])
                ?>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php Pjax::end() ?>
</section>