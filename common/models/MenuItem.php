<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-cmf/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\cmf\common\models;

use dosamigos\transliterator\TransliteratorHelper;
use gromver\cmf\backend\behaviors\NestedSetBehavior;
use gromver\cmf\common\interfaces\ViewableInterface;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;

/**
 * This is the model class for table "cms_menu_item".
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 *
 * @property integer $id
 * @property integer $menu_type_id
 * @property integer $parent_id
 * @property integer $translation_id
 * @property integer $status
 * @property string $language
 * @property string $title
 * @property string $alias
 * @property string $path
 * @property string $note
 * @property string $link
 * @property integer $link_type
 * @property string $link_params
 * @property string $layout_path
 * @property string $access_rule
 * @property string $metakey
 * @property string $metadesc
 * @property string $robots
 * @property integer $secure
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $lft
 * @property integer $rgt
 * @property integer $level
 * @property string $ordering
 * @property string $hits
 * @property string $lock
 *
 * @property string $linkTitle
 * @property MenuType $menuType
 * @property MenuItem $parent
 * @property MenuItem[] $translations
 */
class MenuItem extends ActiveRecord implements ViewableInterface
{
    const STATUS_UNPUBLISHED = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_MAIN_PAGE = 2;

    const LINK_ROUTE = 1;   //MenuItem::link используется в качестве роута, MenuItem::path в качестве ссылки
    const LINK_HREF = 2;    //MenuItem::link используется в качестве ссылки, MenuItem::path не используется

