<?php

namespace common\models\search;

use api\common\models\RkAgent;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\Organization;

/**
 * Класс для осуществления поиска по контрагентам R-Keeper
 *
 * @property int          $id
 * @property int          $acc
 * @property int          $rid
 * @property int          $vendor_id
 * @property string       $agent_type
 * @property string       $denom
 * @property string       $comment
 * @property string       $created_at
 * @property string       $updated_at
 * @property RkAgent      $agent
 * @property Organization $organization
 */
class RkAgentSearch extends RkAgent
{
    public $searchString;
    public $noComparison;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rk_agent';
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
            [['created_at', 'updated_at'], 'safe'],
            [['acc', 'vendor_id', 'rid'], 'integer'],
            [['denom', 'comment', 'agent_type'], 'string'],
            [['acc'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['acc' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['vendor_id' => 'id']],
        ];
    }

    /**
     * Search
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params, $organization)
    {

        $this->load($params);

        $query = RkAgent::find()
            ->where(['acc' => $organization]);

        if ((isset($this->noComparison)) and ($this->noComparison == 1)) {
            $query->andWhere(['vendor_id' => null]);
        }

        $query->andFilterWhere(['like', 'denom', $this->searchString]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }
}
