<?php

use yii\helpers\Html;

/*

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\widgets\Pjax;
 * 
 * 
 */

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '1C suppliers API (version 1)';
// $this->params['breadcrumbs'][] = $this->title;

        echo "Local time:<strong> ".date('Y-m-d H:i:s',time())."</strong><br>";
        echo "GMT time: <strong>".gmdate("Y-m-d H:i:s")."</strong><br><br>";
        
echo Html::a('Auth', ['/v1/restor/sendlogin'], ['class'=>'btn btn-primary', 'target'=>'_blank']);
echo Html::a('Auth', ['/v1/restor/getgoods'], ['class'=>'btn btn-primary', 'target'=>'_blank']);




?>




