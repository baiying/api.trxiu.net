<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fans".
 *
 * @property integer $fans_id
 * @property integer $anchor_id
 * @property string $wx_openid
 * @property string $wx_name
 * @property integer $wx_sex
 * @property string $wx_thumb
 * @property string $wx_access_token
 * @property string $wx_refresh_token
 * @property integer $wx_access_token_expire
 * @property string $wx_continue
 * @property string $wx_province
 * @property string $wx_city
 * @property integer $last_time
 * @property integer $create_time
 */
class Fans extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fans';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wx_openid', 'wx_name', 'wx_thumb', 'wx_access_token', 'wx_refresh_token'], 'required', 'on'=>'create'],
            [['anchor_id', 'wx_sex', 'wx_access_token_expire', 'last_time', 'create_time'], 'integer'],
            [['wx_openid', 'wx_country'], 'string', 'max' => 64],
            [['wx_name'], 'string', 'max' => 32],
            [['wx_thumb'], 'string', 'max' => 256],
            [['wx_access_token', 'wx_refresh_token'], 'string', 'max' => 512],
            [['wx_province', 'wx_city', 'wx_unionid'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fans_id' => 'Fans ID',
            'anchor_id' => 'Anchor ID',
            'wx_openid' => 'Wx Openid',
            'wx_name' => 'Wx Name',
            'wx_sex' => 'Wx Sex',
            'wx_thumb' => 'Wx Thumb',
            'wx_access_token' => 'Wx Access Token',
            'wx_refresh_token' => 'Wx Refresh Token',
            'wx_access_token_expire' => 'Wx Access Token Expire',
            'wx_continue' => 'Wx Continue',
            'wx_province' => 'Wx Province',
            'wx_city' => 'Wx City',
            'last_time' => 'Last Time',
            'create_time' => 'Create Time',
        ];
    }
}
