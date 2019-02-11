<?php

use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use kartik\form\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use common\models\Currency;
use yii\helpers\Url;
use yii\helpers\Json;
use common\models\RelationSuppRestPotential;
use kartik\dropdown\DropdownX;

kartik\select2\Select2Asset::register($this);
\frontend\assets\HandsOnTableAsset::register($this);

$style = "
    .glyphicon-refresh-animate {
        -animation: spin .7s infinite linear;
        -ms-animation: spin .7s infinite linear;
        -webkit-animation: spinw .7s infinite linear;
        -moz-animation: spinm .7s infinite linear;
    }
    
    @keyframes spin {
        from { transform: scale(1) rotate(0deg);}
        to { transform: scale(1) rotate(360deg);}
    }
      
    @-webkit-keyframes spinw {
        from { -webkit-transform: rotate(0deg);}
        to { -webkit-transform: rotate(360deg);}
    }
    
    @-moz-keyframes spinm {
        from { -moz-transform: rotate(0deg);}
        to { -moz-transform: rotate(360deg);}
    }
";

/**
 * @var $this View
 */
$this->registerCss($style);
?>
<?=
Modal::widget([
    'id' => 'view-supplier',
    'size' => 'modal-md',
    'clientOptions' => false,
])
?>
<?=
Modal::widget([
    'id' => 'view-catalog',
    'size' => 'modal-lg',
    'clientOptions' => false,
])
?>
<?=
Modal::widget([
    'id' => 'edit-catalog',
    'size' => 'modal-big',
    'clientOptions' => false,
])
?>
<?php
$this->title = Yii::t('message', 'frontend.views.client.suppliers.vendors', ['ru' => 'Поставщики']);
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss('
.Handsontable_table{position: relative;width: 100%;height:400px;overflow: hidden;}
.hide{display:none}
.file-input{width: 400px; float: left;}

');

$currencySymbolListList = Currency::getSymbolList();
$firstCurrency = $currencySymbolListList[1];
$currencyList = Json::encode(Currency::getList());
$currencySymbolList = Json::encode($currencySymbolListList);
?>
<div id="modal_addProduct" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="text-center">
                    <h5 class="modal-title">
                        <b class="client-manager-name"></b><?= Yii::t('message', 'frontend.views.client.suppliers.set_goods', ['ru' => ', укажите товары, которые Вы покупаете у поставщика']) ?>
                        <b class="supplier-org-name"></b>
                    </h5>
                </div>
            </div>
            <div class="modal-body">
                <div class="handsontable" id="CreateCatalog"></div>
            </div>
            <div class="modal-footer">
                <?=
                Html::button('<span class="text-label">' . Yii::t('app', 'Изменить валюту:') . '  </span> <span class="currency-symbol">' . $firstCurrency . '</span>', [
                    'class' => 'btn btn-default pull-left',
                    'style' => ['margin' => '0 5px;'],
                    'id' => 'changeCurrency',
                ])
                ?>
                <button id="btnCancel" type="button" class="btn btn-gray"
                        data-dismiss="modal"><?= Yii::t('message', 'frontend.views.client.suppliers.cancel', ['ru' => 'Отмена']) ?></button>
                <button id="invite" type="button" class="btn btn-success"
                        data-loading-text="<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> <?= Yii::t('message', 'frontend.views.client.suppliers.sending', ['ru' => 'Отправляем...']) ?>">
                    <span><?= Yii::t('message', 'frontend.views.client.suppliers.send', ['ru' => 'Отправить']) ?></span>
                </button>
            </div>
        </div>
    </div>
</div>
<section class="content-header">
    <h1>
        <i class="fa fa-users"></i> <?= Yii::t('message', 'frontend.views.client.suppliers.vendors_two', ['ru' => 'Поставщики']) ?>
        <small><?= Yii::t('message', 'frontend.views.client.suppliers.vendors_search', ['ru' => 'Находите и добавляйте в Вашу систему новых поставщиков']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb'
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
        'links' => [
            Yii::t('message', 'frontend.views.client.suppliers.vendors_three', ['ru' => 'Поставщики'])
        ],
    ])
    ?>
</section>


<?php
$gridColumnsCatalog = [
    [
        'attribute' => 'vendor_name',
        'label' => Yii::t('message', 'frontend.views.client.suppliers.organization', ['ru' => 'Организация']),
        'format' => 'raw',
        'contentOptions' => ['class' => 'text-bold', 'style' => 'vertical-align:middle;width:45%;font-size:14px'],
        'value' => function ($data) {
            return Html::a(Html::encode($data->vendor->name), ['client/view-supplier', 'id' => $data->supp_org_id], [
                'data' => [
                    'target' => '#view-supplier',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                ],
            ]);
        }
    ],
    [
        'format' => 'raw',
        'contentOptions' => ['class' => 'text-bold', 'style' => 'vertical-align:middle;width:5%;font-size:14px'],
        'value' => function ($data) {
            if (isset($data->vendor->ediOrganization->gln_code) && $data->vendor->ediOrganization->gln_code > 0) {
                $text = Yii::t('app', 'frontend.views.client.suppliers.edi_alt_text', ['ru' => 'Поставщик работает через систему электронного документооборота']);
                return Html::img(Url::to('/img/edi-logo.png'), ['alt' => $text, 'title' => $text, 'width' => 40]);
            } else {
                return '';
            }
        }
    ],
    [
        'attribute' => 'status',
        'label' => Yii::t('message', 'frontend.views.client.suppliers.status', ['ru' => 'Статус сотрудничества']),
        'contentOptions' => ['style' => 'vertical-align:middle;min-width:180px;'],
        'format' => 'raw',
        'value' => function ($data) {
            if ($data->status == 3)
                return '<span class="text-yellow">' . Yii::t('message', 'frontend.views.client.suppliers.need_apply', ['ru' => 'Ожидает<br>вашего подтверждения']) . ' </span>';
            elseif ($data->invite == 0) {
                return '<span class="text-danger">' . Yii::t('message', 'frontend.views.client.suppliers.sent', ['ru' => 'Приглашение<br>отправлено']) . ' </span>';
            } elseif (isset($data->catalog) && $data->catalog->status == 1) {
                return '<span class="text-success">' . Yii::t('message', 'frontend.views.client.suppliers.partner', ['ru' => 'Партнер']) . ' </span>';
            } else {
                return '<span class="text-yellow">' . Yii::t('message', 'frontend.views.client.suppliers.not_set', ['ru' => 'Партнер<br> Каталог не назначен']) . ' </span>';
            }
        }
    ],
    [
        'label' => '',
        'contentOptions' => ['class' => 'text-right', 'style' => 'vertical-align:middle;min-width:174px'],
        'headerOptions' => ['style' => 'text-align:right'],
        'format' => 'raw',
        'value' => function ($data) {

            if ($data === null) {
                return "<div class='btn-group'></div>";
            }

            $result = "";

            /**
             * Кнопка ЗАКАЗ
             */
            //Поставщик прислал приглашение, отображаем кнопку "сотрудничать"
            if ($data->status == RelationSuppRestPotential::RELATION_STATUS_POTENTIAL) {
                $result .= Html::button(
                    '<i class="fa fa-shopping-cart m-r-xs"></i> ' .
                    Yii::t('message', 'frontend.views.client.suppliers.supplier_apply', ['ru' => 'Сотрудничать']),
                    [
                        'class' => 'btn btn-success btn-sm apply',
                        'data' => [
                            'id' => $data["supp_org_id"]
                        ]
                    ]);
            } else if ($data->cat_id != 0) {
                $result .= Html::a(
                    '<i class="fa fa-shopping-cart m-r-xs"></i> ' .
                    Yii::t('message', 'frontend.views.client.suppliers.order_two', ['ru' => 'Заказ']) . ' ',
                    [
                        'order/create',
                        'OrderCatalogSearch[searchString]' => "",
                        'OrderCatalogSearch[selectedCategory]' => "",
                        'OrderCatalogSearch[selectedVendor]' => $data["supp_org_id"],
                    ],
                    [
                        'class' => 'btn btn-success btn-sm',
                        'data-pjax' => 0,
                    ]
                );
            } else {
                $result .= Html::a(
                    '<i class="fa fa-shopping-cart m-r-xs"></i> ' .
                    Yii::t('message', 'frontend.views.client.suppliers.order_two', ['ru' => 'Заказ']) . ' ',
                    '#',
                    [
                        'class' => 'btn btn-success btn-sm',
                        'data-pjax' => 0,
                        'disabled' => 'disabled'
                    ]
                );
            }

            /**
             * Кнопка каталога
             */
            //Поставщик не работает в системе
            if ($data->vendor->is_work == 0) {
                if ($data->cat_id != 0) {
                    //Редактирование каталога
                    $result .= Html::a(
                        '<i class="fa fa-pencil"></i>',
                        [
                            'client/edit-catalog',
                            'id' => $data["cat_id"]
                        ],
                        [
                            'class' => 'btn btn-default btn-sm editCatalogButtons',
                            'style' => 'display:block;',
                            'disabled' => 'disabled',
                            'data-pjax' => 0,
                            'data' => [
                                'target' => '#edit-catalog',
                                'toggle' => 'modal',
                                'backdrop' => 'static'
                            ]
                        ]
                    );
                } else {
                    //Редактирование каталога запрещено
                    $result .= Html::a(
                        '<i class="fa fa-pencil"></i>', '#',
                        [
                            'class' => 'btn btn-default btn-sm',
                            'style' => 'text-center',
                            'disabled' => 'disabled'
                        ]
                    );
                }
            } else {
                if ($data->cat_id != 0) {
                    //Просмотр каталога
                    $result .= Html::a('<i class="fa fa-eye"></i>', ['client/view-catalog', 'id' => $data["cat_id"]], [
                        'class' => 'btn btn-default btn-sm',
                        'style' => 'text-center',
                        'data-pjax' => 0,
                        'data' => [
                            'target' => '#view-catalog',
                            'toggle' => 'modal',
                            'backdrop' => 'static',
                        ],
                    ]);
                } else {
                    $result .= Html::tag('span', '<i class="fa fa-eye m-r-xs"></i>',
                        [
                            'class' => 'btn btn-default btn-sm',
                            'disabled' => 'disabled'
                        ]
                    );
                }
            }

            /**
             *  Кнопка INVITE
             */
            if ($data->invite == 0) {
                $result .= Html::a(
                    '<i class="fa fa-envelope m-r-xs"></i>',
                    [
                        'client/re-send-email-invite',
                        'id' => $data["supp_org_id"]
                    ],
                    [
                        'class' => 'btn btn-default btn-sm resend-invite',
                        'data-pjax' => 0
                    ]
                );
            } else {
                $result .= Html::tag('span', '<i class="fa fa-envelope m-r-xs"></i>',
                    [
                        'class' => 'btn btn-default btn-sm',
                        'disabled' => 'disabled'
                    ]
                );
            }

            //Кнопка сопоставления номенклатуры
            /* $result .= Html::button('<i class="fa fa-paperclip m-r-xs"></i>', [
                 'class' => 'btn btn-default btn-sm',
                 'data' => ['id' => $data["supp_org_id"], 'type' => (($data->status == RelationSuppRestPotential::RELATION_STATUS_POTENTIAL) ? 1 : 0)]
             ]);
 */
            /* Временно отключена кнопка глобального сопоставления - до внедрения Rabbit и нового интерфейса сопоставления
            /*
             $result .= Html::beginTag('span', ['class'=>'text-right dropdown']);
             $result .= Html::button(' <i class="fa fa-paperclip m-r-xs"></i></button>',
                 ['type'=>'button', 'class'=>'btn btn-default btn-sm', 'data-toggle'=>'dropdown']);
             $result .= DropdownX::widget([
                 'options'=>['class'=>'pull-right'], // for a right aligned dropdown menu
                 'items' => [
                     // ['label' => 'R-keeper', 'url' => '/clientintegr/rkws/fullmap/index'],
                     '<li><a href="/clientintegr/rkws/fullmap/index" data-pjax=0>R-keeper</a> </li>'
                 ],
             ]);
             $result .= Html::endTag('span');

 */
            //Кнопка удаления
            $result .= Html::button('<i class="fa fa-trash m-r-xs"></i>', [
                'class' => 'btn btn-danger btn-sm del',
                'data' => ['id' => $data["supp_org_id"], 'type' => (($data->status == RelationSuppRestPotential::RELATION_STATUS_POTENTIAL) ? 1 : 0)]
            ]);

            return "<div class='btn-group'>" . $result . "</div>";
        }
    ]
];
?>
<section class="content">
    <div class="row">
        <div class="col-sm-12 col-md-8">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('message', 'frontend.views.client.suppliers.vendors_list', ['ru' => 'Список поставщиков']) ?></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $form = ActiveForm::begin([
                                'options' => [
                                    'id' => 'search-form',
                                    'role' => 'search',
                                ],
                            ]);
                            ?>
                            <?=
                            $form->field($searchModel, "search_string", [
                                'addon' => [
                                    'append' => [
                                        'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                        'options' => [
                                            'class' => 'append',
                                        ],
                                    ],
                                ],
                            ])
                                ->textInput(['prompt' => 'Поиск', 'class' => 'form-control', 'id' => 'searchString'])
                                ->label(false)
                            ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box-body table-responsive no-padding">
                                <?php Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'timeout' => 10000, 'id' => 'sp-list']) ?>
                                <?=
                                GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    'filterPosition' => false,
                                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                                    'columns' => $gridColumnsCatalog,
                                    'filterPosition' => false,
                                    'summary' => '',
                                    'options' => ['class' => 'table-responsive'],
                                    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
                                    'resizableColumns' => false,
                                ]);
                                ?>
                                <?php Pjax::end(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-4">
            <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'add-supplier-list']) ?>
            <?php $form = ActiveForm::begin(['id' => 'SuppliersFormSend', 'enableClientValidation' => true]); ?>
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('message', 'frontend.views.client.suppliers.vendor_add', ['ru' => 'Добавить поставщика']) ?></h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <?= $form->field($user, 'email') ?>
                    <?= $form->field($profile, 'full_name')->label(Yii::t('message', 'frontend.views.client.suppliers.fio', ['ru' => 'ФИО'])) ?>
                    <?=
                    $form->field($profile, 'phone')
                        ->widget(\common\widgets\phone\PhoneInput::className(), [
                            'jsOptions' => [
                                'preferredCountries' => ['ru'],
                                'nationalMode' => false,
                                'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                            ],
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ])
                        ->label(Yii::t('message', 'frontend.views.client.suppliers.phone', ['ru' => 'Телефон']))
                        ->textInput()
                    ?>
                    <?= $form->field($organization, 'name')->label(Yii::t('message', 'frontend.views.client.suppliers.org', ['ru' => 'Организация'])) ?>
                    <?= $form->field($organization, 'inn')->textInput(['id' => 'organization-view-supplirs-inn']); ?>
                    <?= $form->field($organization, 'kpp')->textInput(['id' => 'organization-view-supplirs-kpp']); ?>
                    <?= $form->field($organization, 'action')->hiddenInput(['value' => 'new'])->label(false); ?>
                </div>
                <div class="box-footer">
                    <div class="form-group">
                        <?= $currentOrganization->isEmpty() ?
                            Html::a(Yii::t('message', 'frontend.views.client.suppliers.add_goods', ['ru' => 'Добавить товары']), ['#'], [
                                'class' => 'btn btn-success btn-sm setInfo',
                                'disabled' => 'disabled',
                                'id' => 'addProduct',
                            ])
                            :
                            Html::a(Yii::t('message', 'frontend.views.client.suppliers.add_goods', ['ru' => 'Добавить товары']), ['#'], [
                                'class' => 'btn btn-success btn-sm',
                                'disabled' => 'disabled',
                                'name' => 'addProduct',
                                'id' => 'addProduct',
                                'data' => [
                                    'target' => '#modal_addProduct',
                                    'toggle' => 'modal',
                                    'backdrop' => 'static',
                                ],
                            ]);
                        ?>
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('message', 'frontend.views.client.suppliers.invite', ['ru' => 'Пригласить']), ['class' => 'btn btn-success hide', 'readonly' => 'readonly', 'name' => 'inviteSupplier', 'id' => 'inviteSupplier']) ?>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</section>

