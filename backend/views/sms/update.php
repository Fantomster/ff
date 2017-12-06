<?php

use yii\helpers\Html;

$this->title = 'Изменить перевод ' . $model->message;
$this->params['breadcrumbs'][] = ['url' => '/sms/message', 'label' => 'Переводы смс сообщений'];
$this->params['breadcrumbs'][] = $this->title;

\common\assets\SweetAlertAsset::register($this);
?>

<div class="sms-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <table class="table">
        <?php foreach ($model->messages as $message): ?>
            <tr>
                <td><?= $message->language ?></td>
                <td class="translation" ><?= $message->translation ?></td>
                <td>
                    <a href="#edit"
                       class="edit-message glyphicon glyphicon-pencil"
                       data-id="<?= $message->id ?>"
                       data-language="<?= $message->language ?>"
                       data-translation="<?= $message->translation ?>"
                    ></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php
$js = "
    $(function(){
        $('.edit-message').click(function(){
            var link = $(this);
            swal({
              input: 'textarea',
              title: link.data('language'),
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
                link.parents('tr').find('.translation').text(link.data('new_translation'));
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