    const CONTEXT_PROPER = 1;
    const CONTEXT_APPLICABLE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_menu_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['menu_type_id', 'parent_id', 'status', 'link_type', 'secure', 'created_at', 'updated_at', 'created_by', 'updated_by', 'lft', 'rgt', 'level', 'ordering', 'hits', 'lock'], 'integer'],
            [['menu_type_id'], 'required'],
            [['menu_type_id'], 'exist', 'targetAttribute' => 'id', 'targetClass' => MenuType::className()],
            [['language'], 'required'],
            [['language'], 'string', 'max' => 7],
            [['language'], function($attribute) {
                if (($parent = self::findOne($this->parent_id)) && !$parent->isRoot() && $parent->language != $this->language) {
                    $this->addError($attribute, Yii::t('gromver.cmf', 'Language has to match with the parental.'));
                }
            }],
            [['title', 'link', 'layout_path'], 'string', 'max' => 1024],
            [['alias', 'note', 'metakey'], 'string', 'max' => 255],
            [['metadesc'], 'string', 'max' => 2048],
            [['access_rule', 'robots'], 'string', 'max' => 50],

            [['parent_id'], 'exist', 'targetAttribute' => 'id'],
            [['parent_id'], 'compare', 'compareAttribute' => 'id', 'operator' => '!='],
            [['parent_id'], function($attribute) {
                if (($parent = self::findOne($this->parent_id)) && !$parent->isRoot() && $parent->menu_type_id != $this->menu_type_id) {
                    $this->addError($attribute, Yii::t('gromver.cmf', 'Parental point of the menu doesn\'t correspond to the chosen menu type.'));
                }
            }],
            [['status'], function($attribute) {
                if ($this->status == self::STATUS_MAIN_PAGE && $this->link_type == self::LINK_HREF) {
                    $this->addError($attribute, Yii::t('gromver.cmf', 'Alias of the menu item can\'t be a main page.'));
                }
            }],
            [['alias'], 'filter', 'filter' => 'trim'],
            [['alias'], 'filter', 'filter' => function($value){
                    if (empty($value)) {
                        return Inflector::slug(TransliteratorHelper::process($this->title));
                    } else {
                        return Inflector::slug($value);
                    }
                }],
            [['alias'], 'unique', 'filter' => function($query) {
                    /** @var $query \yii\db\ActiveQuery */
                    if ($parent = self::findOne($this->parent_id)){
                        $query->andWhere('lft>=:lft AND rgt<=:rgt AND level=:level AND language=:language', [
                                'lft' => $parent->lft,
                                'rgt' => $parent->rgt,
                                'level' => $parent->level + 1,
                                'language' => $this->language
                            ]);
                    }
                }],
            [['alias'], 'string', 'max' => 255],
            [['alias'], 'required', 'enableClientValidation' => false],
            [['translation_id'], 'unique', 'filter' => function($query) {
                /** @var $query \yii\db\ActiveQuery */
                $query->andWhere(['language' => $this->language]);
            }, 'message' => Yii::t('gromver.cmf', 'Локализация ({language}) для записи (ID:{id}) уже существует.', ['language' => $this->language, 'id' => $this->translation_id])],
            [['title',  'link', 'status'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('gromver.cmf', 'ID'),
            'menu_type_id' => Yii::t('gromver.cmf', 'Menu Type ID'),
            'parent_id' => Yii::t('gromver.cmf', 'Parent ID'),
            'translation_id' => Yii::t('gromver.cmf', 'Translation ID'),
            'status' => Yii::t('gromver.cmf', 'Status'),
            'language' => Yii::t('gromver.cmf', 'Language'),
            'title' => Yii::t('gromver.cmf', 'Title'),
            'alias' => Yii::t('gromver.cmf', 'Alias'),
            'path' => Yii::t('gromver.cmf', 'Path'),
            'note' => Yii::t('gromver.cmf', 'Note'),
            'link' => Yii::t('gromver.cmf', 'Link'),
            'link_type' => Yii::t('gromver.cmf', 'Link Type'),
            'link_params' => Yii::t('gromver.cmf', 'Link Params'),
            'layout_path' => Yii::t('gromver.cmf', 'Layout Path'),
            'access_rule' => Yii::t('gromver.cmf', 'Access Rule'),
            'metakey' => Yii::t('gromver.cmf', 'Metakey'),
            'metadesc' => Yii::t('gromver.cmf', 'Metadesc'),
            'robots' => Yii::t('gromver.cmf', 'Robots'),
            'secure' => Yii::t('gromver.cmf', 'Secure'),
            'created_at' => Yii::t('gromver.cmf', 'Created At'),
            'updated_at' => Yii::t('gromver.cmf', 'Updated At'),
            'created_by' => Yii::t('gromver.cmf', 'Created By'),
            'updated_by' => Yii::t('gromver.cmf', 'Updated By'),
            'lft' => Yii::t('gromver.cmf', 'Lft'),
            'rgt' => Yii::t('gromver.cmf', 'Rgt'),
            'level' => Yii::t('gromver.cmf', 'Level'),
            'ordering' => Yii::t('gromver.cmf', 'Ordering'),
            'hits' => Yii::t('gromver.cmf', 'Hits'),
            'lock' => Yii::t('gromver.cmf', 'Lock'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            BlameableBehavior::className(),
            NestedSetBehavior::className()
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenuType()
    {
        return $this->hasOne(MenuType::className(), ['id' => 'menu_type_id']);
    }

    /**
     * @return static | null
     */
    public function getParent() {
        return $this->parent()->one();
    }

    /**
     * @inheritdoc
     * @return MenuItemQuery
     */
    public static function find()
    {
        return new MenuItemQuery(get_called_class());
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_UPDATE,
        ];
    }

    private static $_statuses = [
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_UNPUBLISHED => 'Unpublished',
        self::STATUS_MAIN_PAGE => 'Main page',
    ];

    public static function statusLabels()
    {
        return array_map(function($label) {
                return Yii::t('gromver.cmf', $label);
            }, self::$_statuses);
    }

    public function getStatusLabel($status=null)
    {
        if ($status === null) {
            return Yii::t('gromver.cmf', self::$_statuses[$this->status]);
        }
        return Yii::t('gromver.cmf', self::$_statuses[$status]);
    }

    public function getTranslations()
    {
        return self::hasMany(self::className(), ['translation_id' => 'translation_id'])->indexBy('language');
    }

    public function optimisticLock()
    {
        return 'lock';
    }

    public function save($runValidation = true, $attributes = null) {
        if ($this->getIsNewRecord() && $this->parent_id) {
            return $this->appendTo(self::findOne($this->parent_id), $runValidation, $attributes);
        }

        return $this->saveNode($runValidation,$attributes);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $newParent = self::findOne($this->parent_id);
        $moved = false;
        if (isset($newParent)) {
            if (!($parent = $this->parent()->one()) or !$parent->equals($newParent)) {
                //предок изменился
                $this->moveAsLast($newParent);
                $newParent->refresh();
                $newParent->reorderNode('lft');
                $moved = true;
            } else {
                if(array_key_exists('ordering', $changedAttributes)) $newParent->reorderNode('ordering');
            }
        }

        if ($moved) {
            $this->refresh();
            $this->normalizePath();
        } elseif (array_key_exists('alias', $changedAttributes)) {
            $this->normalizePath();
        }

        //Если изменен тип меню или язык, смена языка возможна только для корневых пунктов меню
        if (array_key_exists('menu_type_id', $changedAttributes) || array_key_exists('language', $changedAttributes)) {
            $this->normalizeDescendants();
        }

        if (array_key_exists('status', $changedAttributes)) {
            $this->normalizeStatus();
        }

        if ($insert && $this->translation_id === null) {
            $this->updateAttributes([
                'translation_id' => $this->id
            ]);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    private function calculatePath()
    {
        $aliases = $this->ancestors()->noRoots()->select('alias')->column();
        return empty($aliases) ? $this->alias : implode('/', $aliases) . '/' . $this->alias;
    }

    public function normalizePath($parentPath = null)
    {
        if ($parentPath === null) {
            $path = $this->calculatePath();
        } else {
            $path = $parentPath . '/' . $this->alias;
        }

        $this->updateAttributes(['path' => $path]);

        $children = $this->children()->all();
        foreach ($children as $child) {
            /** @var self $child */
            $child->normalizePath($path);
        }
    }

    public function normalizeDescendants()
    {
        $ids = $this->descendants()->select('id')->column();
        self::updateAll(['menu_type_id' => $this->menu_type_id, 'language' => $this->language], ['id' => $ids]);
    }
    //для каждого языка возможен только один пукнт меню со статусом главной страницы
    public function normalizeStatus()
    {
        if ($this->status == self::STATUS_MAIN_PAGE) {
            self::updateAll(['status' => self::STATUS_PUBLISHED], 'status=:status AND language=:language AND id!=:id', [':status' => self::STATUS_MAIN_PAGE, ':id' => $this->id, ':language' => $this->language]);
        }
    }

    public function getLinkParams()
    {
        return Json::decode($this->link_params);
    }

    public function setLinkParams($value)
    {
        $this->link_params = Json::encode($value);
    }

    /**
     * @return MenuLinkParams
     */
    public function getLinkParamsModel()
    {
        $model = new MenuLinkParams();
        $model->setAttributes($this->getLinkParams());
        return $model;
    }


    static public function getLinkTypes()
    {
        return [
            self::LINK_ROUTE => 'Ссылка на компонент',
            self::LINK_HREF => 'Обычная ссылка',
        ];
    }


    private static $_linkTypes = [
        self::LINK_ROUTE => 'Ссылка на компонент',
        self::LINK_HREF => 'Обычная ссылка',
    ];

    public static function linkTypeLabels()
    {
        return array_map(function($label) {
                return Yii::t('gromver.cmf', $label);
            }, self::$_statuses);
    }

    public function getLinkTypeLabel($type=null)
    {
        if ($type === null) {
            return Yii::t('gromver.cmf', self::$_linkTypes[$this->link_type]);
        }
        return Yii::t('gromver.cmf', self::$_linkTypes[$type]);
    }


    public function parseUrl()
    {
        $arUrl = parse_url($this->link);
        parse_str(@$arUrl['query'], $params);
        if(!empty($arUrl['fragment']))
            $params['#'] = $arUrl['fragment'];
        return [trim($arUrl['path'], '/'), $params];
    }

    public static function toRoute($route, $params=null)
    {
        if (is_array($route)) {
            $_route = $route;
            $route = ArrayHelper::remove($_route, 0);
            $params = array_merge($_route, (array)$params);
        }

        return !empty($params) ? $route . '?' . http_build_query($params):$route;
    }

    private $_context;

    public function setContext($value)
    {
        $this->_context = $value;
    }

    public function getContext()
    {
        return $this->_context;
    }

    public function isProperContext()
    {
        return $this->_context === self::CONTEXT_PROPER;
    }

    public function isApplicableContext()
    {
        return $this->_context === self::CONTEXT_APPLICABLE;
    }

    //ViewableInterface
    /**
     * @inheritdoc
     */
    public function getViewLink()
    {
        if ($this->link_type == self::LINK_ROUTE) {
            return '/' . $this->path;
        } else {
            return $this->link;
        }
    }
    /**
     * @inheritdoc
     */
    public static function viewLink($model) {
        if ($model['link_type']==self::LINK_ROUTE) {
            return '/' . $model['path'];
        } else {
            return $model['link'];
        }
    }

    public function getBreadcrumbs($includeSelf = false)
    {
        if ($this->isRoot()) {
            return [];
        } else {
            $path = $this->ancestors()->noRoots()->all();
            if ($includeSelf) {
                $path[] = $this;
            }
            return array_map(function ($item) {
                /** @var self $item */
                return [
                    'label' => $item->title,
                    'url' => $item->getViewLink()
                ];
            }, $path);
        }
    }

    /**
     * Тайтл для ссылок в меню
     * @return string
     */
    public function getLinkTitle()
    {
        $linkParams = $this->getLinkParams();
        return empty($linkParams['title']) ? $this->title : $linkParams['title'];
    }
}
