<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/12/2018
 * Time: 4:04 PM
 */

namespace console\modules\daemons\components;

use api\common\models\iiko\iikoDic;
use api\common\models\iiko\iikoDictype;
use frontend\modules\clientintegr\modules\iiko\helpers\iikoApi;
use yii\web\BadRequestHttpException;

class IikoSyncConsumer extends AbstractConsumer
{
    /**@var $orgId int*/
    public $orgId;

    public function __construct($orgId = null)
    {
        $this->orgId = $orgId;
    }

    /**
     * Запуск синфронизации определенного типа
     * @param $type
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function run($type)
    {
        /**
         * @var $transaction Transaction
         */
        $model = iikoDictype::findOne($type);

        if (empty($model)) {
            throw new BadRequestHttpException('Not found type ' . $type);
        }

        if (empty($model->method)) {
            throw new BadRequestHttpException('Empty [iko_dictype.method] in DB');
        }

        if (method_exists($this, $model->method) === true) {
            try {
                //Пробуем пролезть в iko
                if (!iikoApi::getInstance($this->orgId)->auth()) {
                    throw new BadRequestHttpException('Не удалось авторизоваться в iiko - Office');
                }
                //Синхронизируем нужное нам и
                //ответ получим, сколько записей у нас в боевом состоянии
                $count = $this->{$model->method}();
                //Убиваем сессию, а то закончатся на сервере iiko
                iikoApi::getInstance($this->orgId)->logout();
                //Обновляем данные
                $dicModel = iikoDic::findOne(['dictype_id' => $model->id, 'org_id' => $this->orgId]);
                if (!$dicModel->updateSuccessSync($count)) {
                    throw new BadRequestHttpException($dicModel->getFirstErrors());
                }
                //Сохраняем данные
                return ['success' => true];
            } catch (\Exception $e) {
                iikoApi::getInstance($this->orgId)->logout();
                iikoDic::errorSync($model->id);
                throw $e;
            }
        } else {
            throw new BadRequestHttpException('Not found method [iikoSync->' . $model->method . '()]');
        }
    }

}