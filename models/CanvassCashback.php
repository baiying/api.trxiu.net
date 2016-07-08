<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "canvass_cashback".
 *
 * @property integer $cash_id
 * @property string $canvass_id
 * @property integer $fans_id
 * @property integer $amount
 * @property integer $create_time
 * @property integer $send_time
 * @property integer $status
 * @property integer $err_count
 * @property string $err_msg
 */
class CanvassCashback extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'canvass_cashback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['canvass_id', 'fans_id', 'amount'], 'required', 'on'=>'create'],
            [['fans_id', 'amount', 'create_time', 'send_time', 'status', 'err_count'], 'integer'],
            [['canvass_id'], 'string', 'max' => 20],
            [['err_msg'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cash_id' => 'Cash ID',
            'canvass_id' => 'Canvass ID',
            'fans_id' => 'Fans ID',
            'amount' => 'Amount',
            'create_time' => 'Create Time',
            'send_time' => 'Send Time',
            'status' => 'Status',
            'err_count' => 'Err Count',
            'err_msg' => 'Err Msg',
        ];
    }
}
