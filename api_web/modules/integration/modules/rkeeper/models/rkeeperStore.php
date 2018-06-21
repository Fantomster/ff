<?php

namespace api_web\modules\integration\modules\rkeeper\models;

use api\common\models\RkStoretree;
use api_web\components\WebApi;

class rkeeperStore extends WebApi
{
    /**
     * rkeeper: Справочник складов
     * @param array $post
     * @return array
     * @throws \Exception
     */
    public function getStoreList(array $post)
    {
        $acc = User::findOne($this->user->id)->organization_id;
        $view_type = $post['view_type'];
        $stores = RkStoretree::find()->andWhere('acc = :acc and active = 1', [':acc' => $acc])->addOrderBy('root, lft')->asArray()->all();
        $arr = [];

        if ($view_type == 1) {
            foreach ($stores as $row) {
                $item['id'] = $row['id'];
                $item['rid'] = $row['rid'];
                $item['name'] = $row['name'];
                $item['type'] = $row['type'];
                $item['level'] = $row['lvl'];
            }
        } else
            $arr = $this->parseStoriesForTree($stores, $pos, 0);

        return $arr;
    }

    /**
     * rkeeper: Парсинг списка складов для построения дерева
     * @param array $stores
     * @param int $pos
     * @param int level
     * @return array
     * @throws \Exception
     */
    private function parseStoriesForTree($stores, &$pos, $level)
    {
        $arr = [];
        if ($stores[$pos + 1]['level'] == $level)
            return $arr;
        for ($i = $pos + 1; $i <= count($stores); $i++) {
            $item['id'] = $stores[$i]['id'];
            $item['rid'] = $stores[$i]['rid'];
            $item['name'] = $stores[$i]['name'];
            $item['type'] = $stores[$i]['type'];
            $item['level'] = $stores[$i]['lvl'];
            $items = $this->parseStoriesForTree($stores, $i, $stores[$i]['lvl']);
            if (!empty($items))
                $item['items'] = $items;
            $arr[] = $item;
        }

        return $arr;
    }
}