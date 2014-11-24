<?php
/**
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-cmf/blob/master/LICENSE
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\cmf\common\models\elasticsearch;


use gromver\cmf\common\models\Category;

/**
 * Class Post
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class Post extends ActiveDocument {
    public function attributes()
    {
        return ['id', 'alias', 'category_id', 'title', 'metakey', 'metadesc', 'published', 'language', 'tags', 'text', 'date'];
    }

    public static function model()
    {
        return \gromver\cmf\common\models\Post::className();
    }

    /**
     * @param \gromver\cmf\common\models\Page $model
     */
    public function loadModel($model)
    {
        $this->attributes = $model->toArray([], ['published', 'language', 'tags', 'text', 'date']);
    }

    public static function filter()
    {
        $filters = [
            [
                'not' => [
                    'and' => [
                        [
                            'type' => ['value' => 'post']
                        ],
                        [
                            'term' => ['published' => false]
                        ]
                    ]
                ]
            ],
        ];

        if ($unpublishedCategories = Category::find()->unpublished()->select('{{%cms_category}}.id')->column()) {
            $filters[] =             [
                'not' => [
                    'and' => [
                        [
                            'type' => ['value' => 'post']
                        ],
                        [
                            'term' => ['category_id' => $unpublishedCategories]
                        ]
                    ]
                ]
            ];
        }

        return $filters;
    }
} 