<?php

namespace console\controllers;

use api_web\modules\integration\modules\egais\helpers\EgaisHelper;
use yii\console\Controller;

class EgaisCronController extends Controller
{
    /**
     * Проверка на наличие тикетов и успешной постановки на баланс
     * @throws \api_web\exceptions\ValidationException
     */
    public function actionCheckActWriteOn(): void
    {
        (new EgaisHelper())->checkActWriteOn();
    }

    /**
     * Проверка документов о запросе продуктов на балансе
     * @throws \api_web\exceptions\ValidationException
     */
    public function actionGoodsOnBalance(): void
    {
        (new EgaisHelper())->saveGoodsOnBalance();
    }
}