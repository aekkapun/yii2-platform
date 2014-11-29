<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-grom/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\platform\frontend\modules\news\components;


use gromver\platform\frontend\components\MenuRouter;
use gromver\platform\common\models\Category;
use gromver\platform\common\models\MenuItem;
use gromver\platform\common\models\Post;
use gromver\platform\common\models\Tag;

/**
 * Class MenuRouterNews
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class MenuRouterNews extends MenuRouter {
    public $postSuffix = 'html';

    public function parseUrlRules()
    {
        return [
            [
                'menuRoute' => 'grom/news/category/view',
                'handler' => 'parseCategory'
            ],
            [
                'menuRoute' => 'grom/news/post/index',
                'handler' => 'parseAllPosts'
            ],
        ];
    }

    /**
     * @return array
     */
    public function createUrlRules()
    {
        return [
            [
                'requestRoute' => 'grom/news/post/view',
                'requestParams' => ['id'],
                'handler' => 'createPost'
            ],
            [
                'requestRoute' => 'grom/news/category/view',
                'requestParams' => ['id'],
                'handler' => 'createCategory'
            ],
            [
                'requestRoute' => 'grom/news/post/day',
                'requestParams' => ['year', 'month', 'day'],
                'handler' => 'createDayPosts'
            ],
            [
                'requestRoute' => 'grom/news/post/index',
                'handler' => 'createAllPosts'
            ],
            [
                'requestRoute' => 'grom/tag/default/posts',
                'handler' => 'createTagPosts'
            ],
            [
                'requestRoute' => 'grom/news/post/rss',
                'handler' => 'createRss'
            ],
        ];
    }

    public function parseCategory($requestInfo)
    {
        if (preg_match("#((.*)/)?(rss)$#", $requestInfo->requestRoute, $matches)) {
            //rss лента
            if ($menuCategory = Category::findOne($requestInfo->menuParams['id'])) {
                $categoryPath = $matches[2];
                $category = empty($categoryPath) ? $menuCategory : Category::findOne([
                    'path' => $menuCategory->path . '/' . $categoryPath,
                    'language' => $menuCategory->language
                ]);
                if ($category) {
                    return ['grom/news/post/rss', ['category_id' => $category->id]];
                }
            }
        } elseif (preg_match("#((.*)/)?(\d{4})/(\d{1,2})/(\d{1,2})$#", $requestInfo->requestRoute, $matches)) {
            //новости за определенную дату
            if ($menuCategory = Category::findOne($requestInfo->menuParams['id'])) {
                $categoryPath = $matches[2];
                $year = $matches[3];
                $month = $matches[4];
                $day = $matches[5];
                $category = empty($categoryPath) ? $menuCategory : Category::findOne([
                    'path' => $menuCategory->path . '/' . $categoryPath,
                    'language' => $menuCategory->language
                ]);
                if ($category) {
                    return ['grom/news/post/day', ['category_id' => $category->id, 'year' => $year, 'month' => $month, 'day' => $day]];
                }
            }
        } elseif (preg_match("#((.*)/)?(([^/]+)\.{$this->postSuffix})$#", $requestInfo->requestRoute, $matches)) {
            //ищем пост
            if ($menuCategory = Category::findOne($requestInfo->menuParams['id'])) {
                $categoryPath = $matches[2];
                $postAlias = $matches[4];
                $category = empty($categoryPath) ? $menuCategory : Category::findOne([
                    'path' => $menuCategory->path . '/' . $categoryPath,
                    'language' => $menuCategory->language
                ]);
                if ($category && $postId = Post::find()->select('id')->where(['alias' => $postAlias, 'category_id' => $category->id])->scalar()) {
                    return ['grom/news/post/view', ['id' => $postId]];
                }
            }
        } elseif (preg_match("#((.*)/)?(tag/([^/]+))$#", $requestInfo->requestRoute, $matches)) {
            //ищем тэг
            if ($menuCategory = Category::findOne($requestInfo->menuParams['id'])) {
                $categoryPath = $matches[2];
                $tagAlias = $matches[4];
                $category = empty($categoryPath) ? $menuCategory : Category::findOne([
                    'path' => $menuCategory->path . '/' . $categoryPath,
                    'language' => $menuCategory->language
                ]);
                if ($category && $tagId = Tag::find()->select('id')->where(['alias' => $tagAlias, 'language' => $category->language])->scalar()) {
                    return ['grom/tag/default/posts', ['id' => $tagId, 'category_id' => $category->id]];
                }
            }
        } else {
            //ищем категорию
            if ($menuCategory = Category::findOne($requestInfo->menuParams['id'])) {
                if ($category = Category::findOne([
                    'path' => $menuCategory->path . '/' . $requestInfo->requestRoute,
                    'language' => $menuCategory->language
                ])) {
                    return ['grom/news/category/view', ['id' => $category->id]];
                }
            }
        }
    }

    public function parseAllPosts($requestInfo)
    {
        if ($requestInfo->requestRoute == 'rss') {
            return ['grom/news/post/rss', []];
        } elseif (preg_match("#^(\d{4})/(\d{1,2})/(\d{1,2})$#", $requestInfo->requestRoute, $matches)) {
            //новости за определенную дату
            return ['grom/news/post/day', ['year' => $matches[1], 'month' => $matches[2], 'day' => $matches[3]]];
        } elseif (preg_match("#^((.*)/)(([^/]+)\.{$this->postSuffix})$#", $requestInfo->requestRoute, $matches)) {
            //ищем пост
            $categoryPath = $matches[2];    //путь категории поста
            $postAlias = $matches[4];       //алиас поста
            $category = Category::findOne([
                'path' => $categoryPath,
                'language' => $requestInfo->menuMap->language
            ]);
            if ($category && $postId = Post::find()->select('id')->where(['alias' => $postAlias, 'category_id' => $category->id])->scalar()) {
                return ['grom/news/post/view', ['id' => $postId]];
            }
        } elseif (preg_match("#^(tag/([^/]+))$#", $requestInfo->requestRoute, $matches)) {
            //ищем тег
            $tagAlias = $matches[2];
            if ($tagId = Tag::find()->select('id')->where(['alias' => $tagAlias])->scalar()) {
                return ['grom/tag/default/posts', ['id' => $tagId]];
            }
        }

    }

    public function createPost($requestInfo)
    {
        //пытаемся найти пункт меню ссылющийся на данный пост
        if ($path = $requestInfo->menuMap->getMenuPathByRoute(MenuItem::toRoute('grom/news/post/view', ['id' => $requestInfo->requestParams['id']]))) {
            unset($requestInfo->requestParams['id'], $requestInfo->requestParams['category_id'], $requestInfo->requestParams['alias']);
            return MenuItem::toRoute($path, $requestInfo->requestParams);
        }
        //ищем пункт меню ссылающийся на категорию данного поста либо ее предков
        if (isset($requestInfo->requestParams['category_id']) && isset($requestInfo->requestParams['alias'])) {
            //можем привязаться к пункту меню ссылающемуся на категорию новостей к которой принадлежит данный пост(напрямую либо косвенно)
            if ($path = $this->findCategoryMenuPath($requestInfo->requestParams['category_id'], $requestInfo->menuMap)) {
                $path .= '/' . $requestInfo->requestParams['alias'] . '.' . $this->postSuffix;
                unset($requestInfo->requestParams['id'], $requestInfo->requestParams['category_id'], $requestInfo->requestParams['alias']);
                return MenuItem::toRoute($path, $requestInfo->requestParams);
            }
        }
        //привязываем ко всем новостям, если пукнт меню существует
        if ($path = $requestInfo->menuMap->getMenuPathByRoute('grom/news/post/index')) {
            $path .= '/' . Post::findOne($requestInfo->requestParams['id'])->category->path . '/' . $requestInfo->requestParams['alias'] . '.' . $this->postSuffix;
            unset($requestInfo->requestParams['id'], $requestInfo->requestParams['category_id'], $requestInfo->requestParams['alias']);
            return MenuItem::toRoute($path, $requestInfo->requestParams);
        }
    }

    public function createCategory($requestInfo)
    {
        if ($path = $this->findCategoryMenuPath($requestInfo->requestParams['id'], $requestInfo->menuMap)) {
            unset($requestInfo->requestParams['id']);
            return MenuItem::toRoute($path, $requestInfo->requestParams);
        }
    }

    public function createDayPosts($requestInfo)
    {
        if ($requestInfo->requestParams['category_id']) {
            $path = $this->findCategoryMenuPath($requestInfo->requestParams['category_id'], $requestInfo->menuMap);
        } else {
            $path = $requestInfo->menuMap->getMenuPathByRoute('grom/news/post/index');
        }

        if ($path) {
            $path .= "/{$requestInfo->requestParams['year']}/{$requestInfo->requestParams['month']}/{$requestInfo->requestParams['day']}";
            unset($requestInfo->requestParams['category_id'], $requestInfo->requestParams['year'], $requestInfo->requestParams['month'], $requestInfo->requestParams['day']);
            return MenuItem::toRoute($path, $requestInfo->requestParams);
        }
    }

    public function createAllPosts($requestInfo)
    {
        if ($path = $requestInfo->menuMap->getMenuPathByRoute('grom/news/post/index')) {
            return MenuItem::toRoute($path, $requestInfo->requestParams);
        }
    }

    public function createTagPosts($requestInfo)
    {
        //строим ссылку на основе пункта меню на категорию
        if (isset($requestInfo->requestParams['category_id']) && isset($requestInfo->requestParams['tag_alias']) && $path = $this->findCategoryMenuPath($requestInfo->requestParams['category_id'], $requestInfo->menuMap)) {
            $path .= '/tag/' . $requestInfo->requestParams['tag_alias'];
            unset($requestInfo->requestParams['tag_alias'], $requestInfo->requestParams['category_id'], $requestInfo->requestParams['tag_id']);
        }
        //строим ссылку на основе пункта меню на все новости
        if (isset($requestInfo->requestParams['tag_alias']) && $path = $requestInfo->menuMap->getMenuPathByRoute('grom/news/post/index')) {
            $path .= '/tag/' . $requestInfo->requestParams['tag_alias'];
            unset($requestInfo->requestParams['tag_alias'], $requestInfo->requestParams['category_id'], $requestInfo->requestParams['tag_id']);
        }

        if (isset($path)) {
            return MenuItem::toRoute($path, $requestInfo->requestParams);
        }
    }

    public function createRss($requestInfo)
    {
        if (isset($requestInfo->requestParams['category_id'])) {
            if ($path = $this->findCategoryMenuPath($requestInfo->requestParams['category_id'], $requestInfo->menuMap)) {
                unset($requestInfo->requestParams['category_id']);
                return MenuItem::toRoute($path . '/rss', $requestInfo->requestParams);
            }
        } else {
            if ($path = $requestInfo->menuMap->getMenuPathByRoute('grom/news/post/index')) {
                return MenuItem::toRoute($path . '/rss', $requestInfo->requestParams);
            }
        }
    }

    private $_categoryPaths = [];

    /**
     * Находит путь к пункту меню ссылающемуся на категорию $categoryId, либо ее предка
     * Если путь ведет к предку, то достраиваем путь категории $categoryId
     * @param $categoryId
     * @param $menuMap \gromver\platform\frontend\components\MenuMap
     * @return null|string
     */
    private function findCategoryMenuPath($categoryId, $menuMap)
    {
        if (!isset($this->_categoryPaths[$menuMap->language][$categoryId])) {
            if ($path = $menuMap->getMenuPathByRoute(MenuItem::toRoute('grom/news/category/view', ['id' => $categoryId]))) {
                $this->_categoryPaths[$menuMap->language][$categoryId] = $path;
            } elseif (($category = Category::findOne($categoryId)) && !$category->isRoot() && $path = $this->findCategoryMenuPath($category->parent_id, $menuMap)) {
                $this->_categoryPaths[$menuMap->language][$categoryId] = $path . '/' . $category->alias;
            } else {
                $this->_categoryPaths[$menuMap->language][$categoryId] = false;
            }
        }

        return $this->_categoryPaths[$menuMap->language][$categoryId];
    }
}