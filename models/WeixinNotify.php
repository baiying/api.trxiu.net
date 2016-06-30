<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "weixin_notify".
 *
 * @property string $serialno
 * @property string $appid
 * @property string $mch_id
 * @property string $device_info
 * @property string $nonce_str
 * @property string $bank_type
 * @property integer $cash_fee
 * @property string $cash_fee_type
 * @property string $fee_type
 * @property integer $total_fee
 * @property string $is_subscribe
 * @property string $trade_type
 * @property string $sign
 * @property string $result_code
 * @property string $err_code
 * @property string $err_code_des
 * @property string $openid
 * @property integer $coupon_fee
 * @property integer $coupon_count
 * @property string $transaction_id
 * @property string $out_trade_no
 * @property string $attach
 * @property string $time_end
 */
class WeixinNotify extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'weixin_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['serialno'], 'required', 'on'=>'create'],
            [['cash_fee', 'total_fee', 'coupon_fee', 'coupon_count'], 'integer'],
            [['serialno'], 'string', 'max' => 20],
            [['appid', 'mch_id', 'device_info', 'nonce_str', 'sign', 'err_code', 'transaction_id', 'out_trade_no'], 'string', 'max' => 32],
            [['bank_type', 'cash_fee_type', 'result_code', 'return_code'], 'string', 'max' => 16],
            [['fee_type'], 'string', 'max' => 8],
            [['is_subscribe'], 'string', 'max' => 1],
            [['trade_type'], 'string', 'max' => 10],
            [['err_code_des', 'openid', 'attach', 'return_msg'], 'string', 'max' => 128],
            [['time_end'], 'string', 'max' => 14],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'serialno' => 'Serialno',
            'appid' => 'Appid',
            'mch_id' => 'Mch ID',
            'device_info' => 'Device Info',
            'nonce_str' => 'Nonce Str',
            'bank_type' => 'Bank Type',
            'cash_fee' => 'Cash Fee',
            'cash_fee_type' => 'Cash Fee Type',
            'fee_type' => 'Fee Type',
            'total_fee' => 'Total Fee',
            'is_subscribe' => 'Is Subscribe',
            'trade_type' => 'Trade Type',
            'sign' => 'Sign',
            'result_code' => 'Result Code',
            'err_code' => 'Err Code',
            'err_code_des' => 'Err Code Des',
            'openid' => 'Openid',
            'coupon_fee' => 'Coupon Fee',
            'coupon_count' => 'Coupon Count',
            'transaction_id' => 'Transaction ID',
            'out_trade_no' => 'Out Trade No',
            'attach' => 'Attach',
            'time_end' => 'Time End',
        ];
    }
}
