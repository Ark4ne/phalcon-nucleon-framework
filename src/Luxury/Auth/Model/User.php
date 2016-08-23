<?php

namespace Luxury\Auth\Model;

use Luxury\Auth\Authenticable;
use Luxury\Auth\Authorizable;
use Phalcon\Acl\RoleInterface;
use Phalcon\Mvc\Model;

/**
 * Class User
 *
 * @package Luxury\Auth\Model
 */
class User extends Model implements Authenticable, Authorizable
{
    /**
     * User Role.
     *
     * @var RoleInterface
     */
    private $role;

    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifier() : string
    {
        return $this->{static::getAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword() : string
    {
        return $this->{static::getAuthPasswordName()};
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken() : string
    {
        return $this->{static::getRememberTokenName()};
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     *
     * @return void
     */
    public function setRememberToken(string $value)
    {
        $this->{static::getRememberTokenName()} = $value;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public static function getAuthIdentifierName() : string
    {
        return 'email';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public static function getAuthPasswordName() : string
    {
        return 'password';
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public static function getRememberTokenName() : string
    {
        return 'remember_token';
    }

    /**
     * @return RoleInterface
     */
    public function getRole() : RoleInterface
    {
        return $this->role;
    }

    /**
     * @param RoleInterface $role
     *
     * @return Authorizable
     */
    public function setRole(RoleInterface $role) : Authorizable
    {
        $this->role = $role;

        return $this;
    }
}