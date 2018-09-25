<?php

/**
 * Class ServiceIiko
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

use api_web\classes\RabbitWebApi;
use yii\web\ServerErrorHttpException;

class ServiceIiko extends AbstractSyncFactory
{
    public $queueName = null;

    public $dictionaryAvailable = [
        self::DICTIONARY_AGENT,
        self::DICTIONARY_PRODUCT,
        self::DICTIONARY_STORE,
    ];

    public function sendRequest()
    {
        if(empty($this->queueName)) {
            throw new ServerErrorHttpException('Empty field $queueName in class ' . get_class($this), 500);
        }

        (new RabbitWebApi())->addToQueue([
            "queue" => $this->queueName,
            "org_id" => $this->user->organization->id
        ]);

        return ['success' => true];
    }
}