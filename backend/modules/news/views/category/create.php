<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model gromver\cmf\common\models\Category */
/* @var $sourceModel gromver\cmf\common\models\Category */

$this->title = Yii::t('gromver.cmf', 'Add Category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('gromver.cmf', 'Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'sourceModel' => $sourceModel
    ]) ?>

</div>
