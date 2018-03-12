<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use yii\widgets\Pjax;
frontend\assets\AppAsset::register($this);
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\LoginForm $model
 */
$this->title = Yii::t('message', 'frontend.views.user.default.enter_register_three', ['ru'=>"Вход / регистрация"]);
$redirect = empty($returnUrl) ? Url::to(['/site/index']) : $returnUrl;
$changeNetworkUrl = Url::to(['/user/change']);
$js = <<<JS
    $(document).on("click",".change-net-org", function(e){
    e.preventDefault();
    var id = $(this).attr('data-id'); 
    $.get(
        "$changeNetworkUrl",
        {id : id}
    ).done(function(result) {
        console.log(result);
        if (result) {
            document.location = "$redirect";
        }
    });
});
JS;
$this->registerJs($js, \yii\web\View::POS_READY);

$this->registerCss('
.h5, h4, h3, h2, h1{
     font-family: \'Circe-Bold\';  
     letter-spacing: 0.05em;
    }
a, span, div, p{
     font-family: \'Circe-Regular\';  
     letter-spacing: 0.03em;
     font-size:14px;
    }
.section{
    background: url(/images/tmp_file/flowers.png) bottom center no-repeat;
    height: 100%;
    position: absolute;
    width: 100%;
}
.block {
    position: relative;
    border-radius: 4px;
    background: #fff;
    margin-top: 20%;
    width: 100%;
    padding:25px;
    -webkit-box-shadow: 0px -1px 12px -3px rgba(112,112,112,1);
-moz-box-shadow: 0px -1px 12px -3px rgba(112,112,112,1);
box-shadow: 0px -1px 12px -3px rgba(112,112,112,1);
}  
.business-title{
margin-top:10px;
text-align:center;
    font-family:\'Circe-Bold\';
    font-size:24px;
}
.business-p-title{
text-align:center;
    font-family:\'Circe-Regular\';
    font-size:14px;
}
a.btn-continue{
    font-size: 24px;
    font-family: \'Circe-Bold\';
    text-align: center;
    margin-top: 20px;
    display: block;
    width: 100%;
    padding: 20px 14px 14px 14px;
    background: #66BC75;
    color: #fff;
    border-radius: 50px;
}
a.btn-continue:hover{
    background: #69C178;
    color: #fff;
}
.pagination>li>a {
    background: #fafafa;
    color: #666;
}
.pagination > li > a, .pagination > li > span {
    color: #666;
    background-color: #fafafa;
    border: 1px solid #ссс;
}
.pagination > li > a:hover, .pagination > li > span:hover, .pagination > li > a:focus, .pagination > li > span:focus {
    color: #666;
    background-color: #eee;
    border-color: #ddd;
}
@media (max-width: 600px){
.table {
    overflow-x: scroll;
    display: table;
}
}
@media (max-width: 480px){
.table a{float:none !important;padding: 0 !important;}
.kv-table-wrap tr > td{border:0 !important;}   
.kv-table-wrap tr > td:last-child{border-bottom: 1px solid #ccc !important;;}
.kv-table-wrap tr:last-child > td:last-child{border-bottom: 0 !important;} 
.kv-table-wrap th, .kv-table-wrap td {width: inherit !important; }
}
');
?>
<?php
$grid = [
    [
    'label'=>false,
    'format' => 'raw',
    'value'=>function ($data) {
    $rel = \common\models\RelationUserOrganization::findOne(['organization_id'=>$data['id'], 'user_id'=>Yii::$app->user->id]);
    if($rel){
        $role = \common\models\Role::findOne(['id'=>$rel->role_id]);
        $roleName = " (" . $role->name . ") ";
    }else{
        $roleName = '';
    }
            if($data['type_id']==\common\models\Organization::TYPE_RESTAURANT){
            return "<span style='color: #cacaca;'>" . Yii::t('message', 'frontend.views.user.default.buyer_three', ['ru'=>'Закупщик']) . " </span> <span style='color: #cacaca;'> $roleName </span><br><span style='color:#84bf76'><b>" . $data['name'] . "</b></span>";
            }
        return "<span style='color: #cacaca;'>" . Yii::t('message', 'frontend.views.user.default.vendor_three', ['ru'=>'Поставщик']) . " </span> <span style='color: #cacaca;'> $roleName </span><br><span style='color:#84bf76'><b>" . $data['name'] . "</b></span>";
        },
    ],
    [
    'label'=>false,
    'format' => 'raw',
    'value'=>function ($data) {
            if($data['id'] == \common\models\User::findIdentity(Yii::$app->user->id)->organization_id){

    return  Html::a('<i class="fa fa-toggle-on"  style="margin-top:5px;"></i>', '#', [
                'class' => 'disabled pull-right',
                'style' => 'font-size:26px;color:#84bf76;padding-right:25px;'
            ]);}
    return  Html::a('<i class="fa fa-toggle-on" style="transform: scale(-1, 1);margin-top:5px;"></i>', '#', [
                'class' => 'change-net-org pull-right',
                'style' => 'font-size:26px;color:#ccc;padding-right:25px;',
                'data' => ['id' => $data['id']],
            ]);
        },
    ],
];
?>

<section class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-offset-3 col-md-6">
                <div class="block">
                    <h5 class="business-title"><?= Yii::t('message', 'frontend.views.user.default.choose_profile', ['ru'=>'Выберите бизнес-профиль']) ?></h5>
                    <p class="business-p-title"><?= Yii::t('message', 'frontend.views.user.default.choose_list', ['ru'=>'Выберите из имеющихся для доступа в Ваш бизнес-профиль']) ?></p>
                    <div class="row">
                        <div class="col-md-12">
                            <?php Pjax::begin(['id' => 'pjax-network-list', 'enablePushState' => false,'timeout' => 10000])?>
                            <?=GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    'filterPosition' => false,
                                    'columns' => $grid,
                                    'options' => [],
                                    'tableOptions' => ['class' => 'table'],
                                    'bordered' => false,
                                    'striped' => false,
                                    'summary' => false,
                                    'condensed' => false,
                                    'showHeader'=>false,
                                    'resizableColumns'=>false,

                            ]);
                            ?>
                            <?php Pjax::end(); ?>
                        </div>
                    </div>
                    <?=Html::a(Yii::t('message', 'frontend.views.user.default.continue', ['ru'=>'ПРОДОЛЖИТЬ']), $redirect, [
                        'class' => 'btn-continue',
                    ]);?>
                </div>
            </div>
        </div>
    </div>
</section>

