<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\User;
use common\models\Role;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\widgets\Pjax;

$user = new User();
$role = new Role();

$this->registerJs(
        '$("document").ready(function(){
            $("#users-list").on("change keyup paste cut", "input", function() {
                $("#search-form").submit();
            });
            $("#users-list").on("pjax:complete", function() {
                var searchInput = $("#search-string");
                var strLength = searchInput.val().length * 2;
                searchInput.focus();
                searchInput[0].setSelectionRange(strLength, strLength);
            });
            $("#add-user").on("click", ".edit", function() {
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



    <div class="box box-info">
        <div class="box-header">
        </div>
        <?=
Modal::widget([
    'id' => 'add-user',
    'clientOptions' => false,
    'toggleButton' => [
        'label' => ' Добавить пользователя',
        'tag' => 'a',
        'data-target' => '#add-user',
        'class' => 'btn btn-primary pull-right',
        'href' => Url::to(['/vendor/ajax-create-user']),
    ],
])
?>
<?php Pjax::begin(['enablePushState' => false, 'id' => 'users-list',]); 
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
                    Pjax::end();
        ?>
    </div>
        <?php  ?>

        <?php
        Modal::begin([
            'id' => 'user-edit',
        ]);
        ?>
        <?php Modal::end(); ?>
