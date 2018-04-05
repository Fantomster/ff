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

$this->title = Yii::t('message', 'frontend.views.client.emp.settings', ['ru' => 'Настройки']);
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
            $(".content").on("click", ".edit", function() {
                $(".edit").button("loading");
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
                $(".delete").button("loading");
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
                $.pjax.reload({container: "#users-list",timeout:30000});
            });
        });'
);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-gears"></i> <?= Yii::t('message', 'frontend.views.client.emp.employees', ['ru' => 'Сотрудники']) ?>
        <small><?= Yii::t('message', 'frontend.views.client.emp.list', ['ru' => 'Список сотрудников организации']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
        'links' => [
            Yii::t('message', 'frontend.views.client.emp.settings_two', ['ru' => 'Настройки']),
            Yii::t('message', 'frontend.views.client.emp.employees_two', ['ru' => 'Сотрудники']),
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
                        'placeholder' => Yii::t('message', 'frontend.views.client.emp.search', ['ru' => 'Поиск'])])->label(false)
                    ?>
                </div><div class="col-md-9">
                    <?=
                    Modal::widget([
                        'id' => 'add-user',
                        'clientOptions' => false,
                        'toggleButton' => [
                            'label' => '<i class="icon fa fa-user-plus"></i>  ' . Yii::t('message', 'frontend.views.client.emp.add', ['ru' => 'Добавить сотрудника']),
                            'tag' => 'a',
                            'data-target' => '#add-user',
                            'class' => 'btn btn-success pull-right',
                            'href' => Url::to(['/client/ajax-create-user']),
                        ],
                    ])
                    ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
            <div class="box-body no-padding">
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

                                if ($data->profile === null) {
                                    return '';
                                }

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
                        [
                            'attribute' => 'email',
                            'format' => 'raw',
                            'value' => function ($data) {
                                $link = Html::a($data->email, ['client/ajax-update-user', 'id' => $data->id], [
                                            'data' => [
                                                'target' => '#add-user',
                                                'toggle' => 'modal',
                                                'backdrop' => 'static',
                                            ]
                                ]);
                                return $link;
                            },
                        ],
                        'profile.phone',
                        [
                            'attribute' => 'role.name',
                            'label' => \Yii::t('app', 'frontend.views.client.emp.role', ['ru' => 'Роль']),
                            'value' => function($model) {
                                return Yii::t('app', Role::getRoleName($model->getRelationUserOrganizationRoleID($model->id)));
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'label' => Yii::t('app', 'frontend.views.client.emp.status', ['ru' => 'Статус']),
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
