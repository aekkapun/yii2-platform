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
use gromver\cmf\common\models\Category;
use gromver\cmf\common\models\Post;
use yii\data\ActiveDataProvider;
use Yii;

/**
 * Class PostList
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class PostList extends Widget {
    /**
     * Category or CategoryId or CategoryId:CategoryPath
     * @var Category|string
     * @type modal
     * @url /cmf/default/select-category
     */
    public $category;
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
    public $layout = 'post/listDefault';
    /**
     * @type list
     * @items itemLayouts
     * @editable
     */
    public $itemLayout = '_itemIssue';
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

    protected function launch()
    {
        if ($this->category && !$this->category instanceof Category) {
            $this->category = Category::findOne(intval($this->category));
        }

        if (!empty($this->category)) {
            $this->language = null;
        } else {
            $this->language or $this->language = Yii::$app->language;
        }

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
            'category' => $this->category,
            'listViewOptions' => $this->listViewOptions
        ]);
    }

    protected function getQuery()
    {
        return Post::find()->published()->category($this->category ? $this->category->id : null)->language($this->language)->with('tags')->last();
    }

    public function customControls()
    {
        return [
            [
                'url' => Yii::$app->urlManagerBackend->createUrl(['cmf/news/post/create', 'category_id' => $this->category ? $this->category->id : null, 'backUrl' => $this->getBackUrl()]),
                'label' => '<i class="glyphicon glyphicon-plus"></i>',
                'options' => ['title' => Yii::t('gromver.cmf', 'Create Post')]
            ],
            [
                'url' => Yii::$app->urlManagerBackend->createUrl(['cmf/news/post/index', 'PostSearch' => ['category_id' => ($this->category ? $this->category->id : null), 'language' => $this->language]]),
                'label' => '<i class="glyphicon glyphicon-th-list"></i>',
                'options' => ['title' => Yii::t('gromver.cmf', 'Posts list'), 'target' => '_blank']
            ],
        ];
    }

    public static function layouts()
    {
        return [
            'post/listDefault' => Yii::t('gromver.cmf', 'Default'),
            'post/listBlog' => Yii::t('gromver.cmf', 'List with calendar and tags'),
        ];
    }

    public static function itemLayouts()
    {
        return [
            '_itemArticle' => Yii::t('gromver.cmf', 'Article'),
            '_itemIssue' => Yii::t('gromver.cmf', 'Issue'),
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