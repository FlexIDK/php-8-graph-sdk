<?php

namespace One23\GraphSdk;

use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Exceptions\SDKException;

class FacebookApp implements \Serializable
{
    /**
     * The app ID.
     */
    protected string $id;

    /**
     * @throws SDKException
     */
    public function __construct(string|int $id, protected string $secret)
    {
        $this->id = (string)$id;
    }

    /**
     * Returns the app ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the app secret.
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Returns an app access token.
     */
    public function getAccessToken(): AccessToken
    {
        return new AccessToken($this->id . '|' . $this->secret);
    }

    public function serialize(): string
    {
        return implode('|', [
            $this->id,
            $this->secret
        ]);
    }

    public function unserialize(string $serialized)
    {
        if (isset($this->id)) {
            throw new \LogicException('unserialize() is an internal function, it must not be called directly.');
        }

        list($id, $secret) = explode('|', $serialized, 2);

        $this->id       = (string)$id;
        $this->secret   = (string)$secret;
    }

    public function __serialize(): array
    {
        return [
            'id'        => $this->id,
            'secret'    => $this->secret,
        ];
    }

    public function __unserialize(array $data): void
    {
        if (isset($this->id)) {
            throw new \LogicException('__unserialize() is an internal function, it must not be called directly.');
        }

        $this->id       = $data['id'];
        $this->secret   = $data['secret'];
    }
}
