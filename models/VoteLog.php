<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vote_log".
 *
 * @property string $vote_id
 * @property integer $ballot_id
 * @property integer $anchor_id
 * @property integer $fans_id
 * @property integer $create_time
 * @property string $canvass_id
 * @property string $earn
 * @property integer $new_fans
 */
class VoteLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vote_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vote_id', 'ballot_id', 'anchor_id', 'fans_id'], 'required', 'on'=>'create'],
            [['ballot_id', 'anchor_id', 'fans_id', 'create_time', 'new_fans'], 'integer'],
            [['earn'], 'number'],
            [['vote_id', 'canvass_id'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'vote_id' => 'Vote ID',
            'ballot_id' => 'Ballot ID',
            'anchor_id' => 'Anchor ID',
            'fans_id' => 'Fans ID',
            'create_time' => 'Create Time',
            'canvass_id' => 'Canvass ID',
            'earn' => 'Earn',
            'new_fans' => 'New Fans',
        ];
    }
    /**
     * 获取粉丝信息
     * @return Ambigous <\yii\db\static, NULL>
     */
    public function getFans() {
        return Fans::findOne(['fans_id'=>$this->fans_id]);
    }
}
