<?php

namespace One23\GraphSdk\Authentication;

use One23\GraphSdk\Exceptions\FacebookSDKException;

/**
 * Class AccessTokenMetadata
 *
 * Represents metadata from an access token.
 *
 * @see     https://developers.facebook.com/docs/graph-api/reference/debug_token
 */
class AccessTokenMetadata
{
    /**
     * Properties that should be cast as DateTime objects.
     */
    protected static array $dateProperties = ['expires_at', 'issued_at'];
    /**
     * The access token metadata.
     */
    protected array $metadata = [];

    /**
     * @throws FacebookSDKException
     */
    public function __construct(array $metadata)
    {
        if (!isset($metadata['data'])) {
            throw new FacebookSDKException('Unexpected debug token response data.', 401);
        }

        $this->metadata = $metadata['data'];

        $this->castTimestampsToDateTime();
    }

    /**
     * Casts the unix timestamps as DateTime entities.
     */
    private function castTimestampsToDateTime()
    {
        foreach (static::$dateProperties as $key) {
            if (isset($this->metadata[$key]) && $this->metadata[$key] !== 0) {
                $this->metadata[$key] = $this->convertTimestampToDateTime($this->metadata[$key]);
            }
        }
    }

    /**
     * Converts a unix timestamp into a DateTime entity.
     *
     * @param int $timestamp
     *
     * @return \DateTime
     */
    private function convertTimestampToDateTime($timestamp)
    {
        $dt = new \DateTime();
        $dt->setTimestamp($timestamp);

        return $dt;
    }

    /**
     * Name of the application this access token is for.
     */
    public function getApplication(): ?string
    {
        $application = $this->getField('application');

        return $application
            ? (string)$application
            : null;
    }

    /**
     * Returns a value from the metadata.
     */
    public function getField(string $field, mixed $default = null): mixed
    {
        return $this->metadata[$field] ?? $default;
    }

    /**
     * Any error that a request to the graph api
     * would return due to the access token.
     */
    public function isError(): bool
    {
        return !is_null($this->getField('error'));
    }

    /**
     * The error code for the error.
     */
    public function getErrorCode(): ?int
    {
        $code = $this->getErrorProperty('code');

        return is_numeric($code)
            ? (int)$code
            : null;
    }

    /**
     * Returns a value from the error metadata.
     */
    public function getErrorProperty(string $field, mixed $default = null): mixed
    {
        return $this->getChildProperty('error', $field, $default);
    }

    /**
     * Returns a value from a child property in the metadata.
     */
    public function getChildProperty(string $parentField, string $field, mixed $default = null): mixed
    {
        if (!isset($this->metadata[$parentField])) {
            return $default;
        }

        if (!isset($this->metadata[$parentField][$field])) {
            return $default;
        }

        return $this->metadata[$parentField][$field];
    }

    /**
     * The error message for the error.
     */
    public function getErrorMessage(): ?string
    {
        $message = $this->getErrorProperty('message');

        return $message
            ? (string)$message
            : null;
    }

    /**
     * The error subcode for the error.
     */
    public function getErrorSubcode(): ?int
    {
        $subcode = $this->getErrorProperty('subcode');

        return is_numeric($subcode)
            ? (int)$subcode
            : null;
    }

    /**
     * Whether the access token is still valid or not.
     */
    public function getIsValid(): bool
    {
        return !!($this->getField('is_valid'));
    }

    /**
     * DateTime when this access token was issued.
     *
     * Note that the issued_at field is not returned
     * for short-lived access tokens.
     *
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#debug
     *
     * @return \DateTime|null
     */
    public function getIssuedAt()
    {
        return $this->getField('issued_at');
    }

    /**
     * General metadata associated with the access token.
     * Can contain data like 'sso', 'auth_type', 'auth_nonce'.
     *
     * @return array|null
     */
    public function getMetadata()
    {
        return $this->getField('metadata');
    }

    /**
     * The 'sso' child property from the 'metadata' parent property.
     *
     * @return string|null
     */
    public function getSso()
    {
        return $this->getMetadataProperty('sso');
    }

    /**
     * Returns a value from the "metadata" metadata. *Brain explodes*
     */
    public function getMetadataProperty(string $field, mixed $default = null): mixed
    {
        return $this->getChildProperty('metadata', $field, $default);
    }

    /**
     * The 'auth_type' child property from the 'metadata' parent property.
     *
     * @return string|null
     */
    public function getAuthType()
    {
        return $this->getMetadataProperty('auth_type');
    }

    /**
     * The 'auth_nonce' child property from the 'metadata' parent property.
     *
     * @return string|null
     */
    public function getAuthNonce()
    {
        return $this->getMetadataProperty('auth_nonce');
    }

    /**
     * For impersonated access tokens, the ID of
     * the page this token contains.
     *
     * @return string|null
     */
    public function getProfileId()
    {
        return $this->getField('profile_id');
    }

    /**
     * List of permissions that the user has granted for
     * the app in this access token.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->getField('scopes');
    }

    /**
     * Ensures the app ID from the access token
     * metadata is what we expect.
     *
     * @param string $appId
     *
     * @throws FacebookSDKException
     */
    public function validateAppId($appId)
    {
        if ($this->getAppId() !== $appId) {
            throw new FacebookSDKException('Access token metadata contains unexpected app ID.', 401);
        }
    }

    /**
     * The ID of the application this access token is for.
     */
    public function getAppId(): ?string
    {
        $appId = $this->getField('app_id');

        return $appId
            ? (string)$appId
            : null;
    }

    /**
     * Ensures the user ID from the access token
     * metadata is what we expect.
     *
     * @param string $userId
     *
     * @throws FacebookSDKException
     */
    public function validateUserId($userId)
    {
        if ($this->getUserId() !== $userId) {
            throw new FacebookSDKException('Access token metadata contains unexpected user ID.', 401);
        }
    }

    /**
     * The ID of the user this access token is for.
     *
     * @return string|null
     */
    public function getUserId()
    {
        return $this->getField('user_id');
    }

    /**
     * Ensures the access token has not expired yet.
     *
     * @throws FacebookSDKException
     */
    public function validateExpiration()
    {
        if (!$this->getExpiresAt() instanceof \DateTime) {
            return;
        }

        if ($this->getExpiresAt()->getTimestamp() < time()) {
            throw new FacebookSDKException('Inspection of access token metadata shows that the access token has expired.', 401);
        }
    }

    /**
     * DateTime when this access token expires.
     *
     * @return \DateTime|null
     */
    public function getExpiresAt()
    {
        return $this->getField('expires_at');
    }
}
