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
class Canvass extends \yii\db\ActiveRecord
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
            [['canvass_id'], 'required'],
            [['ballot_id', 'anchor_id', 'fans_id', 'status', 'create_time', 'active_time', 'end_time'], 'integer'],
            [['amount', 'refund'], 'number'],
            [['canvass_id'], 'string', 'max' => 26],
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
}
