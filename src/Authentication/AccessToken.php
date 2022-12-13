<?php

namespace One23\GraphSdk\Authentication;

class AccessToken
{
    /**
     * Date when token expires.
     */
    protected ?\DateTime $expiresAt;

    /**
     * Create a new access token entity.
     */
    public function __construct(protected string $accessToken, int $expiresAt = 0)
    {
        if ($expiresAt) {
            $this->setExpiresAtFromTimeStamp($expiresAt);
        }
    }

    /**
     * Setter for expires_at.
     */
    protected function setExpiresAtFromTimeStamp(int $timeStamp): void
    {
        $dt = new \DateTime();
        $dt->setTimestamp($timeStamp);

        $this->expiresAt = $dt;
    }

    /**
     * Generate an app secret proof to sign a request to Graph.
     */
    public function getAppSecretProof(string $appSecret): string
    {
        return hash_hmac('sha256', $this->accessToken, $appSecret);
    }

    /**
     * Determines whether or not this is a long-lived token.
     */
    public function isLongLived(): bool
    {
        if ($this->expiresAt) {
            return $this->expiresAt->getTimestamp() > time() + (60 * 60 * 2);
        }

        if ($this->isAppAccessToken()) {
            return true;
        }

        return false;
    }

    /**
     * Determines whether or not this is an app access token.
     */
    public function isAppAccessToken(): bool
    {
        return !!str_contains($this->accessToken, '|');
    }

    /**
     * Checks the expiration of the access token.
     */
    public function isExpired(): ?bool
    {
        if ($this->getExpiresAt() instanceof \DateTime) {
            return $this->getExpiresAt()->getTimestamp() < time();
        }

        if ($this->isAppAccessToken()) {
            return false;
        }

        return null;
    }

    /**
     * Getter for expiresAt.
     */
    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    /**
     * Returns the access token as a string.
     */
    public function __toString(): string
    {
        return $this->getValue();
    }

    /**
     * Returns the access token as a string.
     */
    public function getValue(): string
    {
        return $this->accessToken;
    }
}
