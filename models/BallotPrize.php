<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ballot_prize".
 *
 * @property integer $prize_id
 * @property integer $ballot_id
 * @property string $level
 * @property integer $sort
 * @property string $title
 * @property string $logo
 * @property string $image
 * @property integer $anchor_id
 * @property integer $create_time
 */
class BallotPrize extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ballot_prize';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ballot_id', 'sort', 'level', 'title', 'logo', 'image'], 'required', 'on'=>'create'],
            [['ballot_id', 'sort', 'anchor_id', 'create_time'], 'integer'],
            [['level'], 'string', 'max' => 8],
            [['title'], 'string', 'max' => 32],
            [['logo', 'image'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prize_id' => 'Prize ID',
            'ballot_id' => 'Ballot ID',
            'level' => 'Level',
            'sort' => 'Sort',
            'title' => 'Title',
            'logo' => 'Logo',
            'image' => 'Image',
            'anchor_id' => 'Anchor ID',
            'create_time' => 'Create Time',
        ];
    }
}
