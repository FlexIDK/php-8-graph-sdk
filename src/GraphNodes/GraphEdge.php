<?php

namespace One23\GraphSdk\GraphNodes;

use One23\GraphSdk\FacebookRequest;
use One23\GraphSdk\Url;
use One23\GraphSdk\Exceptions\SDKException;

/**
 * Class GraphEdge

 */
class GraphEdge extends Collection
{
    public function __construct(
        protected FacebookRequest $request,
        array $data = [],
        protected array $metaData = [],
        protected ?string $parentEdgeEndpoint = null,
        protected ?string $subclassName = null
    ) {
        parent::__construct($data);
    }

    /**
     * Gets the parent Graph edge endpoint that generated the list.
     */
    public function getParentGraphEdge(): ?string
    {
        return $this->parentEdgeEndpoint;
    }

    /**
     * Gets the subclass name that the child GraphNode's are cast as.
     */
    public function getSubClassName(): ?string
    {
        return $this->subclassName;
    }

    /**
     * Returns the raw meta data associated with this GraphEdge.
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    /**
     * Returns the next cursor if it exists.
     */
    public function getNextCursor(): ?string
    {
        return $this->getCursor('after');
    }

    /**
     * Returns the cursor for a specific direction if it exists.
     */
    public function getCursor(string $direction): ?string
    {
        if (isset($this->metaData['paging']['cursors'][$direction])) {
            return self::mapType(
                $this->metaData['paging']['cursors'][$direction],
                'str'
            );
        }

        return null;
    }

    /**
     * Returns the previous cursor if it exists.
     */
    public function getPreviousCursor(): ?string
    {
        return $this->getCursor('before');
    }

    /**
     * Gets the request object needed to make a "next" page request.
     *
     * @throws SDKException
     */
    public function getNextPageRequest(): ?FacebookRequest
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
     * @throws SDKException
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
     * @throws SDKException
     */
    public function getPaginationUrl(string $direction): ?string
    {
        $this->validateForPagination();

        // Do we have a paging URL?
        if (!isset($this->metaData['paging'][$direction])) {
            return null;
        }

        $pageUrl = self::mapType(
            $this->metaData['paging'][$direction],
            'str'
        );

        if (!$pageUrl) {
            return null;
        }

        return Url\Manipulator::baseGraphUrlEndpoint($pageUrl);
    }

    /**
     * Validates whether or not we can paginate on this request.
     *
     * @throws SDKException
     */
    public function validateForPagination(): void
    {
        if ($this->request->getMethod() !== 'GET') {
            throw new SDKException('You can only paginate on a GET request.', 720);
        }
    }

    /**
     * Gets the request object needed to make a "previous" page request.
     *
     * @throws SDKException
     */
    public function getPreviousPageRequest(): ?FacebookRequest
    {
        return $this->getPaginationRequest('previous');
    }

    /**
     * The total number of results according to Graph if it exists.
     *
     * This will be returned if the summary=true modifier is present in the request.
     */
    public function getTotalCount(): ?int
    {
        if (isset($this->metaData['summary']['total_count'])) {
            return self::mapType(
                $this->metaData['summary']['total_count'],
                'int'
            );
        }

        return null;
    }

    public function map(\Closure $callback): static
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
