<?php

use yii\helpers\Html;


/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'R-keeper API White Server (version 1)';
// $this->params['breadcrumbs'][] = $this->title;

        echo "Local time:<strong> ".date('Y-m-d H:i:s',time())."</strong><br>";
        echo "GMT time: <strong>".gmdate("Y-m-d H:i:s")."</strong><br><br>";
        
echo Html::a('Home', ['/v1/restor'], ['class'=>'btn btn-primary']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('Settings', ['/v1/restor/settings'], ['class'=>'btn btn-primary']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('Auth', ['/v1/restor/auth'], ['class'=>'btn btn-primary']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('Single requests', ['/v1/restor/srequest'], ['class'=>'btn btn-primary']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('Postponed requests', ['/v1/restor/prequest'], ['class'=>'btn btn-primary']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('History', ['/v1/restor/history'], ['class'=>'btn btn-primary']);
echo "&nbsp; &nbsp; &nbsp; ";
echo Html::a('Error codes', ['/v1/restor/serror'], ['class'=>'btn btn-primary']);

?>