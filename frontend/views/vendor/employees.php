<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\User;
use common\models\Role;
use kartik\form\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;

$user = new User();
$role = new Role();

$this->registerJs(
        '$("document").ready(function(){
            $(".content").on("change keyup paste cut", "#searchString", function() {
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#search-form").submit();
                }, 700);
            });
            $("#users-list").on("pjax:complete", function() {
                var searchInput = $("#search-string");
                var strLength = searchInput.val().length * 2;
                searchInput.focus();
                searchInput[0].setSelectionRange(strLength, strLength);
            });
            $(".content").on("click", ".edit", function() {
                var form = $("#user-form");
                $.post(
                    form.attr("action"),
                    form.serialize()
                )
                .done(function(result) {
                    form.replaceWith(result);
                });
                return false;
            });
            $(".content").on("click", ".delete", function(e) {
                var form = $("#user-form");
                $.post(
                    $(this).data("action"),
                    {id:$(this).data("id")}
                )
                .done(function(result) {
                    form.replaceWith(result);
                });
                return false;
            });
            $("body").on("hidden.bs.modal", "#add-user", function() {
                $(this).data("bs.modal", null);
                $.pjax.reload({container: "#users-list"});
            });
        });'
);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> Сотрудники
        <small>Список сотрудников организации</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Настройки',
            'Сотрудники',
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info settings">
        <div class="box-header">
        <?php
        $form = ActiveForm::begin([
                    'options' => [
//                        'data-pjax' => true,
                        'id' => 'search-form',
                        'role' => 'search',
                    ],
  //                  'method' => 'get',
        ]);
        ?>
            <div class="row">
                
            <div class="col-md-3">
        <?=
        $form->field($searchModel, 'searchString', [
                        'addon' => ['prepend' => ['content' => '<i class="fa fa-search"></i>']]
                    ])->textInput([
            'id' => 'searchString',
            'class' => 'form-control',
            'placeholder' => 'Поиск'])->label(false)
        ?>
            </div><div class="col-md-9">
        <?=
        Modal::widget([
            'id' => 'add-user',
            'clientOptions' => false,
            'toggleButton' => [
                'label' => '<i class="icon fa fa-user-plus"></i>  Добавить сотрудника',
                'tag' => 'a',
                'data-target' => '#add-user',
                'class' => 'btn btn-success pull-right',
                'href' => Url::to(['/vendor/ajax-create-user']),
            ],
        ])
        ?>
            </div>
        <?php ActiveForm::end(); ?>
        </div>
       <div class="box-body no-padding">
        <!--?= Html::button('Добавить пользователя', ['id' => 'add-user', 'class' => 'btn btn-primary']) ?-->
        <?php Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'users-list', 'timeout' => 5000]); ?>
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterPosition' => false,
            'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'profile.full_name',
                    'format' => 'raw',
                    'value' => function ($data) {
                        $link = Html::a($data->profile->full_name, ['vendor/ajax-update-user', 'id' => $data->id], [
                                    'data' => [
                                        'target' => '#add-user',
                                        'toggle' => 'modal',
                                        'backdrop' => 'static',
                                    ]
                        ]);
                        return $link;
                    },
                        ],
                        'email',
                        'profile.phone',
                        'role.name',
                        [
                            'attribute' => 'status',
                            'label' => Yii::t('user', 'Status'),
                            'filter' => $user::statusDropdown(),
                            'value' => function($model, $index, $dataColumn) use ($user) {
                                $statusDropdown = $user::statusDropdown();
                                return $statusDropdown[$model->status];
                            },
                        ],
                    ],
                ]);
                ?>
                <?php Pjax::end(); ?>
            </div>
            </div>
            <?php
            Modal::begin([
                'id' => 'user-edit',
            ]);
            ?>
            <?php Modal::end(); ?>
</section>
