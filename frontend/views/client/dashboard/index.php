<?php
use kartik\date\DatePicker;
use kartik\grid\GridView;
use common\models\Order;
use common\models\Organization;
use common\models\Profile;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
frontend\assets\AdminltePluginsAsset::register($this);
$this->registerCss('
.box-analytics {border:1px solid #eee}.input-group.input-daterange .input-group-addon {
    border-left: 0px;
}
tfoot tr{border-top:2px solid #ccc}
.info-box-content:hover{color:#378a5f;}
.info-box-content{color:#84bf76;-webkit-box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.41);
-moz-box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.41);
box-shadow: 0px 0px 34px -11px rgba(0,0,0,0.41);}
.order-history .info-box {
     box-shadow: none; 
}
.info-box {
     box-shadow: none;
     border:1px solid #eee;
}
');
?>
<div class="box box-info">   
    <div class="box-header with-border">
      <div class="col-md-12">
        <h3 class="box-title">Рабочий стол</h3>
      </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body order-history">
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <a href="index.php?r=order/create">
                    <div class="info-box-content">
                        <i class="fa fa-truck" style="font-size: 28px;"></i>
                        <p class="info-box-text">Разместить заказ</p>
                    </div>                    
                </a>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <a href="index.php?r=client/suppliers">
                    <div class="info-box-content">
                        <i class="fa fa-users" style="font-size: 28px;"></i>
                        <p class="info-box-text">Управление вашими поставщиками</p>
                    </div>                    
                </a>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Мои поставщики</h3>

          <div class="box-tools pull-right">
            <?= Html::a('Мои поставщики', ['client/suppliers'],['class'=>'btn btn-success btn-sm']) ?>
          </div>
        </div>
          <div class="box-header with-border">
            <?=Html::input('text', 'search', null, ['class' => 'form-control','placeholder'=>'Поиск','id'=>'search']) ?>
          </div>
        <div class="box-body" style="display: block;">
        <?php
        $columns1 = [
        ['attribute' => 'supp_org_id','label'=>'Поставщик','value'=>function($data) {
            return Organization::find()->where(['id'=>$data['supp_org_id']])->one()->name;            
        }],
        ['attribute' => 'client_id','label'=>'','value'=>function($data) {
            return $data['supp_org_id'];           
        }]
        ];
        ?>
        <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'suppliers-list',]); ?>
            <?=GridView::widget([
           'dataProvider' => $suppliers_dataProvider,
           'filterPosition' => false,
           'columns' => $columns1,
           'tableOptions' => ['class' => 'table no-margin'],
           'options' => ['class' => 'table-responsive'],
           'bordered' => false,
           'striped' => false,
           'condensed' => false,
           'responsive' => false,
           'hover' => true,
           'rowOptions' => function ($model, $key, $index, $grid) {
                return ['id' => $model['supp_org_id'],'style'=>'cursor:pointer', 'onclick' => 'window.location.replace("index.php?r=order/view&id="+this.id);'];
            },
           ]);
           ?> 
        <?php  Pjax::end(); ?>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <div class="col-md-8">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Аналитика за неделю</h3>

          <div class="box-tools pull-right">
            <?= Html::a('Аналитика', ['client/analytics'],['class'=>'btn btn-outline-success btn-sm']) ?>
          </div>
        </div>
        <div class="box-body" style="display: block;">
        
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
</div>
<div class="row">
    <div class="col-md-12">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">История заказов</h3>

          <div class="box-tools pull-right">
            <?= Html::a('История заказов', ['order/index'],['class'=>'btn btn-outline-success btn-sm']) ?>
          </div>
        </div>
        <div class="box-body" style="display: block;">
          <?php 
        $columns = [
    ['attribute' => 'id','label'=>'№','value'=>'id'],
    ['attribute' => 'client_id','label'=>'Ресторан','value'=>function($data) {
        return Organization::find()->where(['id'=>$data['client_id']])->one()->name;           
    }],
    ['attribute' => 'created_by_id','label'=>'Заказ создал','value'=>function($data) {
        return $data['created_by_id']?
             Profile::find()->where(['id'=>$data['created_by_id']])->one()->full_name :
             "";
    }],
    ['attribute' => 'accepted_by_id','label'=>'Заказ принял','value'=>function($data) {
        return $data['accepted_by_id']?
             Profile::find()->where(['id'=>$data['accepted_by_id']])->one()->full_name :
             "";
    }],
    [
        'format' => 'raw',
        'attribute' => 'total_price',
        'value' => function($data) {
            return $data['total_price'] . '<i class="fa fa-fw fa-rub"></i>';
        },
        'label' => 'Сумма',
    ],
    [
        'format' => 'raw',
        'attribute' => 'created_at',
        'value' => function($data) {
            $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
            return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
        },
        'label' => 'Дата создания',
    ],
    ['attribute' => 'status','label'=>'Статус','format' => 'raw','value' => function($data) {
                        switch ($data['status']) {
                            case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                            case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                $statusClass = 'new';
                                break;
                            case Order::STATUS_PROCESSING:
                                $statusClass = 'processing';
                                break;
                            case Order::STATUS_DONE:
                                $statusClass = 'done';
                                break;
                            case Order::STATUS_REJECTED:
                            case Order::STATUS_CANCELLED:
                                $statusClass = 'cancelled';
                                break;
                        }
                        return '<span class="status ' . $statusClass . '"><i class="fa fa-circle-thin"></i> ' . Order::statusText($data['status']) . '</span>';//fa fa-circle-thin
                    },]
	];
        ?>
        <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'order-analytic-list',]); ?>
            <?=GridView::widget([
           'dataProvider' => $dataProvider,
           'filterPosition' => false,
           'columns' => $columns,
           'tableOptions' => ['class' => 'table no-margin'],
           'options' => ['class' => 'table-responsive'],
           'bordered' => false,
           'striped' => false,
           'condensed' => false,
           'responsive' => false,
           'hover' => true,
           'rowOptions' => function ($model, $key, $index, $grid) {
                return ['id' => $model['id'],'style'=>'cursor:pointer', 'onclick' => 'window.location.replace("index.php?r=order/view&id="+this.id);'];
            },
           ]);
           ?> 
        <?php  Pjax::end(); ?>   
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>    
</div>