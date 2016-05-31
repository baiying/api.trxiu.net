<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ballot".
 *
 * @property integer $ballot_id
 * @property string $ballot_name
 * @property string $description
 * @property integer $anchor_count
 * @property integer $votes
 * @property integer $create_time
 * @property integer $begin_time
 * @property integer $end_time
 * @property integer $status
 */
class Ballot extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ballot';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['anchor_count', 'votes', 'create_time', 'begin_time', 'end_time', 'status'], 'integer'],
            [['ballot_name'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ballot_id' => 'Ballot ID',
            'ballot_name' => 'Ballot Name',
            'description' => 'Description',
            'anchor_count' => 'Anchor Count',
            'votes' => 'Votes',
            'create_time' => 'Create Time',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'status' => 'Status',
        ];
    }
}
