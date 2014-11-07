<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-cmf/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\cmf\frontend\widgets;

use gromver\cmf\common\widgets\Widget;
use gromver\cmf\common\models\Post;
use gromver\cmf\common\models\Tag;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;

/**
 * Class TagPosts
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class TagPosts extends Widget {
    /**
     * Tag or TagId or TagId:TagAlias
     * @var Tag|string
     * @type modal
     * @url /cmf/default/select-tag
     */
    public $source;
    /**
     * CategoryId
     * @var string
     * @type modal
     * @url /cmf/default/select-category
     */
    public $categoryId;
    /**
     * @type list
     * @items languages
     */
    public $language;
    /**
     * @type list
     * @items layouts
     * @editable
     */
    public $layout = 'tag/postsDefault';
    /**
     * @type list
     * @items itemLayouts
     * @editable
     */
    public $itemLayout = '_itemPost';
    public $pageSize = 20;
    /**
     * @type list
     * @editable
     * @items sortColumns
     * @var string
     */
    public $sort = 'published_at';
    /**
     * @type list
     * @editable
     * @items sortDirections
     */
    public $dir = SORT_DESC;
    /**
     * @ignore
     */
    public $listViewOptions = [];


    protected function normalizeSource()
    {
        if ($this->source && !$this->source instanceof Tag) {
            @list($id, $alias) = explode(':', $this->source);
            $this->source = null;

            if ($alias) {
                $this->language or $this->language = Yii::$app->language;

                $this->source = Tag::find()->andWhere(['alias' => $alias, 'language' => $this->language])->one();
            }

            if (empty($this->source)) {
                $this->source = Tag::findOne($id);
            }
        }

        if (empty($this->source)) {
            throw new InvalidConfigException(Yii::t('gromver.cmf', 'Tag must be set.'));
        }
    }

    protected function launch()
    {
        $this->normalizeSource();

        echo $this->render($this->layout, [
            'dataProvider' => new ActiveDataProvider([
                    'query' => $this->getQuery(),
                    'pagination' => [
                        'pageSize' => $this->pageSize
                    ],
                    'sort' => [
                        'defaultOrder' => [$this->sort => (int)$this->dir]
                    ]
                ]),
            'itemLayout' => $this->itemLayout,
            'model' => $this->source
        ]);
    }

    protected function getQuery()
    {
        return Post::find()->published()->category($this->categoryId)->innerJoinWith('tags', false)->andWhere(['{{%cms_tag}}.id' => $this->source->id]);
    }

    public static function layouts()
    {
        return [
            'tag/postsDefault' => Yii::t('gromver.cmf', 'Default'),
            'tag/postsList' => Yii::t('gromver.cmf', 'List'),
        ];
    }

    public static function itemLayouts()
    {
        return [
            '_itemPost' => Yii::t('gromver.cmf', 'Default'),
        ];
    }


    public static function sortColumns()
    {
        return [
            'published_at' => Yii::t('gromver.cmf', 'By publish date'),
            'created_at' => Yii::t('gromver.cmf', 'By create date'),
            'title' => Yii::t('gromver.cmf', 'By name'),
            'ordering' => Yii::t('gromver.cmf', 'By order'),
        ];
    }

    public static function sortDirections()
    {
        return [
            SORT_ASC => Yii::t('gromver.cmf', 'Asc'),
            SORT_DESC => Yii::t('gromver.cmf', 'Desc'),
        ];
    }

    public static function languages()
    {
        return ['' => Yii::t('gromver.cmf', 'Autodetect')] + Yii::$app->getLanguagesList();
    }
} 