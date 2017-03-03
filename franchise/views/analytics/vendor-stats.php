<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\widgets\DatePicker;
use yii\widgets\Pjax;
use kartik\grid\GridView;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-signal"></i> Аналитика
        <small>Статистика поставщика <?= $vendor->name ?></small>
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
<div class="box box-info order-history">
    <!-- /.box-header -->
    <div class="box-body">
        <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$headerStats["ordersCount"];?></span>
                    <span class="info-box-text">Всего заказов</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$headerStats["goodsCount"];?></span>
                    <span class="info-box-text">Всего товаров</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$headerStats["clientsCount"]?></span>
                    <span class="info-box-text">Всего клиентов</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-number"><?=$headerStats["totalTurnover"];?> <i class="fa fa-fw fa-rub"></i></span>
                    <span class="info-box-text">Оборот</span>
                </div>
            </div>
        </div>
        </div>
        <div class="col-lg-5 col-md-6 col-sm-6"> 
                    <?= Html::label('Начальная дата / Конечная дата', null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <div class="form-group" style="width: 300px; height: 44px;">
            <?=
            DatePicker::widget([
                'name' => 'date',
                'name2' => 'date2',
                'value' => $dateFilterFrom,
                'value2' => $dateFilterTo,
                'options' => ['placeholder' => 'Начальная Дата', 'id' => 'dateFrom'],
                'options2' => ['placeholder' => 'Конечная дата', 'id' => 'dateTo'],
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
          <h3 class="box-title">Объем заказов</h3>

          <div class="box-tools pull-right">
            
            </button>
          </div>
        </div>
        <div class="box-body" style="display: block;">
          <div class="chart">
            <canvas id="areaChart" style="height: 282px; width: 574px;" height="282" width="574"></canvas>
          </div>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <div class="col-md-12">
      <!-- AREA CHART -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Объем по поставщикам</h3>

          <div class="box-tools pull-right">
            
            </button>
          </div>
        </div>
        <div class="box-body" style="display: block;">
          <div class="chart">
          <canvas id="pieChart" style="height: 282px; width: 574px;" height="282" width="574"></canvas>  
          </div>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <div class="col-md-12">
      <!-- AREA CHART -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Статистика по товарам</h3>

          <div class="box-tools pull-right">
            
            </button>
          </div>
        </div>
        <div class="box-body" style="display: block;">
          <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'product-analytic-list',]); ?>
             <?php 
            $columns = [
                [
                'attribute' => 'name',
                'label'=>'Товар',
                'value'=>'name',
//                'contentOptions' => ['style' => 'vertical-align:middle;'],
                ],
                [
                'attribute' => 'quantity',
                'label'=>'Кол-во',
                'value'=>'quantity',
//                'contentOptions' => ['style' => 'vertical-align:middle;width:18%'],
                ],
                [
                'attribute' => 'sum_spent',
                'format'=>'raw',
                'label'=>'Итого',
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
            <?php  Pjax::end(); ?>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
</div>
