<?php

use yii\widgets\Breadcrumbs;
?>

<section class="content-header">
    <h1>
        Обучающие видео
        <small>Примеры работы с системой f-keeper</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Обучающие видео',
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
                        Общая информация 
                    </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                    <i class="fa fa-video-camera bg-fk-success"></i>

                    <div class="timeline-item">
                        <h3 class="timeline-header">Описание возможностей системы f-keeper</h3>

                        <div class="timeline-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/fIeCESIFID4" frameborder="0" allowfullscreen=""></iframe>
                                </div>
                                <div class="col-md-9">
                                    Описание возможностей системы f-keepers
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- END timeline item -->
                
                <!-- timeline time label -->
                <li class="time-label">
                    <span class="bg-fk-dark">
                        Работа с поставщиками
                    </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                    <i class="fa fa-video-camera bg-fk-success"></i>

                    <div class="timeline-item">
                        <h3 class="timeline-header">Мои поставщики</h3>

                        <div class="timeline-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/Cj85FCJOZbQ" frameborder="0" allowfullscreen=""></iframe>
                                </div>
                                <div class="col-md-9">
                                    Как начать работать в системе f-keeper, или Загрузка каталогов поставщиков 
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