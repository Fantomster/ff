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
use common\components\ecom\TestClient;

/**
 * Class TestProvider for unit tests
 *
 * @package common\components\edi\providers
 */
class TestProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var mixed
     */
    public $client;

    /**
     * Provider constructor.
     */
    public function __construct()
    {
        $this->client = new TestClient();
    }

    /**
     * @param $login
     * @param $pass
     * @return array|mixed
     */
    public function getResponse($login, $pass){
        $list = [];
        foreach (glob('tests/edi_xml/test_*.xml') as $file){
            $list[] = str_replace('tests/edi_xml/', '', $file);
        }
        return $list;
    }

    /**
     * @param array $list
     * @throws \yii\db\Exception
     */
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
                var_dump($e->getMessage());
                \Yii::error($e->getMessage());
            }
        }
    }
}