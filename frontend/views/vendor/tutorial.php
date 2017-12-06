<?php

use yii\widgets\Breadcrumbs;
$this->title = Yii::t('message', 'frontend.views.vendor.video', ['ru'=>'Обучающие видео']);
?>

<section class="content-header">
    <h1>
        <?= Yii::t('message', 'frontend.views.vendor.video_two', ['ru'=>'Обучающие видео']) ?>
        <small><?= Yii::t('message', 'frontend.views.vendor.examples', ['ru'=>'Примеры работы с системой MixCart']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru'=>'Главная']), 'url' => '/'],
        'links' => [
            Yii::t('message', 'frontend.views.vendor.video_three', ['ru'=>'Обучающие видео']),
        ],
    ])
    ?>

</section>
<section class="content">

    <!-- row -->
    <div class="row">
        <div class="col-md-12">
            <!-- The time line -->
            <ul class="timeline">
                <!-- timeline time label -->
                <li class="time-label">
                    <span class="bg-fk-dark">
                        <?= Yii::t('message', 'frontend.views.vendor.cat_work', ['ru'=>'Работа с каталогом']) ?>
                    </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                    <i class="fa fa-video-camera bg-fk-success"></i>

                    <div class="timeline-item">
                        <h3 class="timeline-header"><?= Yii::t('message', 'frontend.views.vendor.main_cat_down', ['ru'=>'Загрузка главного каталога']) ?></h3>

                        <div class="timeline-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/ElzNEsKR0dA" frameborder="0" allowfullscreen=""></iframe>
                                </div>
                                <div class="col-md-9">
                                    <?= Yii::t('message', 'frontend.views.vendor.system_down', ['ru'=>'Загрузка Главного каталога поставщика при входе в систему']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- END timeline item -->
                
            </ul>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</section>