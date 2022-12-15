<?php

namespace One23\GraphSdk\Helpers;

use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Authentication\OAuth2Client;
use One23\GraphSdk\Exceptions\SDKException;
use One23\GraphSdk\PersistentData\PersistentDataFactory;
use One23\GraphSdk\PersistentData\Handlers\PersistentDataInterface;
use One23\GraphSdk\PseudoRandomString\GeneratorFactory;
use One23\GraphSdk\PseudoRandomString\Generators\GeneratorInterface;
use One23\GraphSdk\Traits\MapTypeTrait;
use One23\GraphSdk\Url;

class RedirectLogin
{
    use MapTypeTrait;

    const CSRF_LENGTH = 32;

    protected Url\DetectionInterface $urlDetectionHandler;

    protected PersistentDataInterface $persistentDataHandler;

    protected GeneratorInterface $pseudoRandomStringGenerator;

    public function __construct(
        protected OAuth2Client $oAuth2Client,
        PersistentDataInterface $persistentDataHandler = null,
        Url\DetectionInterface $urlHandler = null,
        GeneratorInterface $prsg = null
    ) {
        $this->persistentDataHandler = $persistentDataHandler
            ?: PersistentDataFactory::createPersistentDataHandler();
        $this->urlDetectionHandler = $urlHandler ?: new Url\DetectionHandler();
        $this->pseudoRandomStringGenerator = GeneratorFactory::createPseudoRandomStringGenerator($prsg);
    }

    /**
     * Returns the persistent data handler.
     */
    public function getPersistentDataHandler(): PersistentDataInterface
    {
        return $this->persistentDataHandler;
    }

    /**
     * Returns the URL detection handler.
     */
    public function getUrlDetectionHandler(): Url\DetectionInterface
    {
        return $this->urlDetectionHandler;
    }

    /**
     * Returns the cryptographically secure pseudo-random string generator.
     */
    public function getPseudoRandomStringGenerator(): GeneratorInterface
    {
        return $this->pseudoRandomStringGenerator;
    }

    /**
     * Returns the URL to send the user in order to login to Facebook.
     *
     * @throws SDKException
     */
    public function getLoginUrl(string $redirectUrl, array $scope = [], string $separator = '&'): string
    {
        return $this->makeUrl($redirectUrl, $scope, [], $separator);
    }

    /**
     * Stores CSRF state and returns a URL to which the user should be sent to in order to continue the login process with Facebook.
     *
     * @throws SDKException
     */
    private function makeUrl(string $redirectUrl, array $scope, array $params = [], string $separator = '&'): string
    {
        $state = $this->persistentDataHandler->get('state')
            ?: $this->pseudoRandomStringGenerator->getPseudoRandomString(static::CSRF_LENGTH);

        $this->persistentDataHandler->set('state', $state);

        return $this->oAuth2Client->getAuthorizationUrl($redirectUrl, $state, $scope, $params, $separator);
    }

    /**
     * Returns the URL to send the user in order to log out of Facebook.
     *
     * @throws SDKException
     */
    public function getLogoutUrl(AccessToken|string $accessToken, string $next, string $separator = '&'): string
    {
        if (!$accessToken instanceof AccessToken) {
            $accessToken = new AccessToken($accessToken);
        }

        if ($accessToken->isAppAccessToken()) {
            throw new SDKException('Cannot generate a logout URL with an app access token.', 722);
        }

        $params = [
            'next' => $next,
            'access_token' => $accessToken->getValue(),
        ];

        return 'https://www.facebook.com/logout.php?' .
            http_build_query($params, "", $separator);
    }

    /**
     * Returns the URL to send the user in order to login to Facebook with permission(s) to be re-asked.
     *
     * @throws SDKException
     */
    public function getReRequestUrl(string $redirectUrl, array $scope = [], string $separator = '&'): string
    {
        $params = ['auth_type' => 'rerequest'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Returns the URL to send the user in order to login to Facebook with user to be re-authenticated.
     *
     * @throws SDKException
     */
    public function getReAuthenticationUrl(string $redirectUrl, array $scope = [], string $separator = '&'): string
    {
        $params = ['auth_type' => 'reauthenticate'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Takes a valid code from a login redirect, and returns an AccessToken entity.
     *
     * @throws SDKException
     */
    public function getAccessToken(string $redirectUrl = null): ?AccessToken
    {
        if (!$code = $this->getCode()) {
            return null;
        }

        $this->validateCsrf();
        $this->resetCsrf();

        $redirectUrl = $redirectUrl ?: $this->urlDetectionHandler->getCurrentUrl();
        // At minimum we need to remove the 'code', 'enforce_https' and 'state' params
        $redirectUrl = Url\Manipulator::removeParamsFromUrl($redirectUrl, ['code', 'enforce_https', 'state']);

        return $this->oAuth2Client->getAccessTokenFromCode($code, $redirectUrl);
    }

    /**
     * Return the code.
     */
    protected function getCode(): ?string
    {
        return self::mapType(
            $this->getInput('code'),
            'str'
        );
    }

    /**
     * Returns a value from a GET param.
     */
    private function getInput(string $key): ?string
    {
        return isset($_GET[$key])
            ? (string)$_GET[$key]
            : null;
    }

    /**
     * Validate the request against a cross-site request forgery.
     *
     * @throws SDKException
     */
    protected function validateCsrf(): void
    {
        $state = $this->getState();
        if (!$state) {
            throw new SDKException('Cross-site request forgery validation failed. Required GET param "state" missing.');
        }
        $savedState = $this->persistentDataHandler->get('state');
        if (!$savedState) {
            throw new SDKException('Cross-site request forgery validation failed. Required param "state" missing from persistent data.');
        }

        if (\hash_equals($savedState, $state)) {
            return;
        }

        throw new SDKException('Cross-site request forgery validation failed. The "state" param from the URL and session do not match.');
    }

    /**
     * Return the state.
     */
    protected function getState(): ?string
    {
        return self::mapType(
            $this->getInput('state'),
            'str'
        );
    }

    /**
     * Resets the CSRF so that it doesn't get reused.
     */
    private function resetCsrf(): void
    {
        $this->persistentDataHandler->set('state', null);
    }

    /**
     * Return the error code.
     */
    public function getErrorCode(): ?string
    {
        return self::mapType(
            $this->getInput('error_code'),
            'str'
        );
    }

    /**
     * Returns the error.
     */
    public function getError(): ?string
    {
        return self::mapType(
            $this->getInput('error'),
            'str'
        );
    }

    /**
     * Returns the error reason.
     */
    public function getErrorReason(): ?string
    {
        return self::mapType(
            $this->getInput('error_reason'),
            'str'
        );
    }

    /**
     * Returns the error description.
     */
    public function getErrorDescription(): ?string
    {
        return self::mapType(
            $this->getInput('error_description'),
            'str'
        );
    }
}
