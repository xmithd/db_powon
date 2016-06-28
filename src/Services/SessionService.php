<?php

namespace Powon\Services;

/**
 * Class SessionService
 * @package Powon\services
 */
interface SessionService
{
    const SESSION_ACTIVE = 1;
    const SESSION_EXPIRED = 2;
    const SESSION_DOES_NOT_EXIST= 3;
    
    /**
     * Loads a session for the current request.
     * @param string $token An authentication token as generated by the @link \Powon\Utils\Token.
     * @return int (1: SESSION_ACTIVE, 2: SESSION_EXPIRED, 3: SESSION_DOES_NOT_EXIST)
     */
    public function loadSession($token);

    /**
     * Sets the expiration time of the unused tokens.
     * Tokens not used after this time will become invalid or garbage collected.
     * @param int $seconds
     * @return void
     */
    public function setExpiration($seconds);

    /**
     * @return int The time in seconds after the last access when the token expires.
     */
    public function getTokenValidityPeriod();

    /**
     * Authenticates a user by username.
     * @param string $username
     * @param string $password
     * @return bool true on success, false otherwise
     */
    public function authenticateUserByUsername($username, $password);

    /**
     * @param string $email
     * @param string $password
     * @return bool true on success, false otherwise
     */
    public function authenticateUserByEmail($email, $password);

    /**
     * Returns true if a user is authenticated.
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Tells whether the currently authenticated member is an admin.
     * @return bool
     */
    public function isAdmin();

    /**
     * Gets the currently authenticated member
     * @return \Powon\Entity\Member|null
     */
    public function getAuthenticatedMember();
    
    /**
     * Saves the current session in the database
     * @return bool
     */
    public function saveSession();

    /**
     * Gets the current Session entity. 
     * @return \Powon\Entity\Session|null
     */
    public function getSession();

    /**
     * Destroys the current session (if any)
     * from the database.
     * @return bool True on success, false otherwise.
     */
    public function destroySession();

    /**
     * Destroys all sessions for the current user.
     * @return bool True on success, false otherwise.
     */
    public function destroyAllSessions();
    
    /**
     * Eliminates expired sessions.
     */
    public function garbageCollect();

    /**
     * Sets a key to use when generating tokens.
     * @param string $newKey
     * @return void
     */
    public function setKey($newKey);

    /**
     * To properly expire the browser cookie, the middleware must know
     * if the user has logged out in this request.
     * @return bool
     */
    public function userHasJustLoggedOut();

}