<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\captcha\Captcha;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var gromver\cmf\common\models\LoginForm $model
 */
$this->title = \Yii::t('auth.user', 'Signup');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login center-block col-lg-3 col-md-4 col-sm-6" style="float:none;">
	<div class="form-signup-heading">
		<h1><?= Html::encode($this->title) ?></h1>
	</div>

	<?php $form = ActiveForm::begin([
		'id' => 'login-form',
		'options' => ['class' => 'form-horizontal', 'autocomplete'=>'off'],
		'fieldConfig' => [
			'template' => "{input}",
			'labelOptions' => ['class' => 'col-lg-1 control-label'],
		],
	]); ?>

	<?= $form->field($model, 'username', ['options' => ['class' => 'form-group input-group input-group-lg'], 'template' => '<span class="input-group-addon"><i class=" glyphicon glyphicon-user"></i></span>{input}'])->textInput(['placeholder' => $model->getAttributeLabel('username')]) ?>

    <?= $form->field($model, 'email', ['options' => ['class' => 'form-group input-group input-group-lg'], 'template' => '<span class="input-group-addon"><i class=" glyphicon glyphicon-envelope"></i></span>{input}'])->textInput(['placeholder' => $model->getAttributeLabel('email'), 'type' => 'email', 'autocomplete'=>'off']) ?>

    <?= $form->field($model, 'password', ['options' => ['class' => 'form-group input-group input-group-lg'], 'template' => '<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>{input}'])->passwordInput(['placeholder' => $model->getAttributeLabel('password'), 'autocomplete'=>'off']) ?>
	<?php if ($model->scenario == 'withCaptcha'): ?>
		<?=
		$form->field($model, 'verifyCode')->widget(Captcha::className(), ['captchaAction' => 'default/captcha', 'options' => ['class' => 'form-control'],]) ?>
	<?php endif; ?>

	<?//= $form->field($model, 'rememberMe')->checkbox() ?>

	<div class="form-group">
		<div class="text-center">
			<?= Html::submitButton(\Yii::t('auth.user', 'Login'), ['class' => 'btn btn-primary btn-lg btn-block']) ?>
		</div>
	</div>

	<?php ActiveForm::end(); ?>
</div>
