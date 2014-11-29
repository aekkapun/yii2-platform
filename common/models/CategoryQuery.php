<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-grom/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\platform\common\models;

use creocoder\behaviors\NestedSetQuery;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * Class CategoryQuery
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class CategoryQuery extends ActiveQuery
{
    public function behaviors() {
        return [
            [
                'class' => NestedSetQuery::className(),
            ],
        ];
    }
    /**
     * @return CategoryQuery
     */
    public function published()
    {
        $badcatsQuery = new Query([
            'select' => ['badcats.id'],
            'from' => ['{{%grom_category}} AS unpublished'],
            'join' => [
                ['LEFT JOIN', '{{%grom_category}} AS badcats', 'unpublished.lft <= badcats.lft AND unpublished.rgt >= badcats.rgt']
            ],
            'where' => 'unpublished.status != '.Category::STATUS_PUBLISHED,
            'groupBy' => ['badcats.id']
        ]);

        return $this->andWhere(['NOT IN', '{{%grom_category}}.id', $badcatsQuery]);
    }

    /**
     * @return CategoryQuery
     */
    public function unpublished()
    {
        return $this->innerJoin('{{%grom_category}} AS ancestors', '{{%grom_category}}.lft >= ancestors.lft AND {{%grom_category}}.rgt <= ancestors.rgt')->andWhere('ancestors.status != '.Category::STATUS_PUBLISHED)->addGroupBy(['{{%grom_category}}.id']);
    }

    /**
     * Фильтр по категории
     * @param $id
     * @return $this
     */
    public function parent($id)
    {
        return $this->andWhere(['{{%grom_category}}.parent_id' => $id]);
    }

    /**
     * @param $language
     * @return static
     */
    public function language($language)
    {
        return $this->andFilterWhere(['{{%grom_category}}.language' => $language]);
    }

    /**
     * @return static
     */
    public function noRoots()
    {
        return $this->andWhere('{{%grom_category}}.lft!=1');
    }
} 