<?php
$chkmailUrl = Url::to(['client/chkmail']);
$inviteUrl = Url::to(['client/invite']);
$createUrl = Url::to(['client/create']);
$suppliersUrl = Url::to(['client/suppliers']);
$removeSupplierUrl = Url::to(['client/remove-supplier']);
$applySupplierUrl = Url::to(['client/apply-supplier']);

$arr = [
    Yii::t('message', 'frontend.views.client.suppliers.var', ['ru' => 'Уведомление']),
    Yii::t('message', 'frontend.views.client.suppliers.var1', ['ru' => 'Окей!']),
    Yii::t('message', 'frontend.views.client.suppliers.var2', ['ru' => 'Наименование товара']),
    Yii::t('message', 'frontend.views.client.suppliers.var3', ['ru' => 'Ед. измерения']),
    Yii::t('message', 'frontend.views.client.suppliers.var4', ['ru' => 'Цена (руб)']),
    Yii::t('message', 'frontend.views.client.suppliers.var5', ['ru' => 'Уведомление']),
    Yii::t('message', 'frontend.views.client.suppliers.var6', ['ru' => 'Завершить']),
    Yii::t('message', 'frontend.views.client.suppliers.var7', ['ru' => 'Уведомление']),
    Yii::t('message', 'frontend.views.client.suppliers.var8', ['ru' => 'Завершить']),
    Yii::t('message', 'frontend.views.client.suppliers.var9', ['ru' => 'Приглашение на MixCart']),
    Yii::t('message', 'frontend.views.client.suppliers.var10', ['ru' => 'Отправить приглашение повторно?']),
    Yii::t('message', 'frontend.views.client.suppliers.var11', ['ru' => 'Закрыть']),
    Yii::t('message', 'frontend.views.client.suppliers.var12', ['ru' => 'Отправить']),
    Yii::t('message', 'frontend.views.client.suppliers.var13', ['ru' => 'Удалить поставщика?']),
    Yii::t('message', 'frontend.views.client.suppliers.var14', ['ru' => 'Поставщик будет удален из Вашего списка поставщиков']),
    Yii::t('message', 'frontend.views.client.suppliers.var15', ['ru' => 'Удалить']),
    Yii::t('message', 'frontend.views.client.suppliers.var16', ['ru' => 'Отмена']),
    Yii::t('app', 'frontend.views.client.suppliers.var17', ['ru' => 'Изменение валюты каталога']),
    Yii::t('app', 'frontend.views.client.suppliers.var18', ['ru' => 'Отмена']),
    Yii::t('app', 'frontend.views.client.suppliers.var19', ['ru' => 'Отмена']),
    Yii::t('app', 'frontend.views.client.suppliers.var20', ['ru' => 'Отмена']),
    Yii::t('app', 'frontend.views.client.suppliers.var21', ['ru' => 'Отмена']),
];
$language = Yii::$app->sourceLanguage;
$customJs = <<< JS
        
