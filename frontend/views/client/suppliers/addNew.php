<?php
use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\url;
use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use common\models\Category;
kartik\select2\Select2Asset::register($this);
?>
<?php
$this->title = 'Добавить поставщика';
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss('
.Handsontable_table{position: relative;width: 100%;height:400px;overflow: hidden;}
.hide{dosplay:none}
');		
?>
<section class="content-header">
    <h1>
        <i class="fa fa-users"></i>  Добавить поставщика
        <small>Находите и добавляйте в Вашу систему новых поставщиков</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Добавить поставщика',
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-header with-border">
            <div class="text-center">
                <ul class="nav fk-tab nav-tabs pull-left">
                    <?='<li class="active">'.Html::a('E-mail поставщика <i class="fa fa-fw fa-hand-o-right"></i>',['vendor/step-1'],['class'=>'btn btn-default']).'</li>';
                    ?>
                    <?='<li class="disabled">'.Html::a('Информация об организации').'</li>';
                    ?>
                    <?='<li class="disabled">'.Html::a('Добавить товары').'</li>';
                    ?>
                </ul>
                <ul class="fk-prev-next pull-right">
                  <?='<li class="fk-next">'.Html::a('Продолжить',['#'],['class' => 'step-2']).'</li>';?>
                </ul>
            </div>
        </div>
        <div class="box-body">
            <div class="callout callout-fk-info">
                <h4>ШАГ 1</h4>
                <p>Введите почтовый адрес Вашего нового поставщика</p>
            </div>
            <div class="col-md-6">
            <?php $form = ActiveForm::begin(['id'=>'new-supplier']); ?>
                <?= $form->field($user, 'email')->textInput(['placeholder' => 'Введите email'])->label(false)?>
            <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</section>
$this->registerJs('

');