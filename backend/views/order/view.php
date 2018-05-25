<?php

/* @var $this yii\web\View */
/* @var $model common\models\Order */

use yii\helpers\Html;
$this->title = Yii::t('app', 'Заказ') . ' №' . $model->id;
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Заказы'), 'url' => ['index']],
    $this->title
];
?>

<style>
    .wrap > .container {
        padding-top: 70px !important;
    }
</style>

<div class="order-view">
    <div class="header">
        <h2><?= Html::encode($this->title) ?></h2>
    </div>
    <hr>
    <section class="content">
        <div class="container1 ">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <?= $this->render('_bill', ['order' => $model, 'dataProvider' => $dataProvider]) ?>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-6">
                        <div class="panel panel-success">
                            <div class="panel-heading">История чата</div>
                            <div class="panel-body">
                                <?php if (!empty($model->orderChat)): ?>
                                    <?php
                                    foreach ($model->orderChat as $chat) {
                                        echo $this->render('_chat-message', [
                                            'name' => $chat->sentBy->profile->full_name,
                                            'message' => $chat->message,
                                            'time' => $chat->created_at,
                                            'isSystem' => $chat->is_system,
                                            'recipient' => $chat->recipient,
                                        ]);
                                    }
                                    ?>
                                <?php else: ?>
                                    <div>
                                        <?= Yii::t('app', 'В чате нет сообщений') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="panel panel-danger">
                            <div class="panel-heading">Ошибки отправки Email</div>
                            <div class="panel-body">
                                <ul class="list-group">
                                    <?php
                                    foreach ($model->getRecipientsList() as $recipient) {

                                        $message = 'нет ошибок';
                                        $class = 'list-group-item-success';

                                        if (\common\models\notifications\EmailFails::findOne(['email' => $recipient->email])) {
                                            $message = 'ошибка при отправке почты';
                                            $class = 'list-group-item-warning';
                                        }

                                        if (\common\models\notifications\EmailBlacklist::findOne(['email' => $recipient->email])) {
                                            $message = 'в черном списке';
                                            $class = 'list-group-item-danger';
                                        }

                                        echo Html::tag('li', '<b>' . $recipient->email . '</b> ' . $message . '.<br>', ['class' => 'list-group-item ' . $class]);
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
