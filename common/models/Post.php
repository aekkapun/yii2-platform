<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-grom/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\platform\common\models;

use dosamigos\transliterator\TransliteratorHelper;
use gromver\platform\backend\behaviors\TaggableBehavior;
use gromver\platform\backend\behaviors\upload\ThumbnailProcessor;
use gromver\platform\backend\behaviors\UploadBehavior;
use gromver\platform\backend\behaviors\VersioningBehavior;
use gromver\platform\common\components\UrlManager;
use gromver\platform\common\interfaces\TranslatableInterface;
use gromver\platform\common\interfaces\ViewableInterface;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * This is the model class for table "grom_post".
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $translation_id
 * @property string $language
 * @property string $title
 * @property string $alias
 * @property string $preview_text
 * @property string $preview_image
 * @property string $detail_text
 * @property string $detail_image
 * @property string $metakey
 * @property string $metadesc
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $published_at
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $ordering
 * @property integer $hits
 * @property integer $lock
 *
 * @property Category $category
 * @property User[] $viewers
 * @property Tag[] $tags
 * @property Post[] $translations
 */
class Post extends ActiveRecord implements TranslatableInterface, ViewableInterface
{
    const STATUS_PUBLISHED = 1;
    const STATUS_UNPUBLISHED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%grom_post}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'category_id', 'title', 'detail_text', 'language'], 'required'],
            [['category_id', 'created_at', 'updated_at', 'status', 'created_by', 'updated_by', 'ordering', 'hits', 'lock'], 'integer'],
            [['preview_text', 'detail_text'], 'string'],
            [['language'], 'string', 'max' => 7],
            [['title', 'preview_image', 'detail_image'], 'string', 'max' => 1024],
            [['alias', 'metakey'], 'string', 'max' => 255],
            [['metadesc'], 'string', 'max' => 2048],

            [['published_at'], 'date', 'format' => 'dd.MM.yyyy HH:mm', 'timestampAttribute' => 'published_at', 'when' => function () {
                    return is_string($this->published_at);
                }],
            [['published_at'], 'integer', 'enableClientValidation' => false],
            [['category_id'], 'exist', 'targetClass' => Category::className(), 'targetAttribute' => 'id', 'filter' => function($query) {
                /** @var $query \gromver\platform\common\models\CategoryQuery */
                $query->noRoots();
            }],
            [['alias'], 'filter', 'filter' => 'trim'],
            [['alias'], 'filter', 'filter' => function ($value) {
                    if (empty($value)) {
                        return Inflector::slug(TransliteratorHelper::process($this->title));
                    } else {
                        return Inflector::slug($value);
                    }
                }],
            [['alias'], 'unique', 'filter' => function ($query) {
                    /** @var $query \yii\db\ActiveQuery */
                    $query->andWhere(['category_id' => $this->category_id, 'language' => $this->language]);
                }, 'message' => '{attribute} - Another article from this category has the same alias'],
            [['translation_id'], 'unique', 'filter' => function($query) {
                /** @var $query \yii\db\ActiveQuery */
                $query->andWhere(['language' => $this->language]);
            }, 'message' => Yii::t('gromver.platform', 'Локализация ({language}) для записи (ID:{id}) уже существует.', ['language' => $this->language, 'id' => $this->translation_id])],
            [['tags', 'versionNote'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('gromver.platform', 'ID'),
            'category_id' => Yii::t('gromver.platform', 'Category'),
            'translation_id' => Yii::t('gromver.platform', 'Translation ID'),
            'language' => Yii::t('gromver.platform', 'Language'),
            'title' => Yii::t('gromver.platform', 'Title'),
            'alias' => Yii::t('gromver.platform', 'Alias'),
            'preview_text' => Yii::t('gromver.platform', 'Preview Text'),
            'preview_image' => Yii::t('gromver.platform', 'Preview Image'),
            'detail_text' => Yii::t('gromver.platform', 'Detail Text'),
            'detail_image' => Yii::t('gromver.platform', 'Detail Image'),
            'metakey' => Yii::t('gromver.platform', 'Meta keywords'),
            'metadesc' => Yii::t('gromver.platform', 'Meta description'),
            'created_at' => Yii::t('gromver.platform', 'Created At'),
            'updated_at' => Yii::t('gromver.platform', 'Updated At'),
            'published_at' => Yii::t('gromver.platform', 'Published At'),
            'status' => Yii::t('gromver.platform', 'Status'),
            'created_by' => Yii::t('gromver.platform', 'Created By'),
            'updated_by' => Yii::t('gromver.platform', 'Updated By'),
            'tags' => Yii::t('gromver.platform', 'Tags'),
            'ordering' => Yii::t('gromver.platform', 'Ordering'),
            'hits' => Yii::t('gromver.platform', 'Hits'),
            'lock' => Yii::t('gromver.platform', 'Lock'),
            'versionNote' => Yii::t('gromver.platform', 'Version Note')
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
            TaggableBehavior::className(),
            [
                'class' => VersioningBehavior::className(),
                'attributes'=>['title', 'alias', 'preview_text', 'detail_text', 'metakey', 'metadesc']
            ],
            [
                'class' => UploadBehavior::className(),
                'attributes' => [
                    'detail_image'=>[
                        'fileName' => '{id}-full.#extension#'
                    ],
                    'preview_image'=>[
                        'fileName' => '{id}-thumb.#extension#',
                        'process' => ThumbnailProcessor::className()
                    ]
                ],
                'options' => [
                    'basePath' => '@frontend/web',
                    'baseUrl' => '',
                    'savePath'=>'upload/posts'
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && $this->translation_id === null) {
            $this->updateAttributes([
                'translation_id' => $this->id
            ]);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getViewers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('{{%grom_post_viewed}}', ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    private static $_statuses = [
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_UNPUBLISHED => 'Unpublished',
    ];

    public static function statusLabels()
    {
        return array_map(function($label) {
                return Yii::t('gromver.platform', $label);
            }, self::$_statuses);
    }

    public function getStatusLabel($status=null)
    {
        if ($status === null) {
            return Yii::t('gromver.platform', self::$_statuses[$this->status]);
        }
        return Yii::t('gromver.platform', self::$_statuses[$status]);
    }


    /**
     * @inheritdoc
     * @return PostQuery
     */
    public static function find()
    {
        return new PostQuery(get_called_class());
    }


    public function optimisticLock()
    {
        return 'lock';
    }

    public function hit()
    {
        return $this->updateAttributes(['hits' => $this->hits + 1]);
    }

    public function getDayLink()
    {
        return ['/grom/news/post/day', 'category_id' => $this->category_id, 'year' => date('Y', $this->published_at), 'month' => date('m', $this->published_at), 'day' => date('j', $this->published_at)];
    }
    //ViewableInterface
    /**
     * @inheritdoc
     */
    public function getViewLink()
    {
        return ['/grom/news/post/view', 'id' => $this->id, 'alias' => $this->alias, 'category_id' => $this->category_id, UrlManager::LANGUAGE_PARAM => $this->getLanguage()];
    }
    /**
     * @inheritdoc
     */
    public static function viewLink($model)
    {
        return ['/grom/news/post/view', 'id' => $model['id'], 'alias' => $model['alias'], 'category_id' => $model['category_id'], UrlManager::LANGUAGE_PARAM => $model['language']];
    }

    //translatable interface
    public function getTranslations()
    {
        return self::hasMany(self::className(), ['translation_id' => 'translation_id'])->indexBy('language');
    }

    public function getLanguage()
    {
        //return $this->category ? $this->category->language : '';
        return $this->language;
    }

    public function extraFields()
    {
        return [
            //'language',
            'published',
            'tags' => function($model) {
                    return array_values(array_map(function ($tag) {
                        return $tag->title;
                    }, $model->tags));
                },
            'text' => function($model) {
                    /** @var self $model */
                    return strip_tags($model->preview_text . "\n" . $model->detail_text);
                },
            'date' => function($model) {
                    /** @var self $model */
                    return date(DATE_ISO8601, $model->published_at);
                },
        ];
    }

    public function getPublished()
    {
        return $this->status == self::STATUS_PUBLISHED;
    }
}
