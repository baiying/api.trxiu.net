<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ballot_e_anchor".
 *
 * @property integer $ballot_anchor_id
 * @property integer $ballot_id
 * @property integer $anchor_id
 * @property integer $votes
 * @property integer $votes_amend
 */
class BallotEAnchor extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ballot_e_anchor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ballot_id', 'anchor_id', 'votes', 'votes_amend', 'vote_free', 'vote_pay'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ballot_anchor_id' => 'Ballot Anchor ID',
            'ballot_id' => 'Ballot ID',
            'anchor_id' => 'Anchor ID',
            'votes' => 'Votes',
            'votes_amend' => 'Votes Amend',
        ];
    }
}
