<?php
/**
 * User ActiveRecord
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 */
class User extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'users';
    }

    public function rules()
    {
        return array(
            array('name, email', 'required'),
            array('email', 'email'),
            array('name', 'length', 'max' => 120),
            array('email', 'length', 'max' => 191),
            array('password', 'length', 'max' => 255),
        );
    }

    public function relations()
    {
        return array(
            'playlists' => array(self::HAS_MANY, 'Playlist', 'user_id'),
        );
    }

    /**
     * Verifica a senha em texto plano contra o hash armazenado.
     */
    public function validatePassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Retorna os dados públicos do usuário (sem o password).
     */
    public function toArray()
    {
        return array(
            'id'         => (int) $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'created_at' => $this->created_at,
        );
    }
}
