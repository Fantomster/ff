<?php

use yii\widgets\Breadcrumbs;

$this->title = 'Интеграция Email: ТОРГ - 12';

/**
 * @var $model \common\models\IntegrationSettingFromEmail
 * @var $this \yii\web\View
 */

$exclude_attributes = [
    'id',
    'password',
    'updated_at'
];

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> <?= $this->title ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr/default'],
            ],
            $this->title,
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <?= \Yii::$app->controller->module->renderMenu() ?>
                <a href="<?=\yii\helpers\Url::to(['/clientintegr/email/setting/create'])?>"
                   class="btn btn-success">
                    <i class="fa fa-plus"></i> Подключение
                </a>
            </div>
            <div class="box-body">
                <?php if (!empty($models)): ?>
                    <?php foreach ($models as $model): ?>
                        <div class="panel panel-default col-sm-4">
                            <div class="panel-heading">
                                Настройки #<?= $model->id ?>

                                <a class="pull-right"
                                   href="<?= \yii\helpers\Url::to(['setting/delete', 'setting_id' => $model->id]) ?>">
                                    <i class="fa fa-trash-o" style="color: red"></i>
                                </a>
                                <p class="pull-right">&nbsp;&nbsp;&nbsp;</p>
                                <a class="pull-right"
                                   href="<?= \yii\helpers\Url::to(['setting/edit', 'setting_id' => $model->id]) ?>">
                                    <i class="fa fa-gear" style="color: green"></i>
                                </a>

                            </div>
                            <table class="table">
                                <tr>
                                    <td><b><?= $model->getAttributeLabel('organization_id') ?>:</b></td>
                                    <td><?= $model->organization->name ?></td>
                                </tr>
                                <tr>
                                    <td><b><?= $model->getAttributeLabel('server_type') ?>:</b></td>
                                    <td><?= $model->server_type ?></td>
                                </tr>
                                <tr>
                                    <td><b><?= $model->getAttributeLabel('server_host') ?>:</b></td>
                                    <td><?= $model->server_host ?></td>
                                </tr>
                                <tr>
                                    <td><b><?= $model->getAttributeLabel('server_port') ?>:</b></td>
                                    <td><?= $model->server_port ?></td>
                                </tr>
                                <tr>
                                    <td><b><?= $model->getAttributeLabel('server_ssl') ?>:</b></td>
                                    <td><?= ($model->server_ssl ? 'Да' : 'Нет') ?></td>
                                </tr>
                                <tr>
                                    <td><b><?= $model->getAttributeLabel('user') ?>:</b></td>
                                    <td><?= $model->user ?></td>
                                </tr>
                                <tr>
                                    <td><b><?= $model->getAttributeLabel('is_active') ?>:</b></td>
                                    <td><?= ($model->is_active ? 'Да' : 'Нет') ?></td>
                                </tr>
                                <tr>
                                    <td><b><?= $model->getAttributeLabel('created_at') ?>:</b></td>
                                    <td><?= gmdate('d.m.Y H:i', strtotime($model->created_at)) ?></td>
                                </tr>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Необходимо добавить подключение
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>