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
use yii\data\ActiveDataProvider;
use Yii;

/**
 * Class CategoryList
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class CategoryList extends Widget {
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
    public $layout = 'category/list';
    /**
     * @type list
     * @items itemLayouts
     * @editable
     */
    public $itemLayout = '_itemArticle';
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

    public function init()
    {
        parent::init();
    }

    protected function normalizeCategory()
    {
        if ($this->category && !$this->category instanceof Category) {
            @list($id, $path) = explode(':', $this->category);
            $this->category = null;

            if ($path) {
                $this->language or $this->language = Yii::$app->language;

                $this->category = Category::find()->andWhere(['path' => $path, 'language' => $this->language])->one();
            }

            if (empty($this->category)) {
                $this->category = Category::findOne($id);
            }
        }
    }

    protected function launch()
    {
        $this->normalizeCategory();

        echo $this->render($this->layout, [
            'dataProvider' => new ActiveDataProvider([
                    'query' => $this->category ? $this->category->children()->published()->orderBy(null) : Category::find()->roots()->published(),
                    'pagination' => false,
                    'sort' => [
                        'defaultOrder' => [$this->sort => $this->dir]
                    ]
                ]),
            'itemLayout' => $this->itemLayout
        ]);
    }

    public static function layouts()
    {
        return [
            'category/list' => Yii::t('gromver.cmf', 'Default'),
        ];
    }


    public static function itemLayouts()
    {
        return [
            '_itemArticle' => Yii::t('gromver.cmf', 'Article'),
            //'_itemNews' => 'Новость',
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