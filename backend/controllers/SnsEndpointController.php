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
class SnsEndpointController extends \yii\rest\Controller {

    //put your code here
    public function actionBounce() {
        //
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

        try {
            $validator->validate($message);
        } catch (SnsException $ex) {
            http_response_code(404);
            Yii::error($ex->getMessage());
            die();
        }
        // Check the type of the message and handle the subscription.
        if ($message->get('Type') === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            file_get_contents($message['SubscribeURL']);
        }
    }

}
