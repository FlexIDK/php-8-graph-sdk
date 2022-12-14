<?php

namespace One23\GraphSdk\Authentication;

use One23\GraphSdk\Exceptions\SDKException;

/**
 * Represents metadata from an access token.
 *
 * @see https://developers.facebook.com/docs/graph-api/reference/debug_token
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
     * @throws SDKException
     */
    public function __construct(array $metadata)
    {
        if (!isset($metadata['data'])) {
            throw new SDKException('Unexpected debug token response data.', 401);
        }

        $this->metadata = $metadata['data'];

        $this->castTimestampsToDateTime();
    }

    /**
     * Casts the unix timestamps as DateTime entities.
     */
    private function castTimestampsToDateTime(): void
    {
        foreach (static::$dateProperties as $key) {
            if (isset($this->metadata[$key]) && $this->metadata[$key] !== 0) {
                $this->metadata[$key] = $this->convertTimestampToDateTime($this->metadata[$key]);
            }
        }
    }

    /**
     * Converts a unix timestamp into a DateTime entity.
     */
    private function convertTimestampToDateTime(int $timestamp): \DateTime
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
     */
    public function getIssuedAt(): ?\DateTime
    {
        $issuedAt = $this->getField('issued_at');

        return $issuedAt instanceof \DateTime
            ? $issuedAt
            : null;
    }

    /**
     * General metadata associated with the access token.
     * Can contain data like 'sso', 'auth_type', 'auth_nonce'.
     */
    public function getMetadata(): ?array
    {
        $metadata = $this->getField('metadata');

        return is_array($metadata)
            ? $metadata
            : null;
    }

    /**
     * The 'sso' child property from the 'metadata' parent property.
     */
    public function getSso(): ?string
    {
        $sso = $this->getMetadataProperty('sso');

        return $sso
            ? (string)$sso
            : null;
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
     */
    public function getAuthType(): ?string
    {
        $authType = $this->getMetadataProperty('auth_type');

        return $authType
            ? (string)$authType
            : null;
    }

    /**
     * The 'auth_nonce' child property from the 'metadata' parent property.
     */
    public function getAuthNonce(): ?string
    {
        $authNonce = $this->getMetadataProperty('auth_nonce');

        return $authNonce
            ? (string)$authNonce
            : null;
    }

    /**
     * For impersonated access tokens, the ID of
     * the page this token contains.
     */
    public function getProfileId(): ?string
    {
        $profileId = $this->getField('profile_id');

        return $profileId
            ? (string)$profileId
            : null;
    }

    /**
     * List of permissions that the user has granted for
     * the app in this access token.
     */
    public function getScopes(): array
    {
        $scopes = $this->getField('scopes');

        return is_array($scopes)
            ? $scopes
            : [];
    }

    /**
     * Ensures the app ID from the access token
     * metadata is what we expect.
     *
     * @throws SDKException
     */
    public function validateAppId(string $appId): void
    {
        if ($this->getAppId() !== $appId) {
            throw new SDKException('Access token metadata contains unexpected app ID.', 401);
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
     * @throws SDKException
     */
    public function validateUserId(string $userId): void
    {
        if ($this->getUserId() !== $userId) {
            throw new SDKException('Access token metadata contains unexpected user ID.', 401);
        }
    }

    /**
     * The ID of the user this access token is for.
     */
    public function getUserId(): ?string
    {
        $userId = $this->getField('user_id');

        return $userId
            ? (string)$userId
            : null;
    }

    /**
     * Ensures the access token has not expired yet.
     *
     * @throws SDKException
     */
    public function validateExpiration(): void
    {
        if (!$this->getExpiresAt() instanceof \DateTime) {
            return;
        }

        if ($this->getExpiresAt()->getTimestamp() < time()) {
            throw new SDKException('Inspection of access token metadata shows that the access token has expired.', 401);
        }
    }

    /**
     * DateTime when this access token expires.
     */
    public function getExpiresAt(): ?\DateTime
    {
        $expiresAt = $this->getField('expires_at');

        return $expiresAt instanceof \DateTime
            ? $expiresAt
            : null;
    }
}
