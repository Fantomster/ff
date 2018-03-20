<?php

use yii\db\Migration;

/**
 * Class m180307_090503_add_traslations
 */
class m180307_090503_add_traslations extends Migration
{
    public $rows = [
        [
            'alias' => 'frontend.views.order.order_invoice_create',
            'value' => 'создан на основании накладной 1С'
        ],
        [
            'alias' => 'frontend.views.order.order_invoice',
            'value' => 'первичный заказ'
        ],
        [
            'alias' => 'frontend.views.order.order_invoice_change',
            'value' => 'заменен заказом на основании накладной 1С'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->rows as $row) {
            $source = \common\models\SourceMessage::find()->where(['message' => $row['alias']])->one();
            if (empty($source)) {
                $source = new \common\models\SourceMessage([
                    'category' => 'message',
                    'message' => $row['alias']
                ]);
                $source->save();
            }

            $message = \common\models\Message::findOne(['id' => $source->id, 'translation' => $row['value'], 'language' => 'ru']);
            if (empty($message)) {
                $message = new \common\models\Message([
                    'id' => $source->id,
                    'translation' => $row['value'],
                    'language' => 'ru'
                ]);
                $message->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->rows as $row) {
            $sources = \common\models\SourceMessage::find()->where(['message' => $row['alias']])->all();
            if (!empty($sources)) {
                foreach ($sources as $source) {
                    $message = \common\models\Message::findOne(['id' => $source->id, 'translation' => $row['value'], 'language' => 'ru']);
                    if (!empty($message)) {
                        $message->delete();
                    }
                }
            }
        }
    }
}
