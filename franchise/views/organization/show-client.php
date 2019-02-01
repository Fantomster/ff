<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\export\ExportMenu;

$this->title = implode(" - ", [
    Yii::t('app', 'franchise.views.organization.rest_four', ['ru' => 'Ресторан']),
    $organization->name
]);

$this->registerJs('
    $("document").ready(function(){
        $("#vendorInfo").data("bs.modal", null);
        var justSubmitted = false;
        var timer;
        $("body").on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#searchForm").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
        $("body").on("change keyup paste cut", "#searchString", function() {
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#searchForm").submit();
                }, 700);
            });
        $("body").on("hidden.bs.modal", "#vendorInfo", function() {
                $(this).data("bs.modal", null);
            });
        $("body").on("click", "td", function (e) {
            if ($(this).find("a").hasClass("stats")) {
                document.location = $(this).find("a").attr("href");
                return false;
            }
            
            var url = $(this).parent("tr").data("url");
            if (url !== undefined && !$(this).find("a").hasClass("f-delete")) {
                $("#vendorInfo").modal({backdrop:"static",toggle:"modal"}).load(url);
            }
        });
        $("body").on("click", ".f-delete", function(e){
            e.preventDefault();
            var url = $(this).attr("url");
            var obj = $(this);
            $.ajax({
              url: url,
            }).done(function() {
              obj.parent("td").parent("tr").fadeOut("fast", function() {});
            });
        });
    });
        ');
