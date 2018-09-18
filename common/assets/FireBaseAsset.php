<?php
 
namespace common\assets;
 
use yii\base\Widget;

class FireBaseAsset extends Widget
{
   public function init()
   {
       parent::init();

       $this->getView()->registerJsFile('https://www.gstatic.com/firebasejs/5.5.0/firebase.js', ['position' => \yii\web\View::POS_HEAD]);

       $databaseUrl= \Yii::$app->params['fireBase']['DEFAULT_URL'];
       $authDomain = \Yii::$app->params['fireBase']['authDomain'];
       $apiKey = \Yii::$app->params['fireBase']['apiKey'];
       $projectId = \Yii::$app->params['fireBase']['projectId'];
       $storageBucket = \Yii::$app->params['fireBase']['storageBucket'];
       $messagingSenderId = \Yii::$app->params['fireBase']['messagingSenderId'];

       $customJs = <<< JS
        // Initialize Firebase
        var config = {
            apiKey: "$apiKey",
            authDomain: "$authDomain",
            databaseURL: "$databaseUrl",
            projectId: "$projectId",
            storageBucket: "$storageBucket",
            messagingSenderId: "$messagingSenderId"
        };
        firebase.initializeApp(config);
JS;
$this->getView()->registerJs($customJs, \yii\web\View::POS_HEAD);
   }
}