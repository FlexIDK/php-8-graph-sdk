<?php

namespace One23\GraphSdk;

use One23\GraphSdk\Exceptions\SDKException;

class SignedRequest
{
    use MapTypeTrait;

    /**
     * The payload from the decrypted signed request.
     */
    protected ?array $payload;

    /**
     * @throws SDKException
     */
    public function __construct(
        protected App $app,
        protected ?string $rawSignedRequest = null
    ) {
        $this->parse();
    }

    /**
     * Validates and decodes a signed request and saves
     * the payload to an array.
     *
     * @throws SDKException
     */
    protected function parse(): void
    {
        if (is_null($this->rawSignedRequest)) {
            return;
        }

        list($encodedSig, $encodedPayload) = $this->split();

        // Signature validation
        $sig = $this->decodeSignature((string)$encodedSig);
        $hashedSig = $this->hashSignature((string)$encodedPayload);
        $this->validateSignature($hashedSig, $sig);

        $this->payload = $this->decodePayload((string)$encodedPayload);

        // Payload validation
        $this->validateAlgorithm();
    }

    /**
     * Splits a raw signed request into signature and payload.
     *
     * @throws SDKException
     */
    protected function split(): array
    {
        if (!str_contains($this->rawSignedRequest, '.')) {
            throw new SDKException('Malformed signed request.', 606);
        }

        return explode('.', $this->rawSignedRequest, 2);
    }

    /**
     * Decodes the raw signature from a signed request.
     *
     * @throws SDKException
     */
    protected function decodeSignature(string $encodedSig): string
    {
        $sig = $this->base64UrlDecode($encodedSig);

        if (!$sig) {
            throw new SDKException('Signed request has malformed encoded signature data.', 607);
        }

        return $sig;
    }

    /**
     * Base64 decoding which replaces characters:
     *   + instead of -
     *   / instead of _
     *
     * @link http://en.wikipedia.org/wiki/Base64#URL_applications
     *
     * @throws SDKException
     */
    public function base64UrlDecode(string $input): string
    {
        $urlDecodedBase64 = strtr($input, '-_', '+/');
        $this->validateBase64($urlDecodedBase64);

        return base64_decode($urlDecodedBase64);
    }

    /**
     * Validates a base64 string.
     *
     * @throws SDKException
     */
    protected function validateBase64(string $input): void
    {
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $input)) {
            throw new SDKException('Signed request contains malformed base64 encoding.', 608);
        }
    }

    /**
     * Hashes the signature used in a signed request.
     *
     * @throws SDKException
     */
    protected function hashSignature(string $encodedData): string
    {
        $hashedSig = hash_hmac(
            'sha256',
            $encodedData,
            $this->app->getSecret(),
            $raw_output = true
        );

        if (!$hashedSig) {
            throw new SDKException('Unable to hash signature from encoded payload data.', 602);
        }

        return $hashedSig;
    }

    /**
     * Validates the signature used in a signed request.
     *
     * @throws SDKException
     */
    protected function validateSignature(string $hashedSig, string $sig): void
    {
        if (\hash_equals($hashedSig, $sig)) {
            return;
        }

        throw new SDKException('Signed request has an invalid signature.', 602);
    }

    /**
     * Decodes the raw payload from a signed request.
     *
     * @throws SDKException
     */
    protected function decodePayload(string $encodedPayload): array
    {
        $payload = $this->base64UrlDecode($encodedPayload);
        if ($payload) {
            $payload = json_decode($payload, true);
        }

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !is_array($payload)
        ) {
            throw new SDKException('Signed request has malformed encoded payload data.', 607);
        }

        return $payload;
    }

    /**
     * Validates the algorithm used in a signed request.
     *
     * @throws SDKException
     */
    protected function validateAlgorithm(): void
    {
        if ($this->get('algorithm') !== 'HMAC-SHA256') {
            throw new SDKException('Signed request is using the wrong algorithm.', 605);
        }
    }

    /**
     * Returns a property from the signed request data if available.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }

    /**
     * Returns the raw signed request data.
     */
    public function getRawSignedRequest(): ?string
    {
        return $this->rawSignedRequest;
    }

    /**
     * Returns the parsed signed request data.
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     * Returns user_id from signed request data if available.
     */
    public function getUserId(): ?string
    {
        return self::mapType(
            $this->get('user_id'),
            'str'
        );
    }

    /**
     * Checks for OAuth data in the payload.
     */
    public function hasOAuthData(): bool
    {
        $oauthToken = self::mapType(
            $this->get('oauth_token'),
            'str'
        );

        $code = self::mapType(
            $this->get('code'),
            'int'
        );

        return !!($oauthToken || $code);
    }

    /**
     * Creates a signed request from an array of data.
     *
     * @throws SDKException
     */
    public function make(array $payload): string
    {
        $payload['algorithm'] = $payload['algorithm'] ?? 'HMAC-SHA256';
        $payload['issued_at'] = $payload['issued_at'] ?? time();

        $encodedPayload = $this->base64UrlEncode(json_encode($payload));

        $hashedSig = $this->hashSignature($encodedPayload);
        $encodedSig = $this->base64UrlEncode($hashedSig);

        return $encodedSig . '.' . $encodedPayload;
    }

    /**
     * Base64 encoding which replaces characters:
     *   + instead of -
     *   / instead of _
     *
     * @link http://en.wikipedia.org/wiki/Base64#URL_applications
     */
    public function base64UrlEncode(string $input): string
    {
        return strtr(base64_encode($input), '+/', '-_');
    }
}
