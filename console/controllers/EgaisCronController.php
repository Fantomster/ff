<?php

namespace console\controllers;

use api_web\modules\integration\modules\egais\models\EgaisCronHelper;
use yii\console\Controller;

class EgaisCronController extends Controller
{
    /**
     * Проверка на наличие тикетов и успешной постановки на баланс
     *
     * @throws \api_web\exceptions\ValidationException
     */
    public function actionCheckActWriteOn(): void
    {
        (new EgaisCronHelper())->checkActWriteOn();
    }

    /**
     * Проверка документов о запросе продуктов на балансе
     *
     * @throws \api_web\exceptions\ValidationException
     */
    public function actionGoodsOnBalance(): void
    {
        (new EgaisCronHelper())->saveGoodsOnBalance();
    }
}