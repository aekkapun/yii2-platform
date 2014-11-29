<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model gromver\platform\common\models\Version */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="history-form">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal'
    ]); ?>

    <?= $form->field($model, 'version_note')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'keep_forever')->dropDownList(['No', 'Yes']) ?>

    <div>
        <?= Html::submitButton($model->isNewRecord ? Yii::t('gromver.platform', 'Create') : Yii::t('gromver.platform', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
