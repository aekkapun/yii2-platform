<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use gromver\cmf\common\models\Category;

/**
 * @var yii\web\View $this
 * @var gromver\cmf\common\models\Category $model
 * @var gromver\cmf\common\models\Category $sourceModel
 * @var yii\bootstrap\ActiveForm $form
 */
?>

<div class="category-form">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'language')->dropDownList(Yii::$app->getLanguagesList(), ['prompt' => Yii::t('gromver.cmf', 'Select...'), 'id' => 'language']) ?>

    <?= $form->field($model, 'parent_id')->widget(\kartik\widgets\DepDrop::className(), [
        'pluginOptions' => [
            //'initialize' => true,
            'depends' => ['language'],
            'placeholder' => Yii::t('gromver.cmf', 'Select...'),
            'url' => \yii\helpers\Url::to(['categories', 'update_item_id' => $model->isNewRecord ? null : $model->id, 'selected' => $model->parent_id]),
        ]
    ]) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => 1000, 'placeholder' => isset($sourceModel) ? $sourceModel->title : null]) ?>

    <?= $form->field($model, 'alias')->textInput(['maxlength' => 255, 'placeholder' => Yii::t('gromver.cmf', 'Auto-generate')]) ?>

    <?//= $form->field($model, 'path')->textInput(['maxlength' => 2000]) ?>

    <?= $form->field($model, 'status')->dropDownList(['' => Yii::t('gromver.cmf', 'Select...')] + $model->statusLabels()) ?>

    <?= $form->field($model, 'published_at')->widget(\kartik\widgets\DateTimePicker::className(), [
        'options' => ['value' => date('d.m.Y H:i', is_int($model->published_at) ? $model->published_at : time())],
        'pluginOptions' => [
            'format' =>  'dd.mm.yyyy hh:ii',
            'autoclose' => true,
        ]
    ]) ?>

    <?= $form->field($model, 'ordering')->textInput() ?>

    <?= $form->field($model, 'preview_text')->textarea(['rows' => 6]) ?>

    <div class="form-group container">
        <?= Html::activeLabel($model, 'detail_text') ?>
        <div>
            <?= \mihaildev\ckeditor\CKEditor::widget([
                'model' => $model,
                'attribute' => 'detail_text',
                'editorOptions' => \mihaildev\elfinder\ElFinder::ckeditorOptions('cmf/media/manager')
            ]) ?>
        </div>
    </div>

    <?= $form->field($model, 'versionNote')->textInput() ?>

    <?= $form->field($model, 'metakey')->textarea(['maxlength' => 255]) ?>

    <?= $form->field($model, 'metadesc')->textarea(['maxlength' => 2000]) ?>

    <?= $form->field($model, 'tags')->widget(\dosamigos\selectize\Selectize::className(), [
        'options' => [
            'multiple' => true
        ],
        'items' => \yii\helpers\ArrayHelper::map($model->tags, 'id', 'title', 'group'),
        'clientOptions' => [
            'maxItems' => 'NaN'
        ],
        'url' => ['/cmf/tag/default/tag-list']
    ]) ?>

    <?= $form->field($model, 'detail_image')->widget(\gromver\cmf\backend\widgets\FileInput::classname(), [
        'options' => ['accept' => 'image/*'],
        'pluginOptions' => ['showUpload' => false]
    ]) ?>

    <?= $form->field($model, 'preview_image')->widget(\gromver\cmf\backend\widgets\FileInput::classname(), [
        'options' => ['accept' => 'image/*'],
        'pluginOptions' => ['showUpload' => false]
    ]) ?>

    <?= Html::activeHiddenInput($model, 'lock') ?>

    <div>
        <?= Html::submitButton($model->isNewRecord ? Yii::t('gromver.cmf', 'Create') : Yii::t('gromver.cmf', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php $this->registerJs('$("#language").change()', \yii\web\View::POS_READY);