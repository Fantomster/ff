<?php
use yii\helpers\Html;
use yii\helpers\Url;

?>
<?=
Html::a(
        '<i class="fa fa-list-alt"></i> <span class="text-label">Скачать шаблон (XLS)</span>', Url::to('@web/upload/template.xlsx'), ['class' => 'btn btn-default', 'style' => ['margin-right' => '10px;']]
)
?>
