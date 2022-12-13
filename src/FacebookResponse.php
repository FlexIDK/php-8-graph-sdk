<?php

namespace One23\GraphSdk;

use One23\GraphSdk\GraphNodes\GraphNodeFactory;
use One23\GraphSdk\Exceptions\FacebookResponseException;
use One23\GraphSdk\Exceptions\FacebookSDKException;

/**
 * Class FacebookResponse

 */
class FacebookResponse
{
    /**
     * @var int The HTTP status code response from Graph.
     */
    protected $httpStatusCode;

    /**
     * @var array The headers returned from Graph.
     */
    protected $headers;

    /**
     * @var string The raw body of the response from Graph.
     */
    protected $body;

    /**
     * @var array The decoded body of the Graph response.
     */
    protected $decodedBody = [];

    /**
     * @var FacebookRequest The original request that returned this response.
     */
    protected $request;

    /**
     * @var FacebookSDKException The exception thrown by this request.
     */
    protected $thrownException;

    /**
     * Creates a new Response entity.
     *
     * @param FacebookRequest $request
     * @param string|null     $body
     * @param int|null        $httpStatusCode
     * @param array|null      $headers
     */
    public function __construct(FacebookRequest $request, $body = null, $httpStatusCode = null, array $headers = [])
    {
        $this->request = $request;
        $this->body = $body;
        $this->httpStatusCode = $httpStatusCode;
        $this->headers = $headers;

        $this->decodeBody();
    }

    /**
     * Convert the raw response into an array if possible.
     *
     * Graph will return 2 types of responses:
     * - JSON(P)
     *    Most responses from Graph are JSON(P)
     * - application/x-www-form-urlencoded key/value pairs
     *    Happens on the `/oauth/access_token` endpoint when exchanging
     *    a short-lived access token for a long-lived access token
     * - And sometimes nothing :/ but that'd be a bug.
     */
    public function decodeBody()
    {
        $this->decodedBody = json_decode($this->body, true);

        if ($this->decodedBody === null) {
            $this->decodedBody = [];
            parse_str($this->body, $this->decodedBody);
        } elseif (is_bool($this->decodedBody)) {
            // Backwards compatibility for Graph < 2.1.
            // Mimics 2.1 responses.
            // @TODO Remove this after Graph 2.0 is no longer supported
            $this->decodedBody = ['success' => $this->decodedBody];
        } elseif (is_numeric($this->decodedBody)) {
            $this->decodedBody = ['id' => $this->decodedBody];
        }

        if (!is_array($this->decodedBody)) {
            $this->decodedBody = [];
        }

        if ($this->isError()) {
            $this->makeException();
        }
    }

    /**
     * Returns true if Graph returned an error message.
     *
     * @return boolean
     */
    public function isError()
    {
        return isset($this->decodedBody['error']);
    }

    /**
     * Instantiates an exception to be thrown later.
     */
    public function makeException()
    {
        $this->thrownException = FacebookResponseException::create($this);
    }

    /**
     * Return the original request that returned this response.
     *
     * @return FacebookRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return the FacebookApp entity used for this response.
     *
     * @return FacebookApp
     */
    public function getApp()
    {
        return $this->request->getApp();
    }

    /**
     * Return the access token that was used for this response.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->request->getAccessToken();
    }

    /**
     * Return the HTTP status code for this response.
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Return the HTTP headers for this response.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Return the raw body response.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Return the decoded body response.
     *
     * @return array
     */
    public function getDecodedBody()
    {
        return $this->decodedBody;
    }

    /**
     * Get the app secret proof that was used for this response.
     *
     * @return string|null
     */
    public function getAppSecretProof()
    {
        return $this->request->getAppSecretProof();
    }

    /**
     * Get the ETag associated with the response.
     *
     * @return string|null
     */
    public function getETag()
    {
        return isset($this->headers['ETag']) ? $this->headers['ETag'] : null;
    }

    /**
     * Get the version of Graph that returned this response.
     *
     * @return string|null
     */
    public function getGraphVersion()
    {
        return isset($this->headers['Facebook-API-Version']) ? $this->headers['Facebook-API-Version'] : null;
    }

    /**
     * Throws the exception.
     *
     * @throws FacebookSDKException
     */
    public function throwException()
    {
        throw $this->thrownException;
    }

    /**
     * Returns the exception that was thrown for this request.
     *
     * @return FacebookResponseException|null
     */
    public function getThrownException()
    {
        return $this->thrownException;
    }

    /**
     * Instantiate a new GraphNode from response.
     *
     * @param string|null $subclassName The GraphNode subclass to cast to.
     *
     * @return \One23\GraphSdk\GraphNodes\GraphNode
     *
     * @throws FacebookSDKException
     */
    public function getGraphNode($subclassName = null)
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphNode($subclassName);
    }

    /**
     * Convenience method for creating a GraphAlbum collection.
     *
     * @return \One23\GraphSdk\GraphNodes\GraphAlbum
     *
     * @throws FacebookSDKException
     */
    public function getGraphAlbum()
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphAlbum();
    }

    /**
     * Convenience method for creating a GraphPage collection.
     *
     * @return \One23\GraphSdk\GraphNodes\GraphPage
     *
     * @throws FacebookSDKException
     */
    public function getGraphPage()
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphPage();
    }

    /**
     * Convenience method for creating a GraphSessionInfo collection.
     *
     * @return \One23\GraphSdk\GraphNodes\GraphSessionInfo
     *
     * @throws FacebookSDKException
     */
    public function getGraphSessionInfo()
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphSessionInfo();
    }

    /**
     * Convenience method for creating a GraphUser collection.
     *
     * @return \One23\GraphSdk\GraphNodes\GraphUser
     *
     * @throws FacebookSDKException
     */
    public function getGraphUser()
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphUser();
    }

    /**
     * Convenience method for creating a GraphEvent collection.
     *
     * @return \One23\GraphSdk\GraphNodes\GraphEvent
     *
     * @throws FacebookSDKException
     */
    public function getGraphEvent()
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphEvent();
    }

    /**
     * Convenience method for creating a GraphGroup collection.
     *
     * @return \One23\GraphSdk\GraphNodes\GraphGroup
     *
     * @throws FacebookSDKException
     */
    public function getGraphGroup()
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphGroup();
    }

    /**
     * Instantiate a new GraphEdge from response.
     *
     * @param string|null $subclassName The GraphNode subclass to cast list items to.
     * @param boolean     $auto_prefix  Toggle to auto-prefix the subclass name.
     *
     * @return \One23\GraphSdk\GraphNodes\GraphEdge
     *
     * @throws FacebookSDKException
     */
    public function getGraphEdge($subclassName = null, $auto_prefix = true)
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphEdge($subclassName, $auto_prefix);
    }
}
