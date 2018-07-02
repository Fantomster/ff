<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = $model->profile->full_name;
$clients = !empty(Yii::$app->session->get('clients')) ? Yii::$app->session->get('clients') : 'index';
$clientsName = !empty(Yii::$app->session->get('clients_name')) ? Yii::$app->session->get('clients_name') : 'Пользователи';
$this->params['breadcrumbs'][] = ['label' => $clientsName, 'url' => [$clients]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    echo Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary', 'style' => 'margin-bottom: 10px;']);
    if ($model->role_id === \common\models\Role::ROLE_FKEEPER_MANAGER) {
        echo Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'style' => 'margin-bottom: 10px; margin-left: 10px;',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]);
    }
    print " ";
    echo Html::a('Вернуться к просмотру списка', [$clients], ['class' => 'btn btn-primary', 'style' => 'margin-bottom: 10px;']);
    ?>

    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            //'role.name',
            [
                'value' => $model->role->name,
                'label' => 'Роль',
            ],
            [
                'format' => 'raw',
                'value' => function($data) {
                    switch($data->status) {
                        case 0: return 'Не активен';
                            break;
                        case 1: return 'Активен';
                            break;
                        case 2: return 'Ожидается подтверждение E-mail';
                            break;
                    }
                },
                'label' => 'Статус',
            ],
            [
                'format' => 'raw',
                'attribute' => 'email',
                'label' => \Yii::t('app', 'Email'),
                'value' => function ($data) use ($model) {
                    $html = Html::tag('a', $data->email, ['href' => 'mailto:' . $data->email]);

                    //Прверяем не попал ли Email в черный список
                    if ($model->getEmailInBlackList()) {
                        $html .= '<span class="badge pull-right">' . \Yii::t('app', 'Email в черном списке') . '</span>';
                    }

                    //Находим последний фэйл по этому емэйлу
                    if ($lastFail = $model->getEmailLastFail()) {
                        $body = json_decode($lastFail->body, true);
                        $reason = null;
                        switch ($body['notificationType']) {
                            case 'Complaint':
                                $reason = $body['complaint']['complaintFeedbackType'];
                                break;
                            case 'Bounce':
                                if (isset($body['bounce']['bouncedRecipients'][0])) {
                                    $reason = $body['bounce']['bouncedRecipients'][0]['diagnosticCode'];
                                }
                                break;
                        }
                        //Добавляем сообщение
                        if($reason !== null) {
                            $html .= '<div class="email-error text-sm alert alert-danger" >';
                            $html .= '<b>' . \Yii::t('app', 'Не удалось отправить письмо') . '</b><br>';
                            $html .= \Yii::t('app', 'Причина') . ':' . $reason;
                            $html .= '</div>';
                        }
                    }

                    return $html;
                }
            ],
            'logged_in_ip',
            'logged_in_at',
            [
                'format' => 'raw',
                'attribute' => 'logged_in_at',
                'label' => 'Дата последней авторизации',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->logged_in_at, "php:j M Y, H:i:s");
                }
            ],
            'created_ip',
            [
                'attribute' => 'created_at',
                'label' => 'Дата создания',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->created_at, "php:j M Y, H:i:s");
                }
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Последнее изменение',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->updated_at, "php:j M Y, H:i:s");
                }
            ],
//            'banned_at',
//            'banned_reason',
//            'organization_id',
            [
                'format' => 'raw',
                'value' => Html::a($model->organization_id, ['organization/view', 'id' => $model->organization_id]),
                'label' => 'ID организации',
            ],
            [
                'format' => 'raw',
                'value' => isset($model->organization) ? Html::a($model->organization->name, ['organization/view', 'id' => $model->organization_id]) : 'Отсутствует',
                'label' => 'Организация',
            ],
            [
                'format' => 'raw',
                'value' => function($data) {
                    switch($data->profile->gender) {
                        case 0: return 'Не указан';
                            break;
                        case 1: return 'Мужской';
                            break;
                        case 2: return 'Женский';
                            break;
                    }
                },
                'label' => 'Пол',
            ],
            [
                'format' => 'raw',
                'value' => function($data) {
                    if($data->profile->job_id==0) return 'Не указана'; else return \common\models\Job::getJobById($data->profile->job_id)['name_job'];
                },
                'label' => 'Должность',
            ],
            [
                'format' => 'raw',
                'value' => $model->profile->phone,
                'label' => 'Телефон',
            ],
            [
                'format' => 'raw',
                'value' => function($data) {
                    switch($data->profile->sms_allow) {
                        case 0: return 'Не указано';
                            break;
                        case 1: return 'Согласен';
                            break;
                        case 2: return 'Не согласен';
                            break;
                    }
                },
                'label' => 'Согласие на смс-рассылки',
            ],
            [
                'format' => 'raw',
                'value' => function($data) {
                    switch($data->profile->email_allow) {
                        case 0: return 'Не указано';
                            break;
                        case 1: return 'Согласен';
                            break;
                        case 2: return 'Не согласен';
                            break;
                    }
                },
                'label' => 'Согласие на Email-рассылки',
            ],
        ],
    ])
    ?>

</div>

<?php if (Yii::$app->session->hasFlash('Forgot-success')): ?>
    <div class="alert alert-info" role="alert">
        <?= Yii::$app->session->getFlash('Forgot-success') ?>
    </div>
<?php endif; ?>

<?php
$form = \yii\widgets\ActiveForm::begin(['method' => 'post']);
?>

<?= $form->field($newPassModel, 'email')->hiddenInput(['value' => $model->email])->label(false); ?>

<?= Html::submitButton('Выслать письмо со сменой пароля', ['class' => 'btn btn-primary']) ?>

<?php \yii\widgets\ActiveForm::end(); ?>
