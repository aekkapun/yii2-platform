<?php
/**
 * @link https://github.com/menst/yii2-cms.git#readme
 * @copyright Copyright (c) Gayazov Roman, 2014
 * @license https://github.com/menst/yii2-cms/blob/master/LICENSE
 * @package yii2-cms
 * @version 1.0.0
 */

namespace menst\cms\backend\modules\user\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use menst\cms\common\models\User;

/**
 * Class UserSearch represents the model behind the search form about `menst\cms\common\models\User`.
 * @package yii2-cms
 * @author Gayazov Roman <m.e.n.s.t@yandex.ru>
 */
class UserSearch extends User
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at', 'deleted_at', 'last_visit_at'], 'integer'],
            [['username', 'email', 'password_hash', 'password_reset_token', 'auth_key', 'roles'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['updated_at' => SORT_DESC]
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            $query->andWhere('status!=:status', [':status' => User::STATUS_DELETED]);

            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'last_visit_at' => $this->last_visit_at,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key]);

        //по умолчанию скрываем удаленыых пользователей
        if (strlen($this->status) === 0) {
            $query->andWhere('status!=:status', [':status' => User::STATUS_DELETED]);
        }

        if (count($this->getRoles())) {
            $auth = Yii::$app->authManager;

            $query->leftJoin($auth->assignmentTable.' roles', 'roles.user_id=id');
            $query->andWhere(['roles.item_name'=>$this->getRoles()]);
        }

        return $dataProvider;
    }
}
