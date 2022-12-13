<?php

namespace One23\GraphSdk\GraphNodes;

/**
 * Class GraphSessionInfo

 */
class GraphSessionInfo extends GraphNode
{
    /**
     * Returns the application id the token was issued for.
     *
     * @return string|null
     */
    public function getAppId()
    {
        return $this->getField('app_id');
    }

    /**
     * Returns the application name the token was issued for.
     *
     * @return string|null
     */
    public function getApplication()
    {
        return $this->getField('application');
    }

    /**
     * Returns the date & time that the token expires.
     *
     * @return \DateTime|null
     */
    public function getExpiresAt()
    {
        return $this->getField('expires_at');
    }

    /**
     * Returns whether the token is valid.
     *
     * @return boolean
     */
    public function getIsValid()
    {
        return $this->getField('is_valid');
    }

    /**
     * Returns the date & time the token was issued at.
     *
     * @return \DateTime|null
     */
    public function getIssuedAt()
    {
        return $this->getField('issued_at');
    }

    /**
     * Returns the scope permissions associated with the token.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->getField('scopes');
    }

    /**
     * Returns the login id of the user associated with the token.
     *
     * @return string|null
     */
    public function getUserId()
    {
        return $this->getField('user_id');
    }
}
