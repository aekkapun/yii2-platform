<?php
/**
 * @link https://github.com/gromver/yii2-cms.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-cmf/blob/master/LICENSE
 * @package yii2-cms
 * @version 1.0.0
 */

namespace gromver\cmf\backend\widgets;

use kartik\helpers\Html;
use kartik\widgets\FileInput as KartikFileInput;
use kartik\widgets\FileInputAsset;
use yii\widgets\InputWidget;

/**
 * Class FileInput
 * Обертка для KartikFileInput, отображает кнопку удалить пикчу, а также саму пикчу, используя UploadBehavior
 * @package yii2-cms
 * @author Gayazov Roman <gromver5@gmail.com>
 */
class FileInput extends InputWidget {
    public $deleteAction = 'delete-file';
    public $pluginOptions = [];

    public function run()
    {
        if ($imageUrl = $this->model->getFileUrl($this->attribute)) { ?>
            <div class="file-input">
                <div class="file-preview">
                    <div class="file-preview-thumbnails">
                        <div class="file-preview-frame">
                            <?=Html::img($imageUrl, ['class' => 'file-preview-image', 'alt' => \Yii::t('menst.cms', 'File not found.')])?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        <?php }

        if ($imageUrl == null) { // to show FileInput only for new upload
            echo KartikFileInput::widget([
                'model' => $this->model,
                'attribute' => $this->attribute,
                'options' => $this->options,
                'pluginOptions' => $this->pluginOptions
            ]);
        } else {
            FileInputAsset::register($this->getView()) ?>
            <div class="file-input">
                <div class="input-group">
                    <div class="form-control file-caption ">
                        <span class="glyphicon glyphicon-file"></span> <span class="file-caption-name"><?= $this->model->getFileName($this->attribute) ?></span>
                    </div>
                    <div class="input-group-btn">
                        <?= Html::a('Remove Image', [$this->deleteAction, 'pk' => $this->model->getPrimaryKey(), 'attribute' => $this->attribute], [
                            'class' => 'btn btn-danger',
                            'data-method' => 'post'
                        ]) ?>
                    </div>
                </div>

            </div>
        <? }
    }
} 