var currencies = $currencySymbolList;
var currentCurrency = 1;
   
$(".content").on("change keyup paste cut", "#searchString", function() {
    if (timer) {
        clearTimeout(timer);
    }
    timer = setTimeout(function() {
        $("#search-form").submit();
    }, 700);
    setTimeout(function() {
        $('.editCatalogButtons').removeAttr('disabled');
    }, 2000);
});        
$(".modal").removeAttr("tabindex");
function bootboxDialogShow(msg){
bootbox.dialog({
    message: msg,
    title: '$arr[0]',
    buttons: {
        success: {
          label: "$arr[1]",
          className: "btn-success btn-md",
          callback: function() {
            //location.reload();    
          }
        },
    },
});
}

$('#modal_addProduct').on('shown.bs.modal', function() {
var data = [];
for ( var i = 0; i < 60; i++ ) {
    data.push({article: '', product: '', units: '', price: '',  ed: '', notes: '',});
}
  var container = document.getElementById('CreateCatalog');
  var hot = new Handsontable(container, {
  data: data,
  colHeaders : ['$arr[2]', '$arr[3]', '$arr[4]'],
  columns: [
        {data: 'product', wordWrap:true},
        {data: 'ed', allowEmpty: false},
	{
            data: 'price', 
            type: 'numeric',
            format: '0.00',
            language: '$language'
        }
    ],
  className : 'Handsontable_table',
  rowHeaders : true,
  renderAllRows: true,
  stretchH : 'all',
  autoRowSize: true,
  manualColumnResize: true,
  autoWrapRow: true,
  minSpareRows: 1,
  Controller: true,
  tableClassName: ['table-hover']
  })   
});
$('#addProduct').click(function (e){
  e.preventDefault();
  if ($(this).attr('disabled') == 'disabled') {
  e.stopPropagation();
  }
});
$('#modal_addProduct').on('hidden.bs.modal', function (e) {
  $('#CreateCatalog *').remove();
});
/*
* 1 Поставщик уже есть в списке контактов (лочим все кнопки)
* 2 Вы уже отправили приглашение этому поставщику, ожидается отклик поставщика (лочим кнопки)
* 3 Поставщик еще не авторизован / добавляем каталог
* 4 Данный email не может быть использован (лочим все кнопки)
* 5 Нет совпадений по Email (Новый поставщик и новый каталог)(#addSupplier)
* 6 поставщик авторизован, связи не найдено, invite	
*/
$('#profile-full_name').attr('readonly','readonly');
$('#organization-name').attr('readonly','readonly');
$('#relationcategory-category_id').attr('disabled','disabled');
$('.select2-search__field').css('width','100%');
$('#addProduct').attr('disabled','disabled');
$('#SuppliersFormSend').on('afterValidateAttribute', function (event, attribute, messages) {	
	var hasError = messages.length !==0;
    var field = $(attribute.container);
    var input = field.find(attribute.input);
	input.attr("aria-invalid", hasError ? "true" : "false");
    if (attribute.name === 'email' && !hasError)
        {
            $.ajax({
            url: "$chkmailUrl",
            type: "POST",
            dataType: "json",
            data: {'email' : input.val()},
            success: function(response) {
            console.log(response)
                if(response.success){
                        // Поставщик уже есть в списке контактов (лочим все кнопки)
	                if(response.eventType==1) { 
                        var fio = response.fio;
                        var phone = response.phone;
                        var organization = response.organization;
                        $('#profile-full_name').val(fio);
                        $('#profile-phone').val(phone);
                        $('#organization-name').val(organization); 
                        $('#addProduct').removeClass('hide');
                        $('#inviteSupplier').addClass('hide');
                        $('#addProduct').attr('disabled','disabled');
                        $('#profile-full_name,#profile-phone,#organization-name').attr('readonly','readonly');
                        $('#relationcategory-category_id').attr('disabled','disabled');
                        bootboxDialogShow(response.message);
                        console.log(organization);    
	                }
	                // Вы уже отправили приглашение этому поставщику, ожидается отклик поставщика (лочим кнопки)
	                if(response.eventType==2) {
                        var fio = response.fio;
                        var phone = response.phone;
                        var organization = response.organization;
                        $('#profile-full_name').val(fio);
                        $('#profile-phone').val(phone);
                        $('#organization-name').val(organization); 
                        $('#addProduct').removeClass('hide');
                        $('#inviteSupplier').addClass('hide');
                        $('#addProduct').attr('disabled','disabled');
                        $('#profile-full_name,#profile-phone,#organization-name').attr('readonly','readonly');
                        $('#relationcategory-category_id').attr('disabled','disabled');
                        bootboxDialogShow(response.message);
                        console.log('type = 2');    
	                }
	                // Связи не найдено - просто invite (#inviteSupplier)
	                if(response.eventType==3) {
		                var fio = response.fio;
                        var phone = response.phone; 
                        var organization = response.organization;
                        $('#profile-full_name').val(fio);
                        $('#profile-phone').val(phone);
                        $('#organization-name').val(organization);  
                        $('#addProduct').removeClass('hide');
                        $('#inviteSupplier').addClass('hide');
                        $('#profile-full_name,#profile-phone,#organization-name').attr('readonly','readonly');
                        $('#relationcategory-category_id,#addProduct').removeAttr('disabled');
                        console.log('type = 3');     
	                }
	                // Данный email не может быть использован (лочим все кнопки)
	                if(response.eventType==4) {
		                $('#addProduct').removeClass('hide');
                        $('#inviteSupplier').addClass('hide');
                        $('#addProduct').attr('disabled','disabled'); 
		                $('#profile-full_name,#profile-phone,#organization-name').attr('readonly','readonly');
		                $('#relationcategory-category_id').attr('disabled','disabled');
		                bootboxDialogShow(response.message);
		                console.log('type = 4');  
	                }
                    // Нет совпадений по Email (Новый поставщик и новый каталог)(#addSupplier)
	                if(response.eventType==5){
                        $('#relationcategory-category_id,#addProduct').removeAttr('disabled');
                        $('#addProduct').removeClass('hide');
                        $('#inviteSupplier').addClass('hide').attr('disabled','disabled');
                        $('#profile-full_name,#organization-name,#profile-phone').removeAttr('readonly');
                        console.log('type = 5'); 
                        if($('.has-error').length >= 1 || !$('#profile-phone').intlTelInput("isValidNumber")) {
                             $('#addProduct').attr('disabled','disabled');                 
                        } else {
                            $('#addProduct').removeAttr('disabled');                      
                        }
                    }
                    // 
	                if(response.eventType==6){
		                var fio = response.fio;
                        var phone = response.phone; 
                        var organization = response.organization;
	                    $('#profile-full_name').val(fio);
                        $('#profile-phone').val(phone);
                        $('#organization-name').val(organization); 
                        $('#addProduct').addClass('hide');
                        $('#inviteSupplier').removeClass('hide');
                        $('#profile-full_name,#profile-phone,#organization-name').attr('readonly','readonly');
                        $('#relationcategory-category_id,#inviteSupplier').removeAttr('disabled');
		                console.log('type = 6');    
	                }               
                } else {
		            console.log(response.message); 
                }
            },
            error: function(response) {
		        console.log(response.message); 
            }
        }); 
	}	 
	
	if($('.has-error').length >= 1 || !$('#profile-phone').intlTelInput("isValidNumber")) {
         $('#addProduct').attr('disabled','disabled');                 
    } else {
        $('#addProduct').removeAttr('disabled');                      
    }
});
$('#profile-full_name,#organization-name').on('keyup paste put', function(e){
        console.log('ok');
    if($('#profile-full_name').val().length<2 || $('#organization-name').val().length<2){
            $('#inviteSupplier').attr('disabled','disabled');
            $('#addProduct').attr('disabled','disabled'); 
    }else{
        $('#inviteSupplier').removeAttr('disabled');
        $('#addProduct').removeAttr('disabled');   
    }
    
    if(!$('#profile-phone').intlTelInput("isValidNumber")) {
         $('#addProduct').attr('disabled','disabled');                 
    } else {
        $('#addProduct').removeAttr('disabled');                      
    }
});   
$('#inviteSupplier').click(function(e){
e.preventDefault();	
    $.ajax({
      url: '$inviteUrl',
      type: 'POST',
      dataType: "json",
      data: $("#SuppliersFormSend" ).serialize(),
      cache: false,
      success: function (response) {
            if(response.success){
            bootbox.dialog({
                      message: response.message,
                      title: "$arr[5]",
                      buttons: {
                        success: {
                          label: "$arr[6]",
                          className: "btn-success",
                          callback: function() {
                                $("#invite").button("reset");
                                $("#btnCancel").prop( "disabled", false );
                              location.reload();   
                          }
                        },
                      }
                    });	
            }
            console.log(response);  
        }
    });
});
$('#invite').click(function(e){	
    $("#invite").button("loading");
    $("#btnCancel").prop( "disabled", true );
    e.preventDefault();
	var i, items, item, dataItem, data = [];
	var cols = ['product', 'ed', 'price'];
	$('#CreateCatalog tr').each(function() {
	  items = $(this).children('td');
	  if(items.length === 0) {
	    return;
	  }
	  dataItem = {};
	  for(i = 0; i < cols.length; i+=1) {
	    item = items.eq(i);
	    if(item) {
	      dataItem[cols[i]] = item.html();
	    }
	  }
	  if(dataItem[cols[0]] || dataItem[cols[1]] || dataItem[cols[2]]){
	    data.push({dataItem});    
	  }	    
	});
	var catalog = data;
	catalog = JSON.stringify(catalog);
        var form = $("#SuppliersFormSend")[0];
        var formData = new FormData(form);
        formData.append('catalog', catalog);
        formData.append('currency',currentCurrency);
	$.ajax({
                    processData: false,
                    contentType: false,
		  url: '$createUrl',
		  type: 'POST',
		  dataType: "json",
		  data: formData, //$("#SuppliersFormSend" ).serialize() + '&' + $.param({'catalog':catalog}),
		  cache: false,
		  success: function (response) {
                        if(response.success){
                            $("#invite").button("reset");
                            $("#btnCancel").prop( "disabled", false );
                          $.pjax.reload({container: "#add-supplier-list",timeout:30000});
                          $.pjax.reload({container: "#sp-list",timeout:30000});
			  $('#modal_addProduct').modal('hide'); 
                          bootbox.dialog({
			  message: response.message,
			  title: "$arr[7]",
			  buttons: {
			    success: {
			      label: "$arr[8]",
			      className: "btn-success",
			      callback: function() {
                               location.reload();     
			      }
			    },
			  }
			});
		  }else{
                    $("#invite").button("reset");
                    $("#btnCancel").prop( "disabled", false );
		  bootboxDialogShow(response.message);
		  console.log(response.message); 	  
		  }
	  },
        error: function(response) {
                    $("#invite").button("reset");
                    $("#btnCancel").prop( "disabled", false );
            }
        });
});       
$("#view-supplier").on("click", ".save-form", function() {             
    var form = $("#supplier-form");
    $.ajax({
    url: form.attr("action"),
    type: "POST",
    data: form.serialize(),
    cache: false,
    success: function(response) {
        $.pjax.reload({container: "#sp-list",timeout:30000});
            form.replaceWith(response);
                  
        },
        failure: function(errMsg) {
        console.log(errMsg);
    }
    });
}); 
$(document).on("click", ".resend-invite", function(e) {     
    e.preventDefault();
    var url = $(this).attr('href');
    console.log(url);
    bootbox.confirm({
        title:"$arr[9]",
        message: "$arr[10]",
        buttons: {
            cancel: {
                label: '$arr[11]',
                className: 'btn-gray'
            },
            confirm: {
                label: '$arr[12]',
                className: 'btn-success'
            }
            
        },
        callback: function (result) {
         if(result)$.post(url, {} );
        }
    });
});

