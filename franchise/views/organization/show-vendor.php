<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use yii\bootstrap\Modal;

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
        <i class="fa fa-home"></i> Поставщик <?= $organization->name ?>
    </h1>
</section>
<section class="content">

    <div class="box box-info order-history">
        <div class="box-body">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Поставщик</a></li>
                <?php if($showButton): ?>
                <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Реквизиты</a></li>
                <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">Список ресторанов поставщика</a></li>
                <?php endif; ?>
                <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">Список сотрудников поставщика</a></li>
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
                                    <?= isset($catalog->id) ? Html::a('Прайс-листы поставщика', ['catalog/index', 'vendor_id' => $organization->id], ['class' => 'btn btn-green btn-block']) : '' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <div class="edite-place">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Название поставщика:</label>
                                    <p><?= $organization->name ?></p>
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">Название юр. лица:</label>
                                    <p><?= $organization->legal_entity ?></p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Город:</label>
                                    <p><?= $organization->city ?></p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Адрес:</label>
                                    <p><?= $organization->address ?></p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Краткая информация:</label>
                                    <p><?= $organization->about ?></p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">ФИО контактного лица:</label>
                                    <p><?= $organization->contact_name ?></p>
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1">E-mail контактного лица:</label>
                                    <p><?= $organization->email ?></p>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Телефон контактного лица:</label>
                                    <p><?= $organization->phone ?></p>
                                </div>
                                <?php if(isset($organization->profile->full_name)): ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Управляющий менеджер:</label>
                                    <p><?= $organization->profile->full_name ?></p>
                                </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body tab-pane" id="tab_2">
                    <div class="modal-header f-header">
                        <h4 class="modal-title f-title">Реквизиты поставщика <?= $organization->name ?></h4>
                    </div>
                    <h3>Реквизиты</h3>
                    <label>Подписант: </label>
                    <p><?= $organization->buisinessInfo->signed ?></p>
                    <label>Юридическое название: </label>
                    <p><?= $organization->buisinessInfo->legal_entity ?></p>
                    <label>Юридический адрес: </label>
                    <p><?= $organization->buisinessInfo->legal_address ?></p>
                    <label>Официальный email: </label>
                    <p><?= $organization->buisinessInfo->legal_email ?></p>
                    <label>ИНН: </label>
                    <p><?= $organization->buisinessInfo->inn ?></p>
                    <label>КПП: </label>
                    <p><?= $organization->buisinessInfo->kpp ?></p>
                    <label>ОГРН: </label>
                    <p><?= $organization->buisinessInfo->ogrn ?></p>
                    <label>Банк: </label>
                    <p><?= $organization->buisinessInfo->bank_name ?></p>
                    <label>БИК: </label>
                    <p><?= $organization->buisinessInfo->bik ?></p>
                    <label>Телефон: </label>
                    <p><?= $organization->buisinessInfo->phone ?></p>
                    <label>р/с: </label>
                    <p><?= $organization->buisinessInfo->correspondent_account ?></p>
                    <label>к/с: </label>
                    <p><?= $organization->buisinessInfo->checking_account ?></p>
                    <label>Поле для заметок: </label>
                    <p><?= $organization->buisinessInfo->info ?></p>
                </div>
                <div class="modal-body tab-pane" id="tab_3">
                    <div class="modal-header f-header">
                        <h4 class="modal-title f-title">Список ресторанов поставщика <?= $organization->name ?></h4>
                    </div>
                    <?php
                    Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'vendor-list', 'timeout' => 5000]);
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <?=
                            GridView::widget([
                                'id' => 'vendorsList',
                                'dataProvider' => $dataProvider,
                                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                                //'filterModel' => $searchModel,
                                'filterPosition' => false,
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
                                                return "<i title='Клиент самостоятельно зарегистрировался' class='fa fa-bolt text-success' aria-hidden='true'></i>" . $data['name'];
                                            }
                                            return $data['name'];
                                        },
                                        'label' => 'Имя ресторана',
                                    ],
                                    [
                                        'format' => 'raw',
                                        'attribute' => 'clientCount',
                                        'value' => function ($data) {
                                            $progress = $data["clientCount"] > 0 ? round($data["clientCount_prev30"] * 100 / $data["clientCount"], 2) : 0;
//                                            if ($progress > 0) {
                                            $divider = '<i class="fa fa-caret-up"></i>';
                                            //                                          }
                                            $class = "text-red";
                                            if ($progress > 20) {
                                                $class = "text-green";
                                            } elseif ($progress > 0) {
                                                $class = " text-orange";
                                            }
                                            return $data["clientCount"] . " <span class='description-percentage $class'>$divider $progress%";
                                        },
                                        'label' => 'Кол-во поставщиков',
                                    ],
                                    [
                                        'format' => 'raw',
                                        'attribute' => 'orderCount',
                                        'value' => function ($data) {
                                            $progress = $data["orderCount"] > 0 ? round($data["orderCount_prev30"] * 100 / $data["orderCount"], 2) : 0;
//                                            if ($progress > 0) {
                                            $divider = '<i class="fa fa-caret-up"></i>';
                                            //                                          }
                                            $class = "text-red";
                                            if ($progress > 20) {
                                                $class = "text-green";
                                            } elseif ($progress > 0) {
                                                $class = " text-orange";
                                            }
                                            return $data["orderCount"] . " <span class='description-percentage $class'>$divider $progress%";
                                        },
                                        'label' => 'Кол-во заказов',
                                    ],
                                    [
                                        'format' => 'raw',
                                        'attribute' => 'orderSum',
                                        'value' => function ($data) {
                                            $progress = $data["orderSum"] ? round($data["orderSum_prev30"] * 100 / $data["orderSum"], 2) : 0;
//                                            if ($progress > 0) {
                                            $divider = '<i class="fa fa-caret-up"></i>';
                                            //                                          }
                                            $class = "text-red";
                                            if ($progress > 20) {
                                                $class = "text-green";
                                            } elseif ($progress > 0) {
                                                $class = " text-orange";
                                            }
                                            return ($data["orderSum"] ? $data["orderSum"] : 0) . " руб. <span class='description-percentage $class'>$divider $progress%";
                                        },
                                        'label' => 'Сумма заказов',
                                    ],
                                    [
                                        'format' => 'raw',
                                        'attribute' => 'created_at',
                                        'value' => function ($data) {
                                            $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                            return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                        },
                                        'label' => 'Дата регистрации',
                                    ],
                                    [
                                        'attribute' => 'contact_name',
                                        'value' => 'contact_name',
                                        'label' => 'Контакт',
                                    ],
                                    [
                                        'attribute' => 'phone',
                                        'value' => 'phone',
                                        'label' => 'Телефон',
                                    ],
//                                    [
//                                        'format' => 'raw',
//                                        'value' => function ($data) {
//                                            return Html::a('<i class="fa fa-signal"></i>', ['analytics/vendor-stats', 'id' => $data["id"]], ['class' => 'stats']);
//                                        },
//                                    ],
                                ],
                                'rowOptions' => function ($model, $key, $index, $grid) {
                                    return ['data-url' => Url::to(['organization/ajax-show-client', 'id' => $model["id"]])];
                                },
                            ]);
                            ?>
                        </div>
                    </div>
                    <?php Pjax::end() ?>
                </div>
                <div class="modal-body tab-pane" id="tab_4">
                    <div class="modal-header f-header">
                        <h4 class="modal-title f-title">Список сотрудников поставщика <?= $organization->name ?></h4>
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