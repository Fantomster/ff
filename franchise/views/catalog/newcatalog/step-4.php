<?php
use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Html;
use dosamigos\switchinput\SwitchBox;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;
use common\models\Users;
use kartik\checkbox\CheckboxX;
$this->title = Yii::t('app', 'franchise.views.catalog.newcatalog.set_cat', ['ru'=>'Назначить каталог']);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-list-alt"></i> <?= Yii::t('app', 'franchise.views.catalog.newcatalog.editing_cat_four', ['ru'=>'Редактирование каталога']) ?> <?='<strong>'.common\models\Catalog::get_value($cat_id)->name.'</strong>'?>
        <small></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'franchise.views.to_main', ['ru'=>'Главная']), 'url' => '/'],
        'links' => [
            [
            'label' => Yii::t('app', 'franchise.views.catalog.newcatalog.catalogs_four', ['ru'=>'Каталоги']),
            'url' => ['catalog/index', 'vendor_id'=>$vendor_id],
            ],
            Yii::t('app', 'franchise.views.catalog.newcatalog.step_four', ['ru'=>'Шаг 4. Редактирование каталога']),
        ],
    ])
    ?>
</section>
<section class="content">
<div class="box box-info">
    <!-- /.box-header -->
    <div class="box-body">
        <div class="panel-body">
            <ul class="nav fk-tab nav-tabs pull-left">
              <?='<li>'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.title_four', ['ru'=>'Название']),['catalog/step-1-update', 'vendor_id'=>$vendor_id,'id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.add_goods_seven', ['ru'=>'Добавить товары']),['catalog/step-2', 'vendor_id'=>$vendor_id,'id'=>$cat_id]).'</li>'?>
                <?='<li>'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.change_prices_five', ['ru'=>'Изменить цены']),['catalog/step-3-copy', 'vendor_id'=>$vendor_id,'id'=>$cat_id]).'</li>'?>
                <?='<li class="active">'.Html::a(' ' . Yii::t('app', 'franchise.views.catalog.newcatalog.settle_to_rest_five', ['ru'=>'Назначить ресторану']) . '  <i class="fa fa-fw fa-thumbs-o-up"></i>',['catalog/step-4', 'vendor_id'=>$vendor_id,'id'=>$cat_id]).'</li>'?>
            </ul>
            <ul class="fk-prev-next pull-right">
              <?='<li class="fk-prev">'.Html::a(Yii::t('app', 'franchise.views.catalog.newcatalog.back_three', ['ru'=>'Назад']),['catalog/step-3-copy', 'vendor_id'=>$vendor_id,'id'=>$cat_id]).'</li>'?>
              <?='<li class="fk-next">'.Html::a('<i class="fa fa-save"></i> ' . Yii::t('app', 'franchise.views.catalog.newcatalog.end', ['ru'=>'Завершить']) . ' ',['catalog/index', 'vendor_id'=>$vendor_id]).'</li>'?>
            </ul>
        </div>



        <?php
        $gridColumns = [
		[
		'label'=>Yii::t('app', 'franchise.views.catalog.newcatalog.rest', ['ru'=>'Ресторан']),
		'value'=>function ($data) {
                $organization_name=common\models\Organization::get_value($data->rest_org_id)->name;
                return $organization_name;
                }
		],
		[
		'label'=>Yii::t('app', 'franchise.views.catalog.newcatalog.current_catalog', ['ru'=>'Текущий каталог']),
                'format' => 'raw',
		'value'=>function ($data) {
            $catalog = common\models\Catalog::get_value($data->cat_id);
		    $catalog_name = !empty($catalog->name) ? $catalog->name : '';
		    return $catalog_name;
		}
		],
              [
            'attribute' => Yii::t('app', 'franchise.views.catalog.newcatalog.set', ['ru'=>'Назначить']),
            'format' => 'raw',
            'contentOptions' => ['style' => 'width:50px;'],
            'value' => function ($data) {
                $link = CheckboxX::widget([
                    'name'=>'setcatalog_'.$data->rest_org_id,
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'value'=>$data->cat_id ==Yii::$app->request->get('id') ? 1 : 0,
                    'autoLabel' => true,
                    'options'=>['id'=>'setcatalog_'.$data->id, 'data-id'=>$data->rest_org_id],
                    'pluginOptions'=>[
                        'threeState'=>false,
                        'theme' => 'krajee-flatblue',
                        'enclosedLabel' => true,
                        'size'=>'lg',
                        ]
                ]);
                return $link;
            },

        ],
        ];
        ?>
        <div class="panel-body">
            <div class="callout callout-fk-info">
                <h4><?= Yii::t('app', 'franchise.views.catalog.newcatalog.step_four_two', ['ru'=>'ШАГ 4']) ?></h4>

                <p><?= Yii::t('app', 'franchise.views.catalog.newcatalog.at_last', ['ru'=>'И наконец, укажите рестораны, которым будет доступен ваш каталог.']) ?></p>
            </div>
        <?php Pjax::begin(['id' => 'pjax-container']); ?>
        <?=GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterPosition' => false,
            'columns' => $gridColumns,
            'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
            'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
            'bordered' => false,
            'striped' => true,
            'summary' => false,
            'condensed' => false,
            'responsive' => false,
            'hover' => false, 
        ]);
        ?>
        <?php Pjax::end(); ?>
        </div>
    </div>
</div>
</section>
<?php
$step4Url = Url::to(['catalog/step-4', 'vendor_id'=>$vendor_id,'id'=>$cat_id]);

$this->registerJs('
/** 
 * Forward port jQuery.live()
 * Wrapper for newer jQuery.on()
 * Uses optimized selector context 
 * Only add if live() not already existing.
*/
if (typeof jQuery.fn.live == "undefined" || !(jQuery.isFunction(jQuery.fn.live))) {
  jQuery.fn.extend({
      live: function (event, callback) {
         if (this.selector) {
              jQuery(document).on(event, this.selector, callback);
          }
      }
  });
}

$(document).on("change", "input[type=checkbox]", function(e) {	
var id = $(this).attr("data-id");
var state = $(this).prop("checked");
    $.ajax({
    url: "'. $step4Url .'",
    type: "POST",
    dataType: "json",
    data: {"add-client":true,"rest_org_id":id,"state":state},
    cache: false,
    success: function(response) {
        console.log(response);
        $.pjax.reload({container: "#pjax-container"});
        },
        failure: function(errMsg) {
        console.log(errMsg);
        }
    });
})
');
?>
