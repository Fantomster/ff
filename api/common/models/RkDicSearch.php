<?php

namespace api\common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\common\models\RkDic;
use common\models\Organization;
use common\models\User;

/**
 * RkAccessSearch represents the model behind the search form about `api\common\models\RkAccess`.
 */
class RkDicSearch extends RkDic
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'org_id', ], 'integer'],
            [['login', 'password', 'token', 'lic', 'comment'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = RkDic::find()->andWhere('org_id = :org',[':org' => User::findOne([Yii::$app->user->id])->organization_id]);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
         //  'fid' => $this->fid,
            'org_id' => $this->org_id,
         //   'fd' => $this->fd,
         //   'td' => $this->td,
         //   'ver' => $this->ver,
         //   'locked' => $this->locked,
        ]);

     /*   $query->andFilterWhere(['like', 'login', $this->login])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'token', $this->token])
            ->andFilterWhere(['like', 'lic', $this->lic])
            ->andFilterWhere(['like', 'usereq', $this->usereq])
            ->andFilterWhere(['like', 'comment', $this->comment]);
*/
        return $dataProvider;
    }

    /**
     * Проверяет, загружены ли справочники.
     *
     * @return boolean
     */
    public function getDicsLoad()
    {
        $query = RkDic::find()->andWhere('org_id = :org',[':org' => User::findOne([Yii::$app->user->id])->organization_id])->all();
        $ret = true;
        foreach($query as $elem) {
            if ($elem['dicstatus_id']!=RkDic::STATUS_DATA_IS_LOADED) $ret = false;
        }
        return $ret;
    }
    

}
