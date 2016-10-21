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
            var timer = null;
            $(".content").on("change keyup paste cut", "#searchString", function() {
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#search-form").submit();
                }, 300);
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
            $("body").on("hidden.bs.modal", "#add-user", function() {
                $(this).data("bs.modal", null);
                $.pjax.reload({container: "#users-list"});
            });
        });'
);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> Работники
        <small>Список работников организации</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Настройки',
            'Работники',
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info settings">
        <?php Pjax::begin(['enablePushState' => false, 'id' => 'users-list', 'timeout' => 3000]); ?>
        <?=
        Modal::widget([
            'id' => 'add-user',
            'clientOptions' => false,
            'toggleButton' => [
                'label' => ' Добавить пользователя',
                'tag' => 'a',
                'data-target' => '#add-user',
                'class' => 'btn btn-primary pull-right margin-right-15',
                'href' => Url::to(['/client/ajax-create-user']),
            ],
        ])
        ?>
        <?php
        $form = ActiveForm::begin([
                    'options' => [
                        'data-pjax' => true,
                        'id' => 'search-form',
                        'class' => "navbar-form",
                        'role' => 'search',
                    ],
                    'method' => 'get',
        ]);
        ?>
        <?=
        $form->field($searchModel, 'searchString')->textInput([
            'id' => 'search-string',
            'class' => 'form-control',
            'placeholder' => 'Поиск'])->label(false)
        ?>
        <?php ActiveForm::end(); ?>
        <!--?= Html::button('Добавить пользователя', ['id' => 'add-user', 'class' => 'btn btn-primary']) ?-->
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterPosition' => false,
            'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'profile.full_name',
                    'format' => 'raw',
                    'value' => function ($data) {
                        $link = Html::a($data->profile->full_name, ['client/ajax-update-user', 'id' => $data->id], [
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
            <?php
            Modal::begin([
                'id' => 'user-edit',
            ]);
            ?>
            <?php Modal::end(); ?>
</section>
