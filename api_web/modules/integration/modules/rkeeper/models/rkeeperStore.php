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
        $acc = $this->user->organization_id;
        $view_type = $post['view_type'];
        $stores = RkStoretree::find()->andWhere('acc = :acc and active = 1', [':acc' => $acc])->addOrderBy('root, lft')->asArray()->all();
        $arr = [];

        if(empty($stores))
            return [];

        if ($view_type == 1) {
            foreach ($stores as $row) {
                $item['id'] = $row['id'];
                $item['rid'] = $row['rid'];
                $item['name'] = $row['name'];
                $item['type'] = $row['type'];
                $item['level'] = $row['lvl'];
                $arr[]=$item;
            }
        } else {
            $arr = $this->parseStoriesForTree($stores, 0);
        }
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
    private function parseStoriesForTree($stores, $pos)
    {

        $arr = [];
        $prev = $pos -1;
        $lft = ($pos == 0) ? 0 : $stores[$prev]['lft'];
        $rgt = ($pos == 0) ? $stores[0]['rgt'] : $stores[$prev]['rgt'];
        $level = ($pos == 0) ? 0 : ($stores[$prev]['lvl']+1);
        for ($i = $pos; $i < count($stores); $i++) {
            if($stores[$i]['lvl'] == $level && ($stores[$i]['lft'] > $lft && $stores[$i]['lft'] < $rgt))
                {
                    $item = [];
                    $item['id'] = $stores[$i]['id'];
                    $item['rid'] = $stores[$i]['rid'];
                    $item['name'] = $stores[$i]['name'];
                    $item['type'] = $stores[$i]['type'];
                    $item['level'] = $stores[$i]['lvl'];
                    if(isset($stores[$i+1])) {
                        if ($stores[$i + 1]['lvl'] > $level) {
                            $items = $this->parseStoriesForTree($stores, $i + 1);
                            if (!empty($items)) {
                                $item['items'] = $items;
                            }
                        }
                    }
                    $arr[] = $item;
                }
        }
        return $arr;
    }
}