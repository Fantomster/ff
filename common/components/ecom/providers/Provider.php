<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:10 PM
 */

namespace common\components\ecom\providers;


use common\components\ecom\AbstractProvider;
use common\components\ecom\ProviderInterface;
use yii\base\Exception;

class Provider extends AbstractProvider implements ProviderInterface
{
    public $client;

    public function __construct()
    {
        $this->client = \Yii::$app->siteApi;
    }

    /**
     * @throws Exception
     * */
    public function getResponse($login, $pass){
        $object = $this->client->getList(['user' => ['login' => $login, 'pass' => $pass]]);

        if ($object->result->errorCode != 0) {
            throw new Exception('EComIntegration getList Error №' . $object->result->errorCode);
        }
        $list = $object->result->list ?? null;
        if (!$list) {
            throw new Exception('No files for ' . $login);
        }

        return $list;
    }

    public function insertFilesInQueue(array $list)
    {
        $batch = [];
        $files = (new \yii\db\Query())
            ->select(['name'])
            ->from('edi_files_queue')
            ->where(['name' => $list])
            ->indexBy('name')
            ->all();

        foreach ($list as $name) {
            if (!array_key_exists($name, $files)) {
                $batch[] = [$name];
            }
        }

        if (!empty($batch)) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                \Yii::$app->db->createCommand()->batchInsert('edi_files_queue', ['name'], $batch)->execute();
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollback();
                \Yii::error($e->getMessage());
            }
        }
    }
}