$(document).on("click",".apply", function(e){
    var id = $(this).attr('data-id');
		$.ajax({
	        url: "$applySupplierUrl",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id},
	        cache: false,
	        success: function(response) {
		        }	
		    });
		$.pjax.reload({container: "#sp-list",timeout:30000});
})
$(document).on("click",".del", function(e){
    var id = $(this).attr('data-id');
    var type = $(this).attr('data-type');
        bootbox.confirm({
            title: "$arr[13]",
            message: "$arr[14]", 
            buttons: {
                confirm: {
                    label: '$arr[15]',
                    className: 'btn-success'
                },
                cancel: {
                    label: '$arr[16]',
                    className: 'btn-default'
                }
            },
            className: "danger-fk",
            callback: function(result) {
		if(result){
		$.ajax({
	        url: "$removeSupplierUrl",
	        type: "POST",
	        dataType: "json",
	        data: {'id' : id, 'type': type},
	        cache: false,
	        success: function(response) { 
			         
		        }	
		    });
        $.pjax.reload({container: "#sp-list",timeout:30000});
		}else{
		console.log('cancel');	
		}
	}});      
})
$("body").on("hidden.bs.modal", "#view-supplier", function() {
    $(this).data("bs.modal", null);
})
$("body").on("hidden.bs.modal", "#view-catalog", function() {
    $(this).data("bs.modal", null);
})
$("body").on("hidden.bs.modal", "#resend", function() {
    $(this).data("bs.modal", null);
})
$("body").on("hidden.bs.modal", "#edit-catalog", function() {
    $(this).data("bs.modal", null);
})
$("#organization-name").keyup(function() {
    $(".client-manager-name").html("$clientName");
    $(".supplier-org-name").html($("#organization-name").val());
});
        
    $(document).on("click", "#changeCurrency", function() {
        swal({
            title: '$arr[17]',
            input: 'select',
            inputOptions: $currencyList,
            inputPlaceholder: '$arr[18]',
            showCancelButton: true,
            allowOutsideClick: false,
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                    if (!value) {
                        reject("$arr[19]")
                    }
                    if (value != currentCurrency) {
                        currentCurrency = value;
                        $(".currency-symbol").html(currencies[currentCurrency]);
                        resolve();
                    } else {
                        reject("$arr[20]")
                    }
                })
            },
        }).then(function (result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else {
                swal({
                    title: '$arr[21]',
                    type: 'success',
                    showCancelButton: false,
                })
            }
        })        
    });
    $(document).on('click', '.pagination a', function() {
      setTimeout(function() {
            $('.editCatalogButtons').removeAttr('disabled').html('<i class="fa fa-pencil"></i>');
      }, 2000)
    })
JS;
$this->registerJs($customJs, View::POS_READY);
if ($currentOrganization->isEmpty()) {
    $infoUrl = Url::to(['/site/ajax-set-info']);
    $customJs = <<< JS2
            $("#SuppliersFormSend").on("click", "input, .setInfo", function(e) {
                $('#data-modal-wizard').trigger("invoke");
            })
JS2;
    $user = Yii::$app->user->identity;
    $organization = $user->organization;
    $profile = $user->profile;
    $this->registerJs($customJs, View::POS_READY);
    echo common\widgets\setinfo\SetInfoWidget::widget([
        'action' => '/site/ajax-complete-registration',
        'organization' => $organization,
        'profile' => $profile,
        'events' => 'invoke',
        'selector' => '#data-modal-wizard',
    ]);
}

$customJs = <<< JS3
    $('.editCatalogButtons').removeAttr('disabled').html('<i class="fa fa-pencil"></i>');
JS3;
$this->registerJs($customJs, View::POS_LOAD);
?>
