<?php

namespace backend\controllers;

use backend\models\TranslationSearch;
use common\models\Message;
use common\models\SourceMessage;
use Yii;
use common\models\Role;
use common\models\SmsSendSearch;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use common\components\AccessRule;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Response;

class TranslationsController extends SmsController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['change-status'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'ajax-balance', 'message', 'message-update', 'create', 'download-excel'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN
                        ],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'change-status' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список сообщений
     */
    public function actionMessage()
    {
        $searchModel = new TranslationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('messages', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }

    public function actionCreate()
    {
        $sourceMessage = new SourceMessage();
        $message = new Message();
        if ($sourceMessage->load(Yii::$app->request->post()) && $sourceMessage->save()) {
            $id = $sourceMessage->id;
            $post = Yii::$app->request->post();
            foreach ($post['Message']['translation'] as $lang=>$translation){
                $m = new Message();
                $m->id = $id;
                $m->language = $lang;
                $m->translation = $translation;
                $m->save();
            }
            return $this->redirect(['message']);
        } else {
            return $this->render('create', [
                'sourceMessage' => $sourceMessage,
                'message' => $message,
            ]);
        }
    }


    public function actionDownloadExcel(){
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator("MixCart")
            ->setLastModifiedBy("MixCart")
            ->setTitle("otchet_zakaz_" . date("d-m-Y-His"));

        $sheet = 0;
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);

        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Переменная');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Англ.');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Русс.');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Перевод');
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray(['font' => ['bold' => true]]);

        $arr = [];
        $sourceMessages = SourceMessage::find()->all();
        foreach ($sourceMessages as $sourceMessage){
            $messageEn = Message::findOne(['id'=>$sourceMessage->id, 'language'=>'en']);
            $messageRu = Message::findOne(['id'=>$sourceMessage->id, 'language'=>'ru']);
            $var = null;
            if($messageRu && (!$messageEn || $messageEn->translation=='')){
                $var = $messageRu->translation;
            }else{
                $var = $messageEn->translation ?? null;
            }
            if($var){
                $arr[$var]['var'] = $sourceMessage->message;
                $arr[$var]['en'] = $messageEn->translation ?? '';
                $arr[$var]['ru'] = $messageRu->translation ?? '';
            }
        }
        ksort($arr);
        $i = 2;
        foreach ($arr as $item){
            $objPHPExcel->getActiveSheet()->setCellValue("A$i", $item['var']);
            $objPHPExcel->getActiveSheet()->setCellValue("B$i", $item['en']);
            $objPHPExcel->getActiveSheet()->setCellValue("C$i", $item['ru']);
            $i++;
        }

        header('Content-Type: application/vnd.ms-excel');
        $filename = "translations_" . date("d-m-Y-His") . ".xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

}
