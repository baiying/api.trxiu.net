<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "manager".
 *
 * @property integer $manager_id
 * @property string $username
 * @property string $password
 * @property string $mobile
 * @property string $auth_token
 * @property string $real_name
 * @property integer $create_time
 * @property integer $login_time
 * @property integer $status
 */
class Manager extends \yii\db\ActiveRecord implements IdentityInterface {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'manager';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['create_time', 'login_time', 'status'], 'integer'],
            [['username'], 'string', 'max' => 32],
            [['password'], 'string', 'max' => 128],
            [['mobile'], 'string', 'max' => 11],
            [['auth_token'], 'string', 'max' => 64],
            [['real_name'], 'string', 'max' => 8],

            [['username', 'password'], 'required', 'on'=>'create'],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/', 'message'=>'用户名只能使用英文、数字、中文', 'on'=>'register'],
            ['mobile', 'match', 'pattern'=>'/^1\d{10}$/', 'message'=>'手机号码格式错误'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'manager_id' => 'Manager ID',
            'username' => 'Username',
            'password' => 'Password',
            'mobile' => 'Mobile',
            'auth_token' => 'Auth Token',
            'real_name' => 'Real Name',
            'create_time' => 'Create Time',
            'login_time' => 'Login Time',
            'status' => 'Status',
        ];
    }

    public static function findIdentity($id){
        return static::findOne(['manager_id'=>$id, 'status'=>1]);
    }

    public static function findIdentityByAccessToken($token, $type = null){
        return static::findOne(['auth_token'=>$token, 'status'=>1]);
    }

    public function getId(){
        return $this->getPrimaryKey();
    }

    public function getAuthKey(){
        return $this->auth_token;
    }

    public function validateAuthKey($authKey){
        return $this->getAuthKey() === $authKey;
    }

    public static function generateAuthKey() {
        return Yii::$app->security->generateRandomString();
    }

    public static function setPassword($password) {
        return Yii::$app->security->generatePasswordHash($password);
    }
    
    public function validatePassword($password){
        return Yii::$app->security->validatePassword($password, $this->password);
    }
}
