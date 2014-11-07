<?php
/**
 * @link https://github.com/gromver/yii2-cms.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-cmf/blob/master/LICENSE
 * @package yii2-cms
 * @version 1.0.0
 */

namespace gromver\cmf\backend\behaviors;

use gromver\cmf\common\models\Tag;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class TaggableBehavior
 * @package yii2-cms
 * @author Gayazov Roman <gromver5@gmail.com>
 *
 * @property \yii\db\ActiveRecord $owner
 */
class TaggableBehavior extends \yii\base\Behavior
{
    private $_tags;

    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * @param $value array массив с айдишниками тегов
     */
    public function setTags($value)
    {
        $this->_tags = empty($value) ? [] : $value;
    }

    /**
     * Tags relation
     * @return static
     */
    public function getTags()
    {
        return $this->owner->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable(Tag::pivotTableName(), ['item_id' => 'id'], function($query) {
            /** @var $query ActiveQuery */
            $query->andWhere(['item_class' => $query->modelClass]);
        })->indexBy('id');
    }

    /**
     * @param $event \Yii\base\Event
     */
    public function afterSave($event)
    {
        $this->owner->getDb()->transaction(function($db) use ($event){
            if(isset($this->_tags) && is_array($this->_tags)) {
                $oldTags = ArrayHelper::map($this->owner->tags, 'id', 'id');
                $this->owner->setIsNewRecord(false);

                $toAppend = array_diff($this->_tags, $oldTags);
                $toRemove = array_diff($oldTags, $this->_tags);

                foreach($toAppend as $id) {
                    $tag = Tag::findOne($id);
                    $this->owner->link('tags', $tag, ['item_class'=>$this->owner->className()]);
                }
                if($event->name==BaseActiveRecord::EVENT_AFTER_UPDATE)
                    foreach($toRemove as $id) {
                        $tag = Tag::findOne($id);
                        $this->owner->unlink('tags', $tag, true);
                    }
            }
        });
    }

    public function afterDelete()
    {
        $this->owner->unlinkAll('tags', true);
    }
}