<?php
/**
 * @link https://github.com/gromver/yii2-cmf.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/gromver/yii2-grom/blob/master/LICENSE
 * @package yii2-cmf
 * @version 1.0.0
 */

namespace gromver\platform\frontend\modules\auth\controllers;

use kartik\widgets\Alert;
use gromver\widgets\ModalIFrame;
use Yii;
use yii\di\Instance;
use yii\mail\BaseMailer;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use gromver\platform\common\models\LoginForm;
use gromver\platform\common\models\User;

/**
 * Class DefaultController
 * @package yii2-cmf
 * @author Gayazov Roman <gromver5@gmail.com>
 *
 * @property \gromver\platform\frontend\modules\auth\Module Module
 */
class DefaultController extends Controller
{
    private $loginAttemptsVar = '__LoginAttemptsCount';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['admin', '?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'height' => 42,
                'width' => 80
            ],
        ];
    }

    public function actionLogin($modal = null)
    {
        if (!\Yii::$app->user->isGuest) {
            $this->goHome();
        }

        $model = new LoginForm();

        //make the captcha required if the unsuccessful attempts are more of thee
        if ($this->getLoginAttempts() >= $this->module->attemptsBeforeCaptcha) {
            $model->scenario = 'withCaptcha';
        }

        if ($model->load($_POST)) {
            if($model->login()) {
                $this->setLoginAttempts(0); //if login is successful, reset the attempts
                if ($modal) {
                    ModalIFrame::refreshPage();
                }
                return $this->goBack();
            } else {
                //if login is not successful, increase the attempts
                $this->setLoginAttempts($this->getLoginAttempts() + 1);
                Yii::$app->session->setFlash(Alert::TYPE_DANGER, Yii::t('gromver.platform', 'Authorization is failed.'));
            }
        }

        if ($modal) {
            Yii::$app->grom->layout = 'modal';
        } else {
            $this->module->layout = $this->module->loginLayout;
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    private function getLoginAttempts()
    {
        return Yii::$app->getSession()->get($this->loginAttemptsVar, 0);
    }

    private function setLoginAttempts($value)
    {
        Yii::$app->getSession()->set($this->loginAttemptsVar, $value);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionSignup()
    {
        $model = new User();
        $model->setScenario('signup');
        if ($model->load($_POST)) {
            if ($model->save()) {
                if (Yii::$app->getUser()->login($model)) {
                    return $this->goHome();
                }
            } else {
                Yii::$app->getSession()->setFlash(Alert::TYPE_DANGER, Yii::t('gromver.platform', 'The form is filled incorrectly.'));
            }
        }

        $this->module->layout = $this->module->loginLayout;

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionRequestPasswordResetToken()
    {
        $model = new User();
        $model->scenario = 'requestPasswordResetToken';
        if ($model->load($_POST)) {
            if ($model->validate()) {
                if ($this->sendPasswordResetEmail($model->email)) {
                    Yii::$app->getSession()->setFlash(Alert::TYPE_SUCCESS, Yii::t('gromver.platform', 'Check your email for further instructions.'));
                    return $this->goHome();
                } else {
                    Yii::$app->getSession()->setFlash(Alert::TYPE_DANGER, Yii::t('gromver.platform', 'There was an error sending email.'));
                }
            } else {
                Yii::$app->session->setFlash(Alert::TYPE_DANGER, Yii::t('gromver.platform', 'Email not found.'));
            }
        }

        $this->module->layout = $this->module->loginLayout;

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    public function actionResetPassword($token)
    {
        $model = User::findOne([
            'password_reset_token' => $token,
            'status' => User::STATUS_ACTIVE,
        ]);

        if (!$model) {
            throw new BadRequestHttpException(Yii::t('gromver.platform', 'Wrong password reset token.'));
        }

        $model->scenario = 'resetPassword';
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->getSession()->setFlash(Alert::TYPE_SUCCESS, Yii::t('gromver.platform', 'New password was saved.'));
            return $this->goHome();
        }

        $this->module->layout = $this->module->loginLayout;

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    private function sendPasswordResetEmail($email)
    {
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $email,
        ]);

        if (!$user) {
            return false;
        }

        $user->password_reset_token = Yii::$app->security->generateRandomString();
        if ($user->save(false)) {
            return Yii::$app->mailer->compose('@gromver/platform/frontend/modules/auth/views/emails/passwordResetToken', ['user'=>$user])
                ->setFrom(Yii::$app->grom->params['supportEmail'], Yii::t('gromver.platform', '{name} robot', ['name' => Yii::$app->grom->siteName]))
                ->setTo($user->email)
                ->setSubject(Yii::t('gromver.platform', 'Password reset for {name}.', ['name' => Yii::$app->grom->siteName]))
                ->send();
        }

        return false;
    }
}
