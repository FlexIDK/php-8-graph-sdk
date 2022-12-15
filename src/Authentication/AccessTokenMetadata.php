<?php

namespace One23\GraphSdk\Authentication;

use One23\GraphSdk\Exceptions\SDKException;
use One23\GraphSdk\Traits\MapTypeTrait;

/**
 * Represents metadata from an access token.
 *
 * @see https://developers.facebook.com/docs/graph-api/reference/debug_token
 */
class AccessTokenMetadata
{
    use MapTypeTrait;

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
        return self::mapType(
            $this->getField('application'),
            'str'
        );
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
        return self::mapType(
            $this->getErrorProperty('code'),
            'int'
        );
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
        return self::mapType(
            $this->getErrorProperty('message'),
            'str'
        );
    }

    /**
     * The error subcode for the error.
     */
    public function getErrorSubcode(): ?int
    {
        return self::mapType(
            $this->getErrorProperty('subcode'),
            'int'
        );
    }

    /**
     * Whether the access token is still valid or not.
     */
    public function getIsValid(): bool
    {
        return self::mapType(
            $this->getField('is_valid'),
            'bool'
        );
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
        return self::mapType(
            $this->getField('issued_at'),
            \DateTime::class
        );
    }

    /**
     * General metadata associated with the access token.
     * Can contain data like 'sso', 'auth_type', 'auth_nonce'.
     */
    public function getMetadata(): ?array
    {
        return self::mapType(
            $this->getField('metadata'),
            'arr'
        );
    }

    /**
     * The 'sso' child property from the 'metadata' parent property.
     */
    public function getSso(): ?string
    {
        return self::mapType(
            $this->getMetadataProperty('sso'),
            'str'
        );
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
        return self::mapType(
            $this->getMetadataProperty('auth_type'),
            'str'
        );
    }

    /**
     * The 'auth_nonce' child property from the 'metadata' parent property.
     */
    public function getAuthNonce(): ?string
    {
        return self::mapType(
            $this->getMetadataProperty('auth_nonce'),
            'str'
        );
    }

    /**
     * For impersonated access tokens, the ID of
     * the page this token contains.
     */
    public function getProfileId(): ?string
    {
        return self::mapType(
            $this->getField('profile_id'),
            'str'
        );
    }

    /**
     * List of permissions that the user has granted for
     * the app in this access token.
     */
    public function getScopes(): array
    {
        return self::mapType(
            $this->getField('scopes'),
            'arr'
        );
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
        return self::mapType(
            $this->getField('app_id'),
            'str'
        );
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
        return self::mapType(
            $this->getField('user_id'),
            'str'
        );
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
        return self::mapType(
            $this->getField('expires_at'),
            \DateTime::class
        );
    }
}
