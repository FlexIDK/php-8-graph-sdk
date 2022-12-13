<?php

namespace One23\GraphSdk\GraphNodes;

use One23\GraphSdk\FacebookRequest;
use One23\GraphSdk\Url\FacebookUrlManipulator;
use One23\GraphSdk\Exceptions\FacebookSDKException;

/**
 * Class GraphEdge

 */
class GraphEdge extends Collection
{
    /**
     * @var FacebookRequest The original request that generated this data.
     */
    protected $request;

    /**
     * @var array An array of Graph meta data like pagination, etc.
     */
    protected $metaData = [];

    /**
     * @var string|null The parent Graph edge endpoint that generated the list.
     */
    protected $parentEdgeEndpoint;

    /**
     * @var string|null The subclass of the child GraphNode's.
     */
    protected $subclassName;

    /**
     * Init this collection of GraphNode's.
     *
     * @param FacebookRequest $request            The original request that generated this data.
     * @param array           $data               An array of GraphNode's.
     * @param array           $metaData           An array of Graph meta data like pagination, etc.
     * @param string|null     $parentEdgeEndpoint The parent Graph edge endpoint that generated the list.
     * @param string|null     $subclassName       The subclass of the child GraphNode's.
     */
    public function __construct(FacebookRequest $request, array $data = [], array $metaData = [], $parentEdgeEndpoint = null, $subclassName = null)
    {
        $this->request = $request;
        $this->metaData = $metaData;
        $this->parentEdgeEndpoint = $parentEdgeEndpoint;
        $this->subclassName = $subclassName;

        parent::__construct($data);
    }

    /**
     * Gets the parent Graph edge endpoint that generated the list.
     *
     * @return string|null
     */
    public function getParentGraphEdge()
    {
        return $this->parentEdgeEndpoint;
    }

    /**
     * Gets the subclass name that the child GraphNode's are cast as.
     *
     * @return string|null
     */
    public function getSubClassName()
    {
        return $this->subclassName;
    }

    /**
     * Returns the raw meta data associated with this GraphEdge.
     *
     * @return array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Returns the next cursor if it exists.
     *
     * @return string|null
     */
    public function getNextCursor()
    {
        return $this->getCursor('after');
    }

    /**
     * Returns the cursor for a specific direction if it exists.
     *
     * @param string $direction The direction of the page: after|before
     *
     * @return string|null
     */
    public function getCursor($direction)
    {
        if (isset($this->metaData['paging']['cursors'][$direction])) {
            return $this->metaData['paging']['cursors'][$direction];
        }

        return null;
    }

    /**
     * Returns the previous cursor if it exists.
     *
     * @return string|null
     */
    public function getPreviousCursor()
    {
        return $this->getCursor('before');
    }

    /**
     * Gets the request object needed to make a "next" page request.
     *
     * @return FacebookRequest|null
     *
     * @throws FacebookSDKException
     */
    public function getNextPageRequest()
    {
        return $this->getPaginationRequest('next');
    }

    /**
     * Gets the request object needed to make a next|previous page request.
     *
     * @param string $direction The direction of the page: next|previous
     *
     * @return FacebookRequest|null
     *
     * @throws FacebookSDKException
     */
    public function getPaginationRequest($direction)
    {
        $pageUrl = $this->getPaginationUrl($direction);
        if (!$pageUrl) {
            return null;
        }

        $newRequest = clone $this->request;
        $newRequest->setEndpoint($pageUrl);

        return $newRequest;
    }

    /**
     * Generates a pagination URL based on a cursor.
     *
     * @param string $direction The direction of the page: next|previous
     *
     * @return string|null
     *
     * @throws FacebookSDKException
     */
    public function getPaginationUrl($direction)
    {
        $this->validateForPagination();

        // Do we have a paging URL?
        if (!isset($this->metaData['paging'][$direction])) {
            return null;
        }

        $pageUrl = $this->metaData['paging'][$direction];

        return FacebookUrlManipulator::baseGraphUrlEndpoint($pageUrl);
    }

    /**
     * Validates whether or not we can paginate on this request.
     *
     * @throws FacebookSDKException
     */
    public function validateForPagination()
    {
        if ($this->request->getMethod() !== 'GET') {
            throw new FacebookSDKException('You can only paginate on a GET request.', 720);
        }
    }

    /**
     * Gets the request object needed to make a "previous" page request.
     *
     * @return FacebookRequest|null
     *
     * @throws FacebookSDKException
     */
    public function getPreviousPageRequest()
    {
        return $this->getPaginationRequest('previous');
    }

    /**
     * The total number of results according to Graph if it exists.
     *
     * This will be returned if the summary=true modifier is present in the request.
     *
     * @return int|null
     */
    public function getTotalCount()
    {
        if (isset($this->metaData['summary']['total_count'])) {
            return $this->metaData['summary']['total_count'];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function map(\Closure $callback)
    {
        return new static(
            $this->request,
            array_map($callback, $this->items, array_keys($this->items)),
            $this->metaData,
            $this->parentEdgeEndpoint,
            $this->subclassName
        );
    }
}