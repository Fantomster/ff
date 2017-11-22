<?php

use yii\helpers\Html;

$labels = new \common\models\AdditionalEmail();
$labels = $labels->attributeLabels();
?>

<?php
//Кнопка добавления, для встраивания в шаблон
$buttonAdd = Html::tag('div',
    Html::button('<i class="glyphicon glyphicon-edit"></i> ' . Yii::t('app', 'Добавить'),
        [
            'class' => 'btn btn-success btn-xs',
            'onclick' => 'addEmail()',
            'data-pjax' => 1
        ]
    ),
    ['class' => 'text-right']
);
?>

<?php

yii\widgets\Pjax::begin(['id' => 'emails-pjax-container']);

echo \kartik\grid\GridView::widget([
    'dataProvider' => $additional_email,
    'columns' => [
        'email:email',
        [
            'header' => $labels['order_created'],
            'class' => 'yii\grid\CheckboxColumn',
            'checkboxOptions' => function ($model) {
                return ['checked' => $model->order_created, 'data-id' => $model->id, 'data-column' => 'order_created'];
            }
        ],
        [
            'header' => $labels['order_canceled'],
            'class' => 'yii\grid\CheckboxColumn',
            'checkboxOptions' => function ($model) {
                return ['checked' => $model->order_canceled, 'data-id' => $model->id, 'data-column' => 'order_canceled'];
            }
        ],
        [
            'header' => $labels['order_changed'],
            'class' => 'yii\grid\CheckboxColumn',
            'checkboxOptions' => function ($model) {
                return ['checked' => $model->order_changed, 'data-id' => $model->id, 'data-column' => 'order_changed'];
            }
        ],
        [
            'header' => $labels['order_processing'],
            'class' => 'yii\grid\CheckboxColumn',
            'checkboxOptions' => function ($model) {
                return ['checked' => $model->order_processing, 'data-id' => $model->id, 'data-column' => 'order_processing'];
            }
        ],
        [
            'header' => $labels['order_done'],
            'class' => 'yii\grid\CheckboxColumn',
            'checkboxOptions' => function ($model) {
                return ['checked' => $model->order_done, 'data-id' => $model->id, 'data-column' => 'order_done'];
            }
        ],
        [
            'class' => '\kartik\grid\ActionColumn',
            'template' => '{delete}',
            'header' => '',
            'buttons' => [
                'delete' => function ($model) {
                    return Html::a('<i class="glyphicon glyphicon-trash"></i>', '#delete', [
                        'data-pjax' => 1,
                        'onclick' => 'deleteEmail(' . $model . ')'
                    ]);
                }
            ],
            'urlCreator' => function ($action, $model) {
                if ($action === 'delete') {
                    return $model->id;
                }
            }
        ],
    ],
    'panelHeadingTemplate' => '
        <div class="pull-right">' . $buttonAdd . '</div>
        <h3 class="panel-title">{heading}</h3>
        <div class="clearfix"></div>
    ',
    'panel' => [
        'type' => kartik\grid\GridView::TYPE_DEFAULT,
        'heading' => \Yii::t('app', 'Дополнительные email'),
        'after' => false,
        'footer' => false,
        'before' => false,
    ]
]);

yii\widgets\Pjax::end();

?>

<?php
$this->registerJs("

addEmail= function () {
    swal({
      title: '" . Yii::t('app', 'Введите email') . "',
      input: 'email',
      showCancelButton: true,
      confirmButtonText: '" . Yii::t('app', 'Сохранить') . "',
      cancelButtonText: '" . Yii::t('app', 'Отменить') . "',
      showLoaderOnConfirm: true,
      preConfirm: function (email) {
        return new Promise(function (resolve, reject) {
          $.post('" . Yii::$app->urlManager->createUrl(["/settings/ajax-add-email"]) . "', {'email':email})
          .done(function(){
            resolve();
            $.pjax.reload('#emails-pjax-container');
          })
          .fail(function(xhr, status, error) {
            reject(error);
          });
        })
      },
      allowOutsideClick: false
    }).then(function (result) {
      swal({
        type: 'success',
        title: '" . Yii::t('app', 'Готово') . "',
        html: '" . Yii::t('app', 'Добавлен новый email') . ": ' + result.value,
        timer: 1500
      }).catch(swal.noop);
    }).catch(swal.noop);
};

deleteEmail= function (id) {
    var id = id;
    swal({
      title: '" . Yii::t('app', 'Вы уверены что хотите удалить email') . "?',
      type: 'question',
      showCancelButton: true,
      confirmButtonText: '" . Yii::t('app', 'Удалить') . "',
      cancelButtonText: '" . Yii::t('app', 'Отменить') . "',
      showLoaderOnConfirm: true,
      preConfirm: function () {
        return new Promise(function (resolve, reject) {
          $.post('" . Yii::$app->urlManager->createUrl(["/settings/ajax-delete-email"]) . "/' + id)
            .done(function(){ 
                resolve();
                $.pjax.reload('#emails-pjax-container');
             })
            .fail(function(xhr, status, error) {
                reject(error);
            });
        })
      },
    }).then(function () {
      swal({
        type: 'success',
        title: '" . Yii::t('app', 'Готово') . "',
        html: '" . Yii::t('app', 'Email удален из списка получаетелей') . "',
        timer: 1500
      }).catch(swal.noop)
    }).catch(swal.noop);
};

$(document).on('change', '#emails-pjax-container input[type=\"checkbox\"]', function(){
    var tih_s = $(this);
    var params = {};
    params.attribute = $(this).data('column');
    params.id = $(this).data('id');
    params.value = $(this).is(':checked') ? 1 : 0;
    $.post('" . Yii::$app->urlManager->createUrl(["/settings/ajax-change-email-notification"]) . "', params)
    .fail(function(xhr, status, error) {
        $(tih_s).prop('checked', (params.value == 1 ? false : true));
        var text = '" . Yii::t('app', 'Сообщите текст ошибки в отдел технической поддержки: ') . "' + error;
        
        if(error == 'Forbidden') {
            text = '" . Yii::t('app', 'Доступ запрещен.') . "';
        }
        
        swal({
          position: 'center',
          type: 'error',
          title: '" . Yii::t('app', 'Ошибка') . "',
          text: text,
          showConfirmButton: true
        });
    });
});

");
?>
