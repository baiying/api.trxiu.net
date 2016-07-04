<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "charge_serial".
 *
 * @property string $serialno
 * @property integer $fans_id
 * @property string $openid
 * @property integer $total
 * @property integer $status
 * @property integer $create_time
 * @property integer $notify_time
 * @property integer $type
 * @property integer $ballot_id
 * @property integer $anchor_id
 */
class ChargeSerial extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'charge_serial';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['serialno', 'fans_id', 'total', 'type', 'openid'], 'required', 'on'=>'create'],
            [['fans_id', 'total', 'status', 'create_time', 'notify_time', 'type', 'ballot_id', 'anchor_id'], 'integer'],
            [['serialno'], 'string', 'max' => 20],
            [['openid', 'fail_msg'], 'string', 'max' => 128],
            [['appid', 'mch_id', 'nonce_str', 'prepay_id', 'result_code', 'return_code', 'return_msg', 'sign', 'trade_type'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'serialno' => 'Serialno',
            'fans_id' => 'Fans ID',
            'openid' => 'Openid',
            'total' => 'Total',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'notify_time' => 'Notify Time',
            'type' => 'Type',
            'ballot_id' => 'Ballot ID',
            'anchor_id' => 'Anchor ID',
        ];
    }
}
