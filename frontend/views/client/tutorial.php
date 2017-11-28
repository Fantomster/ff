<?php

use yii\widgets\Breadcrumbs;
$this->title = Yii::t('message', 'frontend.views.client.tutorial.video', ['ru'=>'Обучающие видео']);
?>

<section class="content-header">
    <h1>
        <?= Yii::t('message', 'frontend.views.client.tutorial.video_two', ['ru'=>'Обучающие видео']) ?>
        <small><?= Yii::t('message', 'frontend.views.client.tutorial.examples', ['ru'=>'Примеры работы с системой MixCart']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            Yii::t('message', 'frontend.views.client.tutorial.video_three', ['ru'=>'Обучающие видео']),
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
                        <?= Yii::t('message', 'frontend.views.client.tutorial.custom_info', ['ru'=>'Общая информация']) ?>
                    </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                    <i class="fa fa-video-camera bg-fk-success"></i>

                    <div class="timeline-item">
                        <h3 class="timeline-header"><?= Yii::t('message', 'frontend.views.client.tutorial.describing', ['ru'=>'Описание возможностей системы MixCart']) ?></h3>

                        <div class="timeline-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/fIeCESIFID4" frameborder="0" allowfullscreen=""></iframe>
                                </div>
                                <div class="col-md-9">
                                    <?= Yii::t('message', 'frontend.views.client.tutorial.describing_two', ['ru'=>'Описание возможностей системы MixCarts']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- END timeline item -->
                
                <!-- timeline time label -->
                <li class="time-label">
                    <span class="bg-fk-dark">
                        <?= Yii::t('message', 'frontend.views.client.tutorial.vendors_job', ['ru'=>'Работа с поставщиками']) ?>
                    </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                    <i class="fa fa-video-camera bg-fk-success"></i>

                    <div class="timeline-item">
                        <h3 class="timeline-header"><?= Yii::t('message', 'frontend.views.client.tutorial.my_vendors', ['ru'=>'Мои поставщики']) ?></h3>

                        <div class="timeline-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/Cj85FCJOZbQ" frameborder="0" allowfullscreen=""></iframe>
                                </div>
                                <div class="col-md-9">
                                    <?= Yii::t('message', 'frontend.views.client.tutorial.how_to', ['ru'=>'Как начать работать в системе MixCart, или Загрузка каталогов поставщиков']) ?>
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