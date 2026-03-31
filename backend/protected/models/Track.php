<?php
/**
 * Track ActiveRecord
 *
 * @property int    $id
 * @property string $title
 * @property string $artist
 * @property string $album
 * @property int    $duration_s
 * @property string $created_at
 */
class Track extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'tracks';
    }

    public function rules()
    {
        return array(
            array('title, artist', 'required'),
            array('title, artist, album', 'length', 'max' => 255),
            array('duration_s', 'numerical', 'integerOnly' => true, 'min' => 0),
        );
    }

    /**
     * Formata duração em mm:ss.
     */
    public function getDurationFormatted()
    {
        $s = (int) $this->duration_s;
        return sprintf('%d:%02d', floor($s / 60), $s % 60);
    }

    public function toArray()
    {
        return array(
            'id'                 => (int) $this->id,
            'title'              => $this->title,
            'artist'             => $this->artist,
            'album'              => $this->album,
            'duration_s'         => (int) $this->duration_s,
            'duration_formatted' => $this->getDurationFormatted(),
        );
    }
}
