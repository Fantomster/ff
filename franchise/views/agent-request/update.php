<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AgentRequest */

$this->title = Yii::t('app', 'Редактировать заявку №') . $model->id;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'Редактировать заявку') ?>
    </h1>
</section>
<section class="content">
    <div class="row hidden-xs">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body with-border">
                    <?=
                    $this->render('_form', [
                        'model' => $model,
                        'attachment' => $attachment,
                    ])
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>