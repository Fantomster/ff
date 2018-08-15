<?php
/**
 * Created by PhpStorm.
 * User: Silukov Konstantin
 * Date: 8/14/2018
 * Time: 11:04 AM
 */

namespace common\helpers;


use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Class ModelsCollection
 * В дальнейшем возможно добавить \ArrayAccess и \Iterator интерфейсы
 * @package common\helpers
 */
class ModelsCollection extends Model
{
    /**
     * Saves multiple models. Only for inserts.
     *
     * @param ActiveRecord[] $models
     * @return array
     */
    public function saveMultiple(array $models)
    {
        if (count($models) > 0) {
            
            $firstModel = reset($models);
            $columnsToInsert = $firstModel->attributes();   // here you can remove excess columns. for example PK column.
            $rowsToInsert = [];
            
            foreach ($models as $model) {
                if ($model->validate()) {
                    if ($model->beforeSave(true)) {
                        $rowsToInsert[] = array_values($model->attributes);// here you can remove excess values
                    }
                } else {
                    return ['success' => false, 'error' => $model->errors];
                }
            }
            
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $numberAffectedRows = \Yii::$app->db_api->createCommand()
                    ->batchInsert($firstModel->tableName(), $columnsToInsert, $rowsToInsert)
                    ->execute();
                $transaction->commit();
            } catch (\Throwable $throwable) {
                $transaction->rollBack();
                return ['success' => false, 'error' => $throwable->getMessage()];
            }
            
            $isSuccess = ($numberAffectedRows === count($models));
            
            if ($isSuccess) {
                $changedAttributes = array_fill_keys($columnsToInsert, null);
                foreach ($models as $model) {
                    $model->afterSave(true, $changedAttributes);
                }
            }
            
            return ['success' => $isSuccess];
        } else {
            
            return ['success' => false, 'error' => 'count(models) < 0 or not array'];
        }
    }
    
    
}