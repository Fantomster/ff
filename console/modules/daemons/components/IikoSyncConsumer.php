<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 9/12/2018
 * Time: 4:04 PM
 */

namespace console\modules\daemons\components;

use api\common\models\RabbitQueues;
use api_web\components\Registry;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use api_web\helpers\iikoApi;
use yii\web\BadRequestHttpException;

/**
 * Class IikoSyncConsumer
 *
 * @package console\modules\daemons\components
 */
class IikoSyncConsumer extends AbstractConsumer
{
    /**@property int|null $orgId Id организации */
    public $orgId;
    /**@var integer */
    const SERVICE_ID = Registry::IIKO_SERVICE_ID;

    /**
     * @var
     */
    public $type;

    /**
     * Description
     *
     * @var iikoApi
     */
    public $iikoApi;

    /**
     * IikoSyncConsumer constructor.
     *
     * @param null $orgId
     */
    public function __construct($orgId = null)
    {
        $this->orgId = $orgId;
        $this->iikoApi = iikoApi::getInstance($this->orgId);
    }

    /**
     * Запуск синхронизации определенного типа
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function run()
    {
        $model = OuterDictionary::findOne(['name' => $this->type, 'service_id' => self::SERVICE_ID]);

        $dictionary = OrganizationDictionary::findOne([
            'org_id'       => $this->orgId,
            'outer_dic_id' => $model->id
        ]);

        if (empty($dictionary)) {
            $dictionary = new OrganizationDictionary([
                'org_id'       => $this->orgId,
                'outer_dic_id' => $model->id,
                'status_id'    => OrganizationDictionary::STATUS_DISABLED
            ]);
        }

        if (empty($model)) {
            throw new BadRequestHttpException('Not found type ' . $this->type);
        }

        if (method_exists($this, $model->name) === true) {
            try {
                //Пробуем пролезть в iko
                if (!$this->iikoApi->auth()) {
                    throw new BadRequestHttpException('Не удалось авторизоваться в iiko - Office');
                }
                //Синхронизируем нужное нам и
                //ответ получим, сколько записей у нас в боевом состоянии
                $count = $this->{$model->name}();
                $dictionary->successSync($count);
            } catch (\Exception $e) {
                $dictionary->errorSync();
                throw $e;
            } finally {
                //Убиваем сессию, а то закончатся на сервере iiko
                $this->iikoApi->logout();
                //Информацию шлем в FCM
                $dictionary->noticeToFCM();
                if ($dictionary->outerDic->service_id == Registry::IIKO_SERVICE_ID && $dictionary->outerDic->name == 'product') {
                    OrganizationDictionary::updateUnitDictionary($dictionary->status_id, $dictionary->org_id, Registry::IIKO_SERVICE_ID);
                }
            }
            return ['success' => true];
        } else {
            throw new BadRequestHttpException('Not found method [iikoSync->' . $model->name . '()]');
        }
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->iikoApi->logout();
    }
}