<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "setting".
 *
 * @property integer $message_id
 * @property string code
 * @property integer send_fans_id
 * @property string content
 * @property integer receive_fans_id
 * @property integer create_time
 * @property integer receive_time
 * @property integer status
 */
class Message extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id','send_fans_id','receive_fans_id','create_time','receive_time','status'], 'integer'],
            [['code'], 'string', 'max' => 20],
            [['content'], 'string', 'max' => 512],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'message_id' => 'Message ID',
            'code' => 'Code',
            'send_fans_id' => 'Send Fans ID',
            'content' => 'Content',
            'receive_fans_id' => 'Receive Fans ID',
            'create_time' => 'Create Time',
            'receive_time' => 'Receive Time',
            'status' => 'Status',
        ];
    }
}
