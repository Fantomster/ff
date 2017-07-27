<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use Aws\Sns\MessageValidator\Message;
use Aws\Sns\MessageValidator\MessageValidator;
use Aws\Sns\Exception\SnsException;

/**
 * Description of SnsEndpointController
 *
 * @author elbabuino
 */
class SnsEndpointController extends Controller {
    //put your code here
    public function actionBounce() {
        //
        $message = MessageValidator\Message::fromRawPostData();
        $validator 
    }
}
