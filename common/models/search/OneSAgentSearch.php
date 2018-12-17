<?php

namespace common\models\search;

use api\common\models\one_s\OneSContragent;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\Organization;

/**
 * Класс для осуществления поиска по контрагентам 1С
 *
 * @property int            $id
 * @property int            $acc
 * @property int            $rid
 * @property int            $vendor_id
 * @property string         $agent_type
 * @property string         $denom
 * @property string         $comment
 * @property string         $created_at
 * @property string         $updated_at
 * @property OneSContragent $agent
 * @property Organization   $organization
 */
class OneSAgentSearch extends OneSContragent
{
    public $searchString;
    public $noComparison;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'one_s_contragent';
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
            [['org_id', 'vendor_id', 'cid'], 'integer'],
            [['name', 'inn_kpp', 'searchString'], 'string'],
            [['org_id'], 'exist', 'skipOnError' => true, 'targetClass' => Organization::className(), 'targetAttribute' => ['org_id' => 'id']],
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

        $query = OneSContragent::find()
            ->where(['org_id' => $organization]);

        if ((isset($this->noComparison)) and ($this->noComparison == 1)) {
            $query->andWhere(['vendor_id' => null]);
        }

        $query->andFilterWhere(['like', 'name', $this->searchString]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $dataProvider;
    }
}