$this->registerCss("
    tr:hover{cursor: pointer;}
    .f-header{background-color: #fff; border-bottom: 1px solid #e5e5e5; color: #33363b; margin-bottom: 15px;}
    .f-title{text-align: left;}
        ");
?>

    <section class="content-header">
        <h1>
            <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.organization.rest_five', ['ru' => 'Ресторан']) ?> <?= $organization->name ?>
        </h1>
    </section>
    <section class="content">

        <div class="box box-info order-history">
            <div class="box-body">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab"
                                          aria-expanded="true"><?= Yii::t('app', 'franchise.views.organization.rest_six', ['ru' => 'Ресторан']) ?></a>
                    </li>
                    <?php if ($showButton): ?>
                        <li class=""><a href="#tab_2" data-toggle="tab"
                                        aria-expanded="false"><?= Yii::t('app', 'franchise.views.organization.requisits_two', ['ru' => 'Реквизиты']) ?></a>
                        </li>
                        <li class=""><a href="#tab_3" data-toggle="tab"
                                        aria-expanded="false"><?= Yii::t('app', 'franchise.views.organization.vendors_list', ['ru' => 'Список поставщиков ресторана']) ?></a>
                        </li>
                    <?php endif; ?>
                    <li class=""><a href="#tab_4" data-toggle="tab"
                                    aria-expanded="false"><?= Yii::t('app', 'franchise.views.organization.employees_list', ['ru' => 'Список сотрудников ресторана']) ?></a>
                    </li>
                </ul>
                <div class="modal-content tab-content" style="box-shadow: 0 2px 3px rgba(0,0,0,0.125);">
                    <div class="modal-body tab-pane active" id="tab_1">
                        <div class="modal-header f-header">
                            <h4 class="modal-title f-title"><?= $organization->name ?></h4>
                        </div>
                        <div class="row">
                            <div class="col-md-4" style="text-align: center;">
                                <img style="max-width: 100%;" src="<?= $organization->pictureUrl ?>">
                                <?php if ($showButton): ?>
                                    <div class="btn-edite">
                                        <?= Html::a(Yii::t('app', 'franchise.views.organization.anal_two', ['ru' => 'Аналитика']), ['analytics/client-stats', 'id' => $organization->id], ['class' => "btn btn-strip-green btn-block"]) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <div class="edite-place">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.rest_name_two', ['ru' => 'Название ресторана:']) ?></label>
                                        <p><?= $organization->name ?></p>
                                    </div>

                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.jur_name_three', ['ru' => 'Название юр. лица:']) ?></label>
                                        <p><?= $organization->legal_entity ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.city_three', ['ru' => 'Город:']) ?></label>
                                        <p><?= $organization->city ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.address_three', ['ru' => 'Адрес:']) ?></label>
                                        <p><?= $organization->address ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.short_info_three', ['ru' => 'Краткая информация:']) ?></label>
                                        <p><?= $organization->about ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.contact_fio', ['ru' => 'ФИО контактного лица:']) ?></label>
                                        <p><?= $organization->contact_name ?></p>
                                    </div>

                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.contact_email_two', ['ru' => 'E-mail контактного лица:']) ?></label>
                                        <p><?= $organization->email ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.contact_phone_two', ['ru' => 'Телефон контактного лица:']) ?></label>
                                        <p><?= $organization->phone ?></p>
                                    </div>
                                    <?php if (isset($organization->profile->full_name)): ?>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?= Yii::t('app', 'franchise.views.organization.manager_two', ['ru' => 'Управляющий менеджер:']) ?></label>
                                            <p><?= $organization->profile->full_name ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <?= Html::a(Yii::t('app', 'franchise.views.organization.go_to', ['ru' => 'Перейти в ЛК организации под своей учеткой']), ['organization/update-users-organization', 'organization_id' => $organization->id], ['class' => 'btn btn-default', 'target' => '_blank']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body tab-pane" id="tab_2">
                        <div class="modal-header f-header">
                            <h4 class="modal-title f-title"><?= Yii::t('app', 'franchise.views.organization.requisits_three', ['ru' => 'Реквизиты ресторана']) ?> <?= $organization->name ?></h4>
                        </div>
                        <h3><?= Yii::t('app', 'franchise.views.organization.req', ['ru' => 'Реквизиты']) ?></h3>
                        <label><?= Yii::t('app', 'franchise.views.organization.subscriber', ['ru' => 'Подписант:']) ?> </label>
                        <p><?= $organization->buisinessInfo->signed ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.jur_name_four', ['ru' => 'Юридическое название:']) ?> </label>
                        <p><?= $organization->buisinessInfo->legal_entity ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.jur_address', ['ru' => 'Юридический адрес:']) ?> </label>
                        <p><?= $organization->buisinessInfo->legal_address ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.official_email', ['ru' => 'Официальный email:']) ?> </label>
                        <p><?= $organization->buisinessInfo->legal_email ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.inn', ['ru' => 'ИНН:']) ?> </label>
                        <p><?= $organization->buisinessInfo->inn ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.kpp', ['ru' => 'КПП:']) ?> </label>
                        <p><?= $organization->buisinessInfo->kpp ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.ogrn', ['ru' => 'ОГРН:']) ?> </label>
                        <p><?= $organization->buisinessInfo->ogrn ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.bank', ['ru' => 'Банк:']) ?> </label>
                        <p><?= $organization->buisinessInfo->bank_name ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.bik', ['ru' => 'БИК:']) ?> </label>
                        <p><?= $organization->buisinessInfo->bik ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.phone_four', ['ru' => 'Телефон:']) ?> </label>
                        <p><?= $organization->buisinessInfo->phone ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.rs', ['ru' => 'р/с:']) ?> </label>
                        <p><?= $organization->buisinessInfo->correspondent_account ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.ks', ['ru' => 'к/с:']) ?> </label>
                        <p><?= $organization->buisinessInfo->checking_account ?></p>
                        <label><?= Yii::t('app', 'franchise.views.organization.field', ['ru' => 'Поле для заметок:']) ?> </label>
                        <p><?= $organization->buisinessInfo->info ?></p>
                    </div>
                    <div class="modal-body tab-pane" id="tab_3">
                        <div class="modal-header f-header">
                            <h4 class="modal-title f-title"><?= Yii::t('app', 'franchise.views.organization.vendors_list_two', ['ru' => 'Список поставщиков ресторана']) ?> <?= $organization->name ?></h4>
                        </div>

                        <div class="col-lg-2 col-md-2 col-sm-2" style="margin-bottom: 20px;">
                            <?= Html::label(Yii::t('message', 'frontend.views.client.anal.currency', ['ru' => 'Валюта']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                            <?=
                            Html::dropDownList('filter_currency', null, $currencyData['currency_list'], ['class' => 'form-control', 'id' => 'filter_currency'])
                            ?>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?=
                                GridView::widget([
                                    'id' => 'clientsList',
                                    'dataProvider' => $dataProvider,
                                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                                    'filterModel' => $searchModel,
                                    'filterPosition' => false,
                                    'pjax' => true,
                                    'pjaxSettings' => ['options' => ['id' => 'client-list'], 'loadingCssClass' => false],
                                    'summary' => '',
                                    'options' => ['class' => 'table-responsive'],
                                    'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                                    'pager' => [
                                        'maxButtonCount' => 5, // Set maximum number of page buttons that can be displayed
                                    ],
                                    'columns' => [
                                        [
                                            'attribute' => 'id',
                                            'value' => 'id',
                                            'label' => '№',
                                        ],
                                        [
                                            'format' => 'raw',
                                            'attribute' => 'name',
                                            'value' => function ($data) {
                                                if ($data['self_registered'] == \common\models\FranchiseeAssociate::SELF_REGISTERED) {
                                                    return "<i title='" . Yii::t('app', 'franchise.views.organization.self_registered_four', ['ru' => 'Клиент самостоятельно зарегистрировался']) . "' class='fa fa-bolt text-success' aria-hidden='true'></i>" . $data['name'];
                                                }
                                                return $data['name'];
                                            },
                                            'label' => Yii::t('app', 'franchise.views.organization.vendors_name', ['ru' => 'Имя поставщика']),
                                        ],
                                        [
                                            'format' => 'raw',
                                            'attribute' => 'associated_count',
                                            'value' => function ($data) {
                                                $progress = $data["associated_count"] > 0 ? round($data["associated_count_prev30"] * 100 / $data["associated_count"], 2) : 0;
//                                            if ($progress > 0) {
                                                $divider = '<i class="fa fa-caret-up"></i>';
                                                //                                          }
                                                $class = "text-red";
                                                if ($progress > 20) {
                                                    $class = "text-green";
                                                } elseif ($progress > 0) {
                                                    $class = " text-orange";
                                                }
                                                return $data["associated_count"] . " <span class='description-percentage $class'>$divider $progress%";
                                            },
                                            'label' => Yii::t('app', 'franchise.views.organization.rest_quantity', ['ru' => 'Кол-во ресторанов']),
                                        ],
                                        [
                                            'format' => 'raw',
                                            'attribute' => 'order_count',
                                            'value' => function ($data) {
                                                $progress = $data["order_count"] > 0 ? round($data["order_count_prev30"] * 100 / $data["order_count"], 2) : 0;
//                                            if ($progress > 0) {
                                                $divider = '<i class="fa fa-caret-up"></i>';
                                                //                                          }
                                                $class = "text-red";
                                                if ($progress > 20) {
                                                    $class = "text-green";
                                                } elseif ($progress > 0) {
                                                    $class = " text-orange";
                                                }
                                                return $data["order_count"] . " <span class='description-percentage $class'>$divider $progress%";
                                            },
                                            'label' => Yii::t('app', 'franchise.views.organization.orders_quan_three', ['ru' => 'Кол-во заказов']),
                                        ],
                                        [
                                            'format' => 'raw',
                                            'attribute' => 'order_sum',
                                            'value' => function ($data) use ($currencyData) {
                                                $progress = $data["order_sum"] ? round($data["order_sum_prev30"] * 100 / $data["order_sum"], 2) : 0;
//                                            if ($progress > 0) {
                                                $divider = '<i class="fa fa-caret-up"></i>';
                                                //                                          }
                                                $class = "text-red";
                                                if ($progress > 20) {
                                                    $class = "text-green";
                                                } elseif ($progress > 0) {
                                                    $class = " text-orange";
                                                }
                                                return ($data["order_sum"] ? $data["order_sum"] : 0) . " " . $currencyData['iso_code'] . " <span class='description-percentage $class'>$divider $progress%";
                                            },
                                            'label' => Yii::t('app', 'franchise.views.organization.orders_sum_two', ['ru' => 'Сумма заказов']),
                                        ],
                                        [
                                            'format' => 'raw',
                                            'attribute' => 'created_at',
                                            'value' => function ($data) {
                                                $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                                return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                            },
                                            'label' => Yii::t('app', 'franchise.views.organization.reg_date', ['ru' => 'Дата регистрации']),
                                        ],
                                        [
                                            'attribute' => 'contact_name',
                                            'value' => 'contact_name',
                                            'label' => Yii::t('app', 'franchise.views.organization.contact_three', ['ru' => 'Контакт']),
                                        ],
                                        [
                                            'attribute' => 'phone',
                                            'value' => 'phone',
                                            'label' => Yii::t('app', 'franchise.views.organization.phone_five', ['ru' => 'Телефон']),
                                        ],
//                                    [
//                                        'format' => 'raw',
//                                        'value' => function ($data) {
//                                            return Html::a('<i class="fa fa-signal"></i>', ['analytics/vendor-stats', 'id' => $data["id"]], ['class' => 'stats']);
//                                        },
//                                    ],
                                    ],
                                    'rowOptions' => function ($model, $key, $index, $grid) {
                                        return ['data-url' => Url::to(['organization/ajax-show-vendor', 'id' => $model["id"]])];
                                    },
                                ]);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body tab-pane" id="tab_4">
                        <div class="modal-header f-header">
                            <div class="row">
                                <div class="col-md-11">
                                    <h4 class="modal-title f-title"><?= Yii::t('app', 'franchise.views.organization.employees_list_two', ['ru' => 'Список сотрудников ресторана']) ?> <?= $organization->name ?></h4>
                                </div>
                                <div class="col-md-1">
                                    <div class="pull-right" style="margin-right: 10px;">
                                        <?= ExportMenu::widget([
                                            'dataProvider' => $managersDataProvider,
                                            'columns' => $exportColumns,
                                            'fontAwesome' => true,
                                            'filename' => Yii::t('message', 'frontend.views.vendor.emp_two', ['ru' => 'Сотрудники']) . ' - ' . date('Y-m-d'),
                                            'encoding' => 'UTF-8',
                                            'target' => ExportMenu::TARGET_SELF,
                                            'showConfirmAlert' => false,
                                            'showColumnSelector' => false,
                                            'batchSize' => 200,
                                            'timeout' => 0,
                                            'dropdownOptions' => [
                                                'label' => '<span class="text-label">' . Yii::t('app', 'franchise.views.site.download_list', ['ru' => 'Скачать список']) . ' </span>',
                                                'class' => ['btn btn-outline-default btn-sm'],
                                                'style' => 'margin-right:10px;',
                                            ],
                                            'exportConfig' => [
                                                ExportMenu::FORMAT_HTML => false,
                                                ExportMenu::FORMAT_TEXT => false,
                                                ExportMenu::FORMAT_EXCEL => false,
                                                ExportMenu::FORMAT_PDF => false,
                                                ExportMenu::FORMAT_CSV => false,
                                                ExportMenu::FORMAT_EXCEL_X => [
                                                    'label' => Yii::t('kvexport', 'Excel'),
                                                    'icon' => 'file-excel-o',
                                                    'iconOptions' => ['class' => 'text-success'],
                                                    'linkOptions' => [],
                                                    'options' => ['title' => Yii::t('kvexport', 'Microsoft Excel 2007+ (xlsx)')],
                                                    'alertMsg' => Yii::t('kvexport', 'Файл EXCEL( XLSX ) будет генерироваться для загрузки'),
                                                    'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                                    'extension' => 'xlsx',
                                                    //'writer' => 'Excel2007',
                                                    'styleOptions' => [
                                                        'font' => [
                                                            'bold' => true,
                                                            'color' => [
                                                                'argb' => 'FFFFFFFF',
                                                            ],
                                                        ],
                                                        'fill' => [
                                                            'type' => PHPExcel_Style_Fill::FILL_NONE,
                                                            'startcolor' => [
                                                                'argb' => 'FFFFFFFF',
                                                            ],
                                                            'endcolor' => [
                                                                'argb' => 'FFFFFFFF',
                                                            ],
                                                        ],
                                                    ]
                                                ],
                                            ],
                                            'onRenderSheet' => function ($sheet, $grid) {
                                                $i = 2;
                                                while ($sheet->cellExists("B" . $i)) {
                                                    $sheet->setCellValue("B" . $i, html_entity_decode($sheet->getCell("B" . $i)));
                                                    $i++;
                                                }
                                                $j = 2;
                                                while ($sheet->cellExists("C" . $j)) {
                                                    $sheet->setCellValue("C" . $j, html_entity_decode($sheet->getCell("C" . $j)));
                                                    $j++;
                                                }
                                            }
                                        ]);
                                        ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <?= GridView::widget([
                                'dataProvider' => $managersDataProvider,
                                'columns' => [
                                    'id',
                                    'profile.full_name',
                                    'email',
                                    'profile.phone'
                                ],
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>
        <?php
        Modal::begin([
            'id' => 'vendorInfo',
        ]);
        ?>
        <?php Modal::end(); ?>
    </section>
<?php
$url = Url::to(['organization/show-client', 'id' => $organization->id]);
$customJs = <<< JS

$("#filter_currency").on("change", function () {
$("#filter_currency").attr('disabled','disabled')

var filter_currency =  $("#filter_currency").val();

$.pjax({
type: 'GET',
push: false,
timeout: 10000,
url: "$url",
container: "#client-list",
data: {
filter_currency: filter_currency
}
}).done(function(text) { $("#filter_currency").removeAttr('disabled') });
});

JS;
$this->registerJs($customJs, \yii\web\View::POS_READY);