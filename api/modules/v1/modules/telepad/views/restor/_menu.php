<?php

use yii\helpers\Html;


/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'R-keeper API White Server (version 1)';
// $this->params['breadcrumbs'][] = $this->title;

        echo "Local time:<strong> ".date('Y-m-d H:i:s',time())."</strong><br>";
        echo "GMT time: <strong>".gmdate("Y-m-d H:i:s")."</strong><br><br>";
        
echo Html::a('Home', ['index'], ['class'=>'btn btn-primary']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('Settings', ['/v1/restor/settings'], ['class'=>'btn btn-primary']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('Auth', ['restor/sendlogin'], ['class'=>'btn btn-primary', 'target'=>'_blank']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('Get Goods', ['/v1/restor/getgoods'], ['class'=>'btn btn-primary', 'target'=>'_blank']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('Settings', ['/v1/restor/settings'], ['class'=>'btn btn-primary']);

?>