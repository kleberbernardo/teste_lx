<?php
/**
 * Playlist ActiveRecord
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $name
 * @property string $description
 * @property string $cover_color
 * @property string $created_at
 * @property string $updated_at
 */
class Playlist extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'playlists';
    }

    public function rules()
    {
        return array(
            array('name, user_id', 'required'),
            array('name', 'length', 'max' => 200),
            array('cover_color', 'match', 'pattern' => '/^#[0-9A-Fa-f]{6}$/'),
            array('description', 'safe'),
        );
    }

    public function relations()
    {
        return array(
            'user'  => array(self::BELONGS_TO, 'User', 'user_id'),
            'tracks' => array(
                self::MANY_MANY,
                'Track',
                'playlist_tracks(playlist_id, track_id)'
            ),
            'playlistTracks' => array(self::HAS_MANY, 'PlaylistTrack', 'playlist_id'),
        );
    }

    /**
     * Conta o número de tracks na playlist.
     */
    public function getTrackCount()
    {
        return (int) Yii::app()->db->createCommand()
            ->select('COUNT(*)')
            ->from('playlist_tracks')
            ->where('playlist_id = :id', array(':id' => (int) $this->id))
            ->queryScalar();
    }

    public function toArray()
    {
        return array(
            'id'          => (int) $this->id,
            'user_id'     => (int) $this->user_id,
            'name'        => $this->name,
            'description' => $this->description,
            'cover_color' => $this->cover_color,
            'track_count' => $this->getTrackCount(),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        );
    }
}
