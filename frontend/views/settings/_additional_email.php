<?php

use yii\helpers\Html;

$labels = new \common\models\AdditionalEmail();
$labels = $labels->attributeLabels();

if ($user->organization->type_id == \common\models\Organization::TYPE_SUPPLIER) {
    $labels['request_accept'] = Yii::t('app', 'frontend.views.settings.executor', ['ru' => 'Исполнитель в заявке']);
}

if ($user->organization->type_id == \common\models\Organization::TYPE_RESTAURANT) {
    $labels['request_accept'] = Yii::t('app', 'frontend.views.settings.new_response', ['ru' => 'Новый отклик на заявку']);
}
?>

<?php

//Кнопка добавления, для встраивания в шаблон
$buttonAdd = Html::tag('div', Html::button('<i class="glyphicon glyphicon-edit"></i> ' . Yii::t('message', 'frontend.views.settings.add', ['ru' => 'Добавить']), [
                    'class' => 'btn btn-success btn-xs',
                    'onclick' => 'addEmail()',
                    'data-pjax' => 1
                        ]
                ), ['class' => 'text-right']
);

$columns = [
    [
        'attribute' => 'email',
        'header' => $labels['email'],
        'format' => 'raw',
        'value' => function ($data) {
            $options = $data->confirmed ? [] : [
                'style' => 'font-style:italic;',
                'data-toggle' => 'tooltip',
                'data-placement' => 'bottom',
                'title' => Yii::t('app', 'frontend.views.settings.email_not_confirmed', ['ru' => 'Почта не подтверждена'])];
            return Html::a($data->email, 'mailto:' . $data->email, $options);
        },
    ],
    //'email:email',
    [
        'header' => $labels['order_created'],
        'class' => 'yii\grid\CheckboxColumn',
        'checkboxOptions' => function ($model) {
            return ['checked' => $model->order_created, 'data-id' => $model->id, 'data-column' => 'order_created', 'disabled' => !$model->confirmed];
        }
    ],
    [
        'header' => $labels['order_canceled'],
        'class' => 'yii\grid\CheckboxColumn',
        'checkboxOptions' => function ($model) {
            return ['checked' => $model->order_canceled, 'data-id' => $model->id, 'data-column' => 'order_canceled', 'disabled' => !$model->confirmed];
        }
    ],
    [
        'header' => $labels['order_changed'],
        'class' => 'yii\grid\CheckboxColumn',
        'checkboxOptions' => function ($model) {
            return ['checked' => $model->order_changed, 'data-id' => $model->id, 'data-column' => 'order_changed', 'disabled' => !$model->confirmed];
        }
    ],
    [
        'header' => $labels['order_processing'],
        'class' => 'yii\grid\CheckboxColumn',
        'checkboxOptions' => function ($model) {
            return ['checked' => $model->order_processing, 'data-id' => $model->id, 'data-column' => 'order_processing', 'disabled' => !$model->confirmed];
        }
    ],
    [
        'header' => $labels['order_done'],
        'class' => 'yii\grid\CheckboxColumn',
        'checkboxOptions' => function ($model) {
            return ['checked' => $model->order_done, 'data-id' => $model->id, 'data-column' => 'order_done', 'disabled' => !$model->confirmed];
        }
    ],
    [
        'header' => $labels['request_accept'],
        'class' => 'yii\grid\CheckboxColumn',
        'checkboxOptions' => function ($model) {
            return ['checked' => $model->request_accept, 'data-id' => $model->id, 'data-column' => 'request_accept', 'disabled' => !$model->confirmed];
        }
    ],
];
if (isset($mercLicense)) {
    $columns[] = [
        'header' => $labels['merc_vsd'],
        'class' => 'yii\grid\CheckboxColumn',
        'checkboxOptions' => function ($model) {
            return ['checked' => $model->merc_vsd, 'data-id' => $model->id, 'data-column' => 'merc_vsd', 'disabled' => !$model->confirmed];
        }
    ];
    $columns[] = [
        'header' => $labels['merc_stock_expiry'],
        'class' => 'yii\grid\CheckboxColumn',
        'checkboxOptions' => function ($model) {
            return ['checked' => $model->merc_stock_expiry, 'data-id' => $model->id, 'data-column' => 'merc_stock_expiry', 'disabled' => !$model->confirmed];
        }
    ];
}
$columns[] = [
    'class' => '\kartik\grid\ActionColumn',
    'template' => '{delete}',
    'header' => '',
    'buttons' => [
        'delete' => function ($model) {
            return Html::a('<i class="glyphicon glyphicon-trash"></i>', '#delete', [
                        'data-pjax' => 1,
                        'onclick' => 'deleteEmail(' . $model . ');return false;'
            ]);
        }
    ],
    'urlCreator' => function ($action, $model) {
        if ($action === 'delete') {
            return $model->id;
        }
    }
];
?>

