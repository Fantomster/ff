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

$this->title = implode(" - ", [
    Yii::t('app', 'franchise.views.site.settings', ['ru'=>'Настройки']),
    Yii::t('app', 'franchise.views.site.employees', ['ru'=>'Сотрудники'])
]);

$user = new User();
$role = new Role();

$this->registerJs(
        '$("document").ready(function(){
            var timer;
            $(".content").on("change keyup paste cut", "#searchString", function() {
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#search-form").submit();
                }, 700);
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
            $(".content").on("click", ".delete", function() {
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
            $("body").on("hidden.bs.modal", "#add-user, #userEdit", function() {
                $(this).data("bs.modal", null);
                $.pjax.reload({container: "#users-list"});
            });
            $("body").on("click", "td", function (e) {
                var url = $(this).parent("tr").data("url");
                if (url !== undefined) {
                    $("#userEdit").modal({backdrop:"static",toggle:"modal"}).load(url);
                }
            });
        });'
);
$this->registerCss("
    tr:hover{cursor: pointer;}
        ");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> <?= Yii::t('app', 'franchise.views.site.employees_two', ['ru'=>'Сотрудники']) ?>
        <small><?= Yii::t('app', 'franchise.views.site.employees_three', ['ru'=>'Список сотрудников организации']) ?></small>
    </h1>
    <?=
    ''
//    Breadcrumbs::widget([
//        'options' => [
//            'class' => 'breadcrumb',
//        ],
//        'links' => [
//            'Настройки',
//            'Сотрудники',
//        ],
//    ])
    ?>
</section>
<section class="content">
    <div class="box box-info settings">
        <div class="box-header">
            <?php
            $form = ActiveForm::begin([
                        'options' => [
                            //  'data-pjax' => true,
                            'id' => 'search-form',
                            'role' => 'search',
                        ],
                            //'method' => 'get',
            ]);
            ?>
            <div class="row"> 

                <div class="col-md-3">
                    <?=
                    $form->field($searchModel, 'searchString')->textInput([
                        'id' => 'searchString',
                        'class' => 'form-control',
                        'placeholder' => Yii::t('app', 'franchise.views.site.search', ['ru'=>'Поиск'])])->label(false)
                    ?>
                </div><div class="col-md-9">
                    <?=
                    Modal::widget([
                        'id' => 'add-user',
                        'clientOptions' => false,
                        'toggleButton' => [
                            'label' => '<i class="icon fa fa-user-plus"></i>  ' . Yii::t('app', 'franchise.views.site.add_employee', ['ru'=>'Добавить сотрудника']) . ' ',
                            'tag' => 'a',
                            'data-target' => '#add-user',
                            'class' => 'btn btn-sm btn-success pull-right',
                            'href' => Url::to(['ajax-create-user']),
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
                            'value' => 'profile.full_name',
                        ],
                        'email',
                        'profile.phone',
                        [
                            'attribute' => 'role.name',
                            'label' => Yii::t('app', 'franchise.views.site.role', ['ru'=>'Роль']),
                            'value' => function($model) {
                                return Yii::t('app', $model['role']['name']);
                            }
                        ],
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
                    'rowOptions' => function ($model, $key, $index, $grid) {
                return ['data-url' => Url::to(['ajax-update-user', 'id' => $model->id])];
            },
                ]);
                ?>
<?php Pjax::end(); ?>
            </div>
        </div>
        <?php
        Modal::begin([
            'id' => 'userEdit',
        ]);
        ?>
<?php Modal::end(); ?>
</section>
