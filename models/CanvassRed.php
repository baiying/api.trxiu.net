<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "canvass_red".
 *
 * @property integer $red_id
 * @property string $canvass_id
 * @property string $amount
 * @property integer $fans_id
 * @property integer $receive_time
 */
class CanvassRed extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'canvass_red';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['canvass_id', 'amount'], 'required', 'on'=>'create'],
            [['amount'], 'number'],
            [['fans_id', 'receive_time', 'status', 'send_time'], 'integer'],
            [['canvass_id'], 'string', 'max' => 20],
            [['err_msg'], 'string', 'max'=>64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'red_id' => 'Red ID',
            'canvass_id' => 'Canvass ID',
            'amount' => 'Amount',
            'fans_id' => 'Fans ID',
            'receive_time' => 'Receive Time',
        ];
    }
}
