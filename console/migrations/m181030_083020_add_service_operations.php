<?php

use common\models\AllServiceOperation;
use yii\db\Migration;

/**
 * Class m181030_083020_add_service_operations
 */
class m181030_083020_add_service_operations extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $params =[
            [
                'denom' => 'auth',
                'comment' => 'Авторизация',
            ],
            [
                'denom' => 'parsing',
                'comment' => 'Разбор поставки (парсинг файлов)',
            ],
            [
                'denom' => 'product_create',
                'comment' => 'Создание товара (в catalog_base_goods)',
            ],
            [
                'denom' => 'order_create',
                'comment' => 'Создание заказа',
            ],
        ];
        for ($i=1; $i < 5; $i++){
            $item = $params[$i-1];
            $model = new AllServiceOperation();
            $model->code = $i;
            $model->denom = $item['denom'];
            $model->comment = $item['comment'];
            $model->service_id = 3;
            $model->save();
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181030_083020_add_service_operations cannot be reverted.\n";

        return false;
    }
}
