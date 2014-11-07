<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-cmf/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\cmf\common\models;

use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * Class PostQuery
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */

class PostQuery extends ActiveQuery {
    /**
     * @return static
     */
    public function published()
    {
        $badcatsQuery = new Query([
            'select' => ['badcats.id'],
            'from' => ['{{%cms_category}} AS unpublished'],
            'join' => [
                ['LEFT JOIN', '{{%cms_category}} AS badcats', 'unpublished.lft <= badcats.lft AND unpublished.rgt >= badcats.rgt']
            ],
            'where' => 'unpublished.status != '.Category::STATUS_PUBLISHED,
            'groupBy' => ['badcats.id']
        ]);

        return $this->andWhere(['{{%cms_post}}.status' => Post::STATUS_PUBLISHED])->andWhere(['NOT IN', '{{%cms_post}}.category_id', $badcatsQuery]);
    }

    /**
     * Фильтр по категории
     * @param $id
     * @return $this
     */
    public function category($id = null)
    {
        return $this->andFilterWhere(['{{%cms_post}}.category_id' => $id]);
    }

    /**
     * @param null|string $value
     * @return $this
     */
    public function language($value = null)
    {
        if ($value) {
            $this->innerJoinWith('category', false)->andWhere(['{{%cms_category}}.language' => $value]);
        }

        return $this;
    }

    /**
     * Статьи за указанный день
     * @param $year integer
     * @param $month integer
     * @param $day integer
     * @return $this
     */
    public function day($year, $month, $day)
    {
        $from = mktime(0,0,0,$month,$day,$year);
        $to = $from + 86400;

        return $this->andWhere('{{%cms_post}}.published_at>=:from AND {{%cms_post}}.published_at<:to', [':from' => $from, ':to' => $to]);
    }
    /**
     * Статьи до указанного дня
     * @param $year integer
     * @param $month integer
     * @param $day integer
     * @return $this
     */
    public function beforeDay($year, $month, $day)
    {
        $date = mktime(0,0,0,$month,$day,$year);

        return $this->andWhere('{{%cms_post}}.published_at<=:date', [':date' => $date])->orderBy('{{%cms_post}}.published_at DESC');
    }
    /**
     * Статьи после указанного дня
     * @param $year integer
     * @param $month integer
     * @param $day integer
     * @return $this
     */
    public function afterDay($year, $month, $day)
    {
        $date = mktime(0,0,0,$month,$day,$year)+86400;

        return $this->andWhere('{{%cms_post}}.published_at>=:date', [':date' => $date])->orderBy('{{%cms_post}}.published_at ASC');
    }
} 