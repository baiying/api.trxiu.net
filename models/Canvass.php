<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "canvass".
 *
 * @property string $canvass_id
 * @property integer $ballot_id
 * @property integer $anchor_id
 * @property integer $fans_id
 * @property string $amount
 * @property string $url
 * @property integer $status
 * @property integer $create_time
 * @property integer $active_time
 * @property integer $end_time
 * @property string $refund
 */
class Canvass extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'canvass';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['canvass_id', 'ballot_id', 'anchor_id', 'fans_id', 'charge'], 'required', 'on'=>'create'],
            [['ballot_id', 'anchor_id', 'fans_id', 'status', 'create_time', 'active_time', 'end_time'], 'integer'],
            [['amount', 'refund', 'charge', 'fee'], 'number'],
            [['canvass_id'], 'string', 'max' => 20],
            [['url'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'canvass_id' => 'Canvass ID',
            'ballot_id' => 'Ballot ID',
            'anchor_id' => 'Anchor ID',
            'fans_id' => 'Fans ID',
            'amount' => 'Amount',
            'url' => 'Url',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'active_time' => 'Active Time',
            'end_time' => 'End Time',
            'refund' => 'Refund',
        ];
    }
    /**
     * 获取拉票全部红包信息
     * @return ActiveQuery|multitype:
     */
    public function getReds() {
        $res = $this->hasOne(CanvassRed::className(), ['canvass_id' => 'canvass_id']);
        if(!empty($res)) {
            return $res;
        }
        return [];
    }
    /**
     * 获取未被领取的红包
     */
    public function getUnreceiveReds() {
        return CanvassRed::find()->where(['canvass_id'=>$this->canvass_id, 'fans_id'=>0])->all();
    }
    /**
     * 获取当前手气最佳红包记录
     * 如果还没有手气最佳，则返回0
     */
    public function getBestAmount() {
        $res = CanvassRed::find()->where(['canvass_id'=>$this->canvass_id, 'best'=>1])->one();
        return empty($res) ? 0 : $res;
    }
}