<?php

yii\widgets\Pjax::begin(['id' => 'emails-pjax-container']);


echo \kartik\grid\GridView::widget([
    'dataProvider' => $additional_email,
    'emptyText' => Yii::t('app', 'No results found'),
    'columns' => $columns,
    'panelHeadingTemplate' => '
        <div class="pull-right">' . $buttonAdd . '</div>
        <h3 class="panel-title">{heading}</h3>
        <div class="clearfix"></div>
    ',
    'panel' => [
        'type' => kartik\grid\GridView::TYPE_DEFAULT,
        'heading' => Yii::t('message', 'frontend.views.settings.additional_email', ['ru' => 'Дополнительные email']),
        'after' => false,
        'footer' => false,
        'before' => false,
    ],
]);

yii\widgets\Pjax::end();
?>

<?php

$this->registerJs("

addEmail= function () {
    swal({
      title: '" . Yii::t('message', 'frontend.views.settings.insert_email', ['ru' => 'Введите email']) . "',
      input: 'email',
              inputValidator: (value) => {
            return !value && '" . Yii::t('app', 'Неправильный формат email') . "'
        },
      showCancelButton: true,
      confirmButtonText: '" . Yii::t('message', 'frontend.views.settings.save_three', ['ru' => 'Сохранить']) . "',
      cancelButtonText: '" . Yii::t('message', 'frontend.views.settings.cancel', ['ru' => 'Отменить']) . "',
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
       
      if(result.dismiss){
        swal.hide();
      } else {
        swal({
            type: 'success',
            title: '" . Yii::t('message', 'frontend.views.settings.ready', ['ru' => 'Готово']) . "',
            html: '" . Yii::t('app', 'frontend.views.settings.new_email_confirm', ['ru' => 'На новый email выслано письмо для подтверждения:']) . ": ' + result.value
          }).catch(swal.noop);
      }
    }).catch(swal.noop);
};

deleteEmail= function (id) {
    var id = id;
    swal({
      title: '" . Yii::t('message', 'frontend.views.settings.delete_email', ['ru' => 'Вы уверены что хотите удалить email']) . "?',
      type: 'question',
      showCancelButton: true,
      confirmButtonText: '" . Yii::t('message', 'frontend.views.settings.delete', ['ru' => 'Удалить']) . "',
      cancelButtonText: '" . Yii::t('message', 'frontend.views.settings.cancel_two', ['ru' => 'Отменить']) . "',
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
    }).then(function (result) {
        if(result.dismiss){
            swal.hide();
        } else {
          swal({
            type: 'success',
            title: '" . Yii::t('message', 'frontend.views.settings.ready_two', ['ru' => 'Готово']) . "',
            html: '" . Yii::t('message', 'frontend.views.settings.email_deleted', ['ru' => 'Email удален из списка получаетелей']) . "'
          }).catch(swal.noop)
        }
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
        var text = '" . Yii::t('app', 'frontend.views.settings.text', ['ru' => 'Сообщите текст ошибки в отдел технической поддержки: ']) . "' + error;
        
        if(error == 'Forbidden') {
            text = '" . Yii::t('error', 'frontend.views.settings.forbidden', ['ru' => 'Доступ запрещен.']) . "';
        }
        
        swal({
          position: 'center',
          type: 'error',
          title: '" . Yii::t('error', 'frontend.views.settings.error', ['ru' => 'Ошибка']) . "',
          text: text,
          showConfirmButton: true
        });
    });
});

");
?>
