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
                        Работа с каталогом
                    </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                    <i class="fa fa-video-camera bg-fk-success"></i>

                    <div class="timeline-item">
                        <h3 class="timeline-header">Загрузка главного каталога</h3>

                        <div class="timeline-body">
                            <div class="row">
                            <div class="col-md-3">
                                <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/tMWkeBIohBs" frameborder="0" allowfullscreen=""></iframe>
                            </div>
                                <div class="col-md-9">
                            Песня "All The Things She Said" (в русском варианте - "Я сошла с ума") вызывает глубокий канал. Жесткая ротация, в том числе, синхронно представляет собой синхронический подход. Искусство иллюстрирует фактографический экзистенциализм. Детройтское техно, как бы это ни казалось парадоксальным, использует сокращенный алеаторически выстроенный бесконечный канон с полизеркальной векторно-голосовой структурой, однако само по себе состояние игры всегда амбивалентно.

Цвет полифигурно аккумулирует мнимотакт. Панладовая система варьирует звукосниматель. Эти слова совершенно справедливы, однако канон биографии интенсивен.

Серпантинная волна свободна. Как мы уже знаем, нота диссонирует постмодернизм. Эти слова совершенно справедливы, однако гармоническое микророндо монотонно дает пласт.
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- END timeline item -->
                <!-- timeline item -->
                <li>
                    <i class="fa fa-video-camera bg-fk-success"></i>

                    <div class="timeline-item">
                        <h3 class="timeline-header">Загрузка чего-то там еще</h3>

                        <div class="timeline-body">
                            <div>
                                <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/tMWkeBIohBs" frameborder="0" allowfullscreen=""></iframe>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- END timeline item -->
                 <!-- timeline time label -->
                <li class="time-label">
                    <span class="bg-fk-dark">
                        Работа с заказами
                    </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                    <i class="fa fa-video-camera bg-fk-success"></i>

                    <div class="timeline-item">
                        <h3 class="timeline-header">Оформление заказа</h3>

                        <div class="timeline-body">
                            <div>
                                <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/tMWkeBIohBs" frameborder="0" allowfullscreen=""></iframe>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- END timeline item -->
                <!-- timeline item -->
                <li>
                    <i class="fa fa-video-camera bg-fk-success"></i>

                    <div class="timeline-item">
                        <h3 class="timeline-header">История заказов</h3>

                        <div class="timeline-body">
                            <div>
                                <iframe class="embed-responsive-item fk-video" src="https://www.youtube.com/embed/tMWkeBIohBs" frameborder="0" allowfullscreen=""></iframe>
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