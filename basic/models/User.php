<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%_user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $name
 * @property string $surname
 * @property string $password
 * @property string $salt
 * @property string $access_token
 * @property string $create_date
 *
 * @property Access[] $Accesses
 * @property Access[] $Accesses0
 * @property Calendar[] $Calendars
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    const PASS_MIN_LENGTH = 6;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'name', 'surname', 'password'], 'required'],
            [['password'], 'string', 'min' => self::PASS_MIN_LENGTH],
            [['username', 'access_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => _('ID'),
            'username' => _('Логин'),
            'name' => _('Имя'),
            'surname' => _('Фамилия'),
            'password' => _('Пароль'),
            'salt' => _('Соль'),
            'access_token' => _('Ключ авторизации'),
            'create_date' => _('Дата создания'),
        ];
    }

//    /**
//     * @return \yii\db\ActiveQuery
//     */
//    public function getAccesses()
//    {
//        return $this->hasMany(Access::className(), ['user_owner' => 'id']);
//    }
//
//    /**
//     * @return \yii\db\ActiveQuery
//     */
//    public function getAccesses0()
//    {
//        return $this->hasMany(Access::className(), ['user_guest' => 'id']);
//    }
//
//    /**
//     * @return \yii\db\ActiveQuery
//     */
//    public function getCalendars()
//    {
//        return $this->hasMany(Calendar::className(), ['creator' => 'id']);
//    }
//
//    /**
//     * @inheritdoc
//     * @return \app\models\query\UserQuery the active query used by this AR class.
//     */
//    public static function find()
//    {
//        return new \app\models\query\UserQuery(get_called_class());
//    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert))
        {
            if ($this->getIsNewRecord() && !empty($this->password))
            {
                $this->salt = $this->saltGenerator();
            }
            if (!empty($this->password))
            {
                $this->password = $this->passWithSalt($this->password, $this->salt);
            }
            else
            {
                unset($this->password);
            }
            $this->generateAuthKey();
            return true;
        }
        else
        {
            return false;
        }
    }

    public function saltGenerator()
    {
        return hash("sha512", uniqid('salt_', true));
    }

    public function passWithSalt($password, $salt)
    {
        return hash("sha512", $password . $salt);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id;    }

    public function getAuthKey()
    {
        return $this->access_token;    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword ($password)
    {
        return $this->password === $this->passWithSalt($password, $this->salt);
    }

    public function setPassword ($password)
    {
        $this->password = $this->passWithSalt($password, $this->saltGenerator());
    }

    public function generateAuthKey ()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
    }

}
