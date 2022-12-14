<?php

namespace One23\GraphSdk\Helpers;

use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Authentication\OAuth2Client;
use One23\GraphSdk\Exceptions\SDKException;
use One23\GraphSdk\PersistentData\FacebookSessionPersistentDataHandler;
use One23\GraphSdk\PersistentData\PersistentDataInterface;
use One23\GraphSdk\PseudoRandomString\GeneratorFactory;
use One23\GraphSdk\PseudoRandomString\Generators\GeneratorInterface;
use One23\GraphSdk\Url;

/**
 * Class FacebookRedirectLoginHelper

 */
class FacebookRedirectLoginHelper
{
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
        $this->persistentDataHandler = $persistentDataHandler ?: new FacebookSessionPersistentDataHandler();
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

        return 'https://www.facebook.com/logout.php?' . http_build_query($params, null, $separator);
    }

    /**
     * Returns the URL to send the user in order to login to Facebook with permission(s) to be re-asked.
     *
     * @param string $redirectUrl The URL Facebook should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getReRequestUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        $params = ['auth_type' => 'rerequest'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Returns the URL to send the user in order to login to Facebook with user to be re-authenticated.
     *
     * @param string $redirectUrl The URL Facebook should redirect users to after login.
     * @param array  $scope       List of permissions to request during login.
     * @param string $separator   The separator to use in http_build_query().
     *
     * @return string
     */
    public function getReAuthenticationUrl($redirectUrl, array $scope = [], $separator = '&')
    {
        $params = ['auth_type' => 'reauthenticate'];

        return $this->makeUrl($redirectUrl, $scope, $params, $separator);
    }

    /**
     * Takes a valid code from a login redirect, and returns an AccessToken entity.
     *
     * @param string|null $redirectUrl The redirect URL.
     *
     * @return AccessToken|null
     *
     * @throws SDKException
     */
    public function getAccessToken($redirectUrl = null)
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
     *
     * @return string|null
     */
    protected function getCode()
    {
        return $this->getInput('code');
    }

    /**
     * Returns a value from a GET param.
     *
     * @param string $key
     *
     * @return string|null
     */
    private function getInput($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    /**
     * Validate the request against a cross-site request forgery.
     *
     * @throws SDKException
     */
    protected function validateCsrf()
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
     *
     * @return string|null
     */
    protected function getState()
    {
        return $this->getInput('state');
    }

    /**
     * Resets the CSRF so that it doesn't get reused.
     */
    private function resetCsrf()
    {
        $this->persistentDataHandler->set('state', null);
    }

    /**
     * Return the error code.
     *
     * @return string|null
     */
    public function getErrorCode()
    {
        return $this->getInput('error_code');
    }

    /**
     * Returns the error.
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->getInput('error');
    }

    /**
     * Returns the error reason.
     *
     * @return string|null
     */
    public function getErrorReason()
    {
        return $this->getInput('error_reason');
    }

    /**
     * Returns the error description.
     *
     * @return string|null
     */
    public function getErrorDescription()
    {
        return $this->getInput('error_description');
    }
}
