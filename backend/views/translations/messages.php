<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SmsSendSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Переводы';
$this->params['breadcrumbs'][] = $this->title;

\common\assets\SweetAlertAsset::register($this);

$this->registerCss("
    .grid-view td{white-space: normal;}
        ");
?>
<div class="row">
    <div class="col-md-2">
        <p>
            <?= Html::a('Создать перевод', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
    </div>
    <div class="col-md-10">
        <p>Инструкция: 1.) Переменные для переводов создаются по принципу - папка.подпапка.имя_файла.переменная 2.) Категории, используемые для перевода: app - общая, error - ошибка, message - сообщение 3.) Поиск работает как по переменным, так и по самим переводам на любом языке</p>
    </div>
</div>
<div class="sms-send-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'message',
                'label' => 'Переменная',
                'format' => 'raw',
                'value' => 'message',
                'headerOptions' => ['style' => 'width:300px'],
            ],
            [
                'header' => 'Перевод',
                'attribute' => 'translation',
                'format' => 'raw',
                'value' => function($data){
                    $message = '<table class="table table-bordered table-hover" style="margin-bottom: 0px;">';
                    foreach($data->messages as $m) {
                        $message .= '<tr>';
                        $message .= '<td width="36" >';
                        $message .= \common\widgets\LangSwitch::getFlag($m->language);
                        $message .= '</td>';
                        $message .= '<td class="translation" style="white-space: normal">';
                        $message .= (!empty($m->translation) ? $m->translation : Html::tag('span','Пусто...', ['style' => 'color:grey']));
                        $message .= '</td>';
                        $message .= '<td width="36" >';
                        $message .= '
                            <a href="#edit"
                               class="edit-message glyphicon glyphicon-edit"
                               data-id="'.$m->id.'"
                               data-language="'.$m->language.'"
                               data-translation="'.$m->translation.'"
                               data-message="'.$data->message.'"
                            ></a>
                        ';
                        $message .= '</td>';
                        $message .= '</tr>';
                    }
                    $message .= '</table>';
                    return $message;
                }
            ],
            [
                'attribute' => 'category',
                'label' => 'Категория',
                'headerOptions' => ['style' => 'width:100px'],
            ],
        ],
    ]); ?>
</div>

<?php
$js = "
    $(function(){
        $('.edit-message').click(function(){
            var link = $(this);
            swal({
              input: 'textarea',
              title: link.data('message') + ':' + link.data('language'),
              inputValue: link.data('translation'),
              showCancelButton: true,
              confirmButtonText: 'Сохранить',
              cancelButtonText: 'Отмена',
              showLoaderOnConfirm: true,
              preConfirm: (text) => {
                link.data('new_translation', text);
                return new Promise((resolve) => {
                  $.post('/sms/message-update/' + link.data('id'),{'translation':text, 'language': link.data('language')}, function(data){
                     resolve(data);   
                  });
                })
              },
              allowOutsideClick: false
            }).then((result) => {
              if (result.value.success === true) {
                link.data('translation', link.data('new_translation'));
                link.parent().parent().find('.translation').text(link.data('new_translation'));
                swal({
                  type: 'success',
                  title: 'Готово!',
                });
              } else {
                link.data('new_translation', null);
                swal({
                  type: 'error',
                  title: 'Ошибка!',
                })
              }
            })
        });
    });";

$this->registerJs($js);
?>