<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fans".
 *
 * @property integer $fans_id
 * @property string $wx_openid
 * @property string $wx_name
 * @property string $wx_thumb
 * @property integer $create_time
 */
class Fans extends \yii\db\ActiveRecord
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
            [['fans_id'], 'required'],
            [['fans_id', 'create_time'], 'integer'],
            [['wx_openid'], 'string', 'max' => 64],
            [['wx_name'], 'string', 'max' => 16],
            [['wx_thumb'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fans_id' => 'Fans ID',
            'wx_openid' => 'Wx Openid',
            'wx_name' => 'Wx Name',
            'wx_thumb' => 'Wx Thumb',
            'create_time' => 'Create Time',
        ];
    }
}
