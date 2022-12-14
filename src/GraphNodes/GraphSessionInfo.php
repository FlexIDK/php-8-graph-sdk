<?php

namespace One23\GraphSdk\GraphNodes;

class GraphSessionInfo extends GraphNode
{
    /**
     * Returns the application id the token was issued for.
     */
    public function getAppId(): ?string
    {
        return self::mapType(
            $this->getField('app_id'),
            'str'
        );
    }

    /**
     * Returns the application name the token was issued for.
     */
    public function getApplication(): ?string
    {
        return self::mapType(
            $this->getField('application'),
            'str'
        );
    }

    /**
     * Returns the date & time that the token expires.
     */
    public function getExpiresAt(): ?\DateTime
    {
        return self::mapType(
            $this->getField('expires_at'),
            \DateTime::class
        );
    }

    /**
     * Returns whether the token is valid.
     */
    public function getIsValid(): bool
    {
        return self::mapType(
            $this->getField('is_valid'),
            'bool'
        );
    }

    /**
     * Returns the date & time the token was issued at.
     */
    public function getIssuedAt(): ?\DateTime
    {
        return self::mapType(
            $this->getField('issued_at'),
            \DateTime::class
        );
    }

    /**
     * Returns the scope permissions associated with the token.
     */
    public function getScopes(): array
    {
        return self::mapType(
            $this->getField('scopes'),
            'arrOrBlank'
        );
    }

    /**
     * Returns the login id of the user associated with the token.
     */
    public function getUserId(): ?string
    {
        return self::mapType(
            $this->getField('user_id'),
            'str'
        );
    }
}
