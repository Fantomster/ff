<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-01-14
 * Time: 10:36
 */

namespace console\modules\daemons\classes;

use api_web\components\Poster;
use api_web\exceptions\ValidationException;
use common\models\OuterStore;
use console\modules\daemons\components\ConsumerInterface;
use console\modules\daemons\components\PosterSyncConsumer;

/**
 * Class PosterAgentSync
 *
 * @package console\modules\daemons\classes
 */
class PosterStoreSync extends PosterSyncConsumer implements ConsumerInterface
{
    /**
     * @var array
     */
    public $updates_uuid = [];

    /**
     * @var
     */
    public $success;

    /**
     * @var int
     */
    public static $timeout = 30;

    /**
     * @var int
     */
    public static $timeoutExecuting = 600;

    /**
     * @var string
     */
    public $type = 'store';

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function getData()
    {
        $this->success = $this->run();
    }

    /**
     * @return mixed
     */
    public function saveData()
    {
        return $this->success['success'];
    }

    /**
     * Синхронизация контрагентов
     *
     * @return int
     * @throws ValidationException|\Exception
     */
    protected function store()
    {
        $poster = Poster::getInstance($this->orgId);
        $poster->getStores();
        //Обновляем колличество полученных объектов
        return OuterStore::find()
            ->where(['is_deleted' => 0, 'org_id' => $this->orgId, 'service_id' => self::SERVICE_ID])
            ->andFilterWhere(['!=', 'level', '0'])
            ->count();
    }

}
