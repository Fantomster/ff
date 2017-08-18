<?php
use yii\helpers\Url;
use yii\widgets\ListView;
use yii\web\View;
use kartik\form\ActiveForm;

$this->registerJs('
    $(document).on("click", ".delete-guide, .delete-product", function(e) {
        e.preventDefault();
        clicked = $(this);
        if (clicked.hasClass(".delete-guide")) {
            title = "Удаление гайда";
            text = "Вы уверены, что хотите удалить гайд?";
            success = "Гайд удален!";
        } else if (clicked.hasClass(".delete-product")){
            title = "Удаление товара";
            text = "Вы уверены, что хотите удалить товара из гайда?";
            success = "Товар удален!";
        }
        swal({
            title: title,
            text: text,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Да, удалить",
            cancelButtonText: "Отмена",
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return new Promise(function (resolve, reject) {
                    $.post(
                        clicked.data("url")
                    ).done(function (result) {
                        if (result) {
                            resolve(result);
                        } else {
                            resolve(false);
                        }
                    });
                })
            },
        }).then(function() {
            swal({title: success, type: "success"});
        });
    });

    $(document).on("click", ".create-guide", function(e) {
        e.preventDefault();
        var clicked = $(this);
        title = "Назовите ваш новый гайд";
        success = "Заказ оформлен!";
        swal({
            title: title,
            text: text,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Да",
            cancelButtonText: "Отмена",
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return new Promise(function (resolve, reject) {
                    $.post(
                        clicked.data("url"),
                        form.serialize() + extData
                    ).done(function (result) {
                        if (result) {
                            resolve(result);
                        } else {
                            resolve(false);
                        }
                    });
                })
            },
        }).then(function() {
            swal({title: success, type: "success"});
        });
    });
', View::POS_READY);

?>

<section class="content">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li><a href="<?= Url::to(['order/create']) ?>">Все продукты</a></li>
            <li class="active">
                <a href="#">
                    Гайды заказов <small class="label bg-yellow">new</small>
                </a>
            </li>
            <li>
                <a href="<?= Url::to(['order/favorites']) ?>">
                    Избранные <small class="label bg-yellow">new</small>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="guid-header">
                        <div class="pull-left">
                            <?php
                    $form = ActiveForm::begin([
                                'options' => [
                                    'id' => 'searchForm',
                                    'class' => "navbar-form no-padding no-margin",
                                    'role' => 'search',
                                ],
                    ]);
                    ?>
                    <?=
                            $form->field($searchModel, 'searchString', [
                                'addon' => [
                                    'append' => [
                                        'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
                                        'options' => [
                                            'class' => 'append',
                                        ],
                                    ],
                                ],
                                'options' => [
                                    'class' => "margin-right-15 form-group",
                                ],
                            ])
                            ->textInput([
                                'id' => 'searchString',
                                'class' => 'form-control',
                                'placeholder' => 'Поиск'])
                            ->label(false)
                    ?>
                    <?php ActiveForm::end(); ?>
                        </div>
                        <div class="pull-right">
                            <a class="btn btn-md btn-outline-success new-guid" href="create.html" data-toggle="tooltip" data-original-title="Создать гайд" data-url="#"><i class="fa fa-plus"></i> Создать гайд</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr>	
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 guid">
                    <?php
                    Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'guidesList', 'timeout' => 30000]);
                    ?>
                    <?=
                                ListView::widget([
                                    'dataProvider' => $dataProvider,
                                    'itemView' => function ($model, $key, $index, $widget) {
                                        return $this->render('guides/_list-view', ['model' => $model]);
                                    },
                                            'pager' => [
                                                'maxButtonCount' => 5,
                                                'options' => [
                                                    'class' => 'pagination col-md-12  no-padding'
                                                ],
                                            ],
                                            'options' => [
                                                'class' => 'col-lg-12 list-wrapper inline no-padding'
                                            ],
                                            'layout' => "\n{items}\n<div class='pull-left'>{pager}</div><div class='pull-right summary-pages'>{summary}</div>",
                                            'summary' => 'Показано {count} из {totalCount}',
                                            'emptyText' => 'Список пуст',
                                        ])
                                        ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</section>