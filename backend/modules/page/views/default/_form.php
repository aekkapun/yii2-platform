<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model gromver\cmf\common\models\Page */
/* @var $sourceModel gromver\cmf\common\models\Page */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="page-form">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal'
    ]); ?>

    <?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => 1024, 'placeholder' => isset($sourceModel) ? $sourceModel->title : null]) ?>

    <?= $form->field($model, 'alias')->textInput(['maxlength' => 255, 'placeholder' => Yii::t('gromver.cmf', 'Auto-generate')]) ?>

    <?= $form->field($model, 'versionNote')->textInput() ?>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#main-options" data-toggle="tab"><?= Yii::t('gromver.cmf', 'Main') ?></a></li>
        <li><a href="#advanced-options" data-toggle="tab"><?= Yii::t('gromver.cmf', 'Advanced') ?></a></li>
        <li><a href="#meta-options" data-toggle="tab"><?= Yii::t('gromver.cmf', 'Metadata') ?></a></li>
    </ul>
    <br/>
    <div class="tab-content">
        <div id="main-options" class="tab-pane active">
            <div class="form-group container">
                <?= Html::activeLabel($model, 'detail_text') ?>
                <div>
                    <?= \mihaildev\ckeditor\CKEditor::widget([
                        'model' => $model,
                        'attribute' => 'detail_text',
                        'editorOptions' => \mihaildev\elfinder\ElFinder::ckeditorOptions('cmf/media/manager', [
                            'extraPlugins' => 'codesnippet'
                        ])
                    ]) ?>
                </div>
            </div>

            <?= $form->field($model, 'language')->dropDownList(Yii::$app->getLanguagesList(), ['prompt' => Yii::t('gromver.cmf', 'Select ...')]) ?>

            <?= $form->field($model, 'status')->dropDownList($model->statusLabels()) ?>

        </div>

        <div id="advanced-options" class="tab-pane">
            <?= $form->field($model, 'preview_text')->textarea(['rows' => 6]) ?>

            <?= $form->field($model, 'tags')->widget(\dosamigos\selectize\Selectize::className(), [
                'options'=>[
                    'multiple'=>true
                ],
                'items' => \yii\helpers\ArrayHelper::map($model->tags, 'id', 'title', 'group'),
                'clientOptions' => [
                    'maxItems' => 'NaN'
                ],
                'url' => ['/cmf/tag/default/tag-list']
            ]) ?>
        </div>

        <div id="meta-options" class="tab-pane">
            <?= $form->field($model, 'metakey')->textInput(['maxlength' => 255]) ?>

            <?= $form->field($model, 'metadesc')->textarea(['maxlength' => 2048]) ?>
        </div>
    </div>

    <?= Html::activeHiddenInput($model, 'lock') ?>

    <div>
        <?= Html::submitButton($model->isNewRecord ? Yii::t('gromver.cmf', 'Create') : Yii::t('gromver.cmf', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
