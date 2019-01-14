<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-01-14
 * Time: 10:39
 */

namespace console\modules\daemons\components;

use api_web\components\Registry;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use yii\web\BadRequestHttpException;

/**
 * Class PosterSyncConsumer
 *
 * @package console\modules\daemons\components
 */
class PosterSyncConsumer extends AbstractConsumer
{
    /**@property int|null $orgId Id организации */
    public $orgId;
    /**@var integer */
    const SERVICE_ID = Registry::POSTER_SERVICE_ID;

    /**
     * @var
     */
    public $type;

    /**
     * IikoSyncConsumer constructor.
     *
     * @param null $orgId
     */
    public function __construct($orgId = null)
    {
        $this->orgId = $orgId;
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
                //Синхронизируем нужное нам и
                //ответ получим, сколько записей у нас в боевом состоянии
                $count = $this->{$model->name}();
                $dictionary->successSync($count);
            } catch (\Exception $e) {
                $dictionary->errorSync();
                throw $e;
            } finally {
                //Информацию шлем в FCM
                $dictionary->noticeToFCM();
                if ($dictionary->outerDic->service_id == self::SERVICE_ID && $dictionary->outerDic->name == 'product') {
                    OrganizationDictionary::updateIikoUnitDictionary($dictionary->status_id, $dictionary->org_id);
                }
            }
            return ['success' => true];
        } else {
            throw new BadRequestHttpException('Not found method [posterSync->' . $model->name . '()]');
        }
    }
}