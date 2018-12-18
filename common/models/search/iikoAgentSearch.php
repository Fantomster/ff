<?php

namespace common\models\search;

use api\common\models\iiko\iikoAgent;
use api\common\models\iiko\iikoStore;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\Organization;

/**
 * Класс для осуществления поиска по контрагентам iiko
 *
 * @property int $id
 * @property int $org_id
 * @property int $is_active
 * @property int $vendor_id
 * @property int $store_id
 * @property int $payment_delay
 * @property string $denom
 * @property string $comment
 * @property string $uuid
 * @property string $created_at
 * @property string $updated_at
 * @property iikoAgent $agent
 * @property Organization $organization
 * @property iikoStore $store
 */
class IikoAgentSearch extends iikoAgent
{
    public $searchString;
    public $noComparison;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'iiko_agent';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_api');
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'noComparison'], 'safe'],
            [['org_id',  'is_active', 'vendor_id', 'store_id'], 'integer'],
            [['payment_delay'], 'integer', 'max' => 365],
            [['denom', 'comment', 'searchString'], 'string'],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['org_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
            [['store_id'], 'exist', 'skipOnError' => true, 'targetClass' => iikoStore::className(), 'targetAttribute' => ['store_id' => 'id']],
        ];
    }

    /**
     * Search
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params, $organization)
    {

        $this->load($params);

        $query = iikoAgent::find()
            ->where(['org_id' => $organization]);

        if ((isset($this->noComparison)) and ($this->noComparison == 1)) {
            $query->andWhere(['vendor_id' => null]);
        }

        $query->andFilterWhere(['like', 'denom', $this->searchString]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }
}