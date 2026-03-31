<?php
/**
 * PlaylistTrack — tabela pivot playlist_tracks
 *
 * @property int    $id
 * @property int    $playlist_id
 * @property int    $track_id
 * @property int    $position
 * @property string $added_at
 */
class PlaylistTrack extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'playlist_tracks';
    }

    public function rules()
    {
        return array(
            array('playlist_id, track_id', 'required'),
            array('playlist_id, track_id, position', 'numerical', 'integerOnly' => true),
        );
    }

    public function relations()
    {
        return array(
            'playlist' => array(self::BELONGS_TO, 'Playlist', 'playlist_id'),
            'track'    => array(self::BELONGS_TO, 'Track', 'track_id'),
        );
    }
}
