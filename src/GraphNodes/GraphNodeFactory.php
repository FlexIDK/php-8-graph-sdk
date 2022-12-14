<?php

namespace One23\GraphSdk\GraphNodes;

use One23\GraphSdk\Response;
use One23\GraphSdk\Exceptions\SDKException;

/**
 * ## Assumptions ##
 * GraphEdge - is ALWAYS a numeric array
 * GraphEdge - is ALWAYS an array of GraphNode types
 * GraphNode - is ALWAYS an associative array
 * GraphNode - MAY contain GraphNode's "recurrable"
 * GraphNode - MAY contain GraphEdge's "recurrable"
 * GraphNode - MAY contain DateTime's "primitives"
 * GraphNode - MAY contain string's "primitives"
 */
class GraphNodeFactory
{
    /**
     * The base graph object class.
     */
    const BASE_GRAPH_NODE_CLASS = GraphNode::class;

    /**
     * The base graph edge class.
     */
    const BASE_GRAPH_EDGE_CLASS = GraphEdge::class;

    /**
     * The graph object prefix.
     */
    const BASE_GRAPH_OBJECT_PREFIX = __NAMESPACE__ . "\\";

    /**
     * @var array The decoded body of the Response entity from Graph.
     */
    protected array $decodedBody;

    public function __construct(protected Response $response)
    {
        $this->decodedBody = $response->getDecodedBody();
    }

    /**
     * Convenience method for creating a GraphAchievement collection.
     *
     * @throws SDKException
     */
    public function makeGraphAchievement(): GraphAchievement
    {
        return $this->makeGraphNode(
            GraphAchievement::class
        );
    }

    /**
     * Tries to convert a Response entity into a GraphNode.
     *
     * @throws SDKException
     */
    public function makeGraphNode(string $subclassName = null): GraphNode
    {
        $this->validateResponseAsArray();
        $this->validateResponseCastableAsGraphNode();

        return $this->castAsGraphNodeOrGraphEdge($this->decodedBody, $subclassName);
    }

    /**
     * Validates the decoded body.
     *
     * @throws SDKException
     */
    public function validateResponseAsArray()
    {
        if (
            !isset($this->decodedBody) ||
            !is_array($this->decodedBody)
        ) {
            throw new SDKException('Unable to get response from Graph as array.', 620);
        }
    }

    /**
     * Validates that the return data can be cast as a GraphNode.
     *
     * @throws SDKException
     */
    public function validateResponseCastableAsGraphNode()
    {
        if (isset($this->decodedBody['data']) && static::isCastableAsGraphEdge($this->decodedBody['data'])) {
            throw new SDKException(
                'Unable to convert response from Graph to a GraphNode because the response looks like a GraphEdge. Try using GraphNodeFactory::makeGraphEdge() instead.',
                620
            );
        }
    }

    /**
     * Determines whether or not the data should be cast as a GraphEdge.
     */
    public static function isCastableAsGraphEdge(array $data): bool
    {
        if ($data === []) {
            return true;
        }

        // Checks for a sequential numeric array which would be a GraphEdge
        return array_keys($data) === range(0, count($data) - 1);
    }

    /**
     * Takes an array of values and determines how to cast each node.
     *
     * @throws SDKException
     */
    public function castAsGraphNodeOrGraphEdge(
        array $data,
        string $subclassName = null,
        string $parentKey = null,
        string $parentNodeId = null
    ): GraphNode|GraphEdge
    {
        if (isset($data['data'])) {
            // Create GraphEdge
            if (static::isCastableAsGraphEdge($data['data'])) {
                return $this->safelyMakeGraphEdge($data, $subclassName, $parentKey, $parentNodeId);
            }

            // Sometimes Graph is a weirdo and returns a GraphNode under the "data" key
            $outerData = $data;
            unset($outerData['data']);
            $data = $data['data'] + $outerData;
        }

        // Create GraphNode
        return $this->safelyMakeGraphNode($data, $subclassName);
    }

    /**
     * Return an array of GraphNode's.
     *
     * @throws SDKException
     */
    public function safelyMakeGraphEdge(
        array $data,
        string $subclassName = null,
        string $parentKey = null,
        string $parentNodeId = null
    ): GraphEdge
    {
        if (!isset($data['data'])) {
            throw new SDKException('Cannot cast data to GraphEdge. Expected a "data" key.', 620);
        }

        $dataList = [];
        foreach ($data['data'] as $graphNode) {
            $dataList[] = $this->safelyMakeGraphNode($graphNode, $subclassName);
        }

        $metaData = $this->getMetaData($data);

        // We'll need to make an edge endpoint for this in case it's a GraphEdge (for cursor pagination)
        $parentGraphEdgeEndpoint = $parentNodeId && $parentKey ? '/' . $parentNodeId . '/' . $parentKey : null;
        $className = static::BASE_GRAPH_EDGE_CLASS;

        return new $className($this->response->getRequest(), $dataList, $metaData, $parentGraphEdgeEndpoint, $subclassName);
    }

    /**
     * Safely instantiates a GraphNode of $subclassName.
     *
     * @throws SDKException
     */
    public function safelyMakeGraphNode(array $data, string $subclassName = null): GraphNode
    {
        /** @var GraphNode $subclassName */
        $subclassName = $subclassName ?: static::BASE_GRAPH_NODE_CLASS;
        static::validateSubclass($subclassName);

        // Remember the parent node ID
        $parentNodeId = $data['id'] ?? null;

        $items = [];

        foreach ($data as $k => $v) {
            // Array means could be recurable
            if (is_array($v)) {
                // Detect any smart-casting from the $graphObjectMap array.
                // This is always empty on the GraphNode collection, but subclasses can define
                // their own array of smart-casting types.
                $graphObjectMap = $subclassName::getObjectMap();
                $objectSubClass = $graphObjectMap[$k] ?? null;

                // Could be a GraphEdge or GraphNode
                $items[$k] = $this->castAsGraphNodeOrGraphEdge($v, $objectSubClass, $k, $parentNodeId);
            }
            else {
                $items[$k] = $v;
            }
        }

        return new $subclassName($items);
    }

    /**
     * Ensures that the subclass in question is valid.
     *
     * @throws SDKException
     */
    public static function validateSubclass(string $subclassName)
    {
        if (
            $subclassName == static::BASE_GRAPH_NODE_CLASS ||
            is_subclass_of($subclassName, static::BASE_GRAPH_NODE_CLASS)
        ) {
            return;
        }

        throw new SDKException('The given subclass "' . $subclassName . '" is not valid. Cannot cast to an object that is not a GraphNode subclass.', 620);
    }

    /**
     * Get the meta data from a list in a Graph response.
     */
    public function getMetaData(array $data): array
    {
        if (isset($data['data'])) {
            unset($data['data']);
        }

        return $data;
    }

    /**
     * Convenience method for creating a GraphAlbum collection.
     *
     * @throws SDKException
     */
    public function makeGraphAlbum(): GraphAlbum
    {
        return $this->makeGraphNode(
            GraphAlbum::class
        );
    }

    /**
     * Convenience method for creating a GraphPage collection.
     *
     * @throws SDKException
     */
    public function makeGraphPage(): GraphPage
    {
        return $this->makeGraphNode(
            GraphPage::class
        );
    }

    /**
     * Convenience method for creating a GraphSessionInfo collection.
     *
     * @throws SDKException
     */
    public function makeGraphSessionInfo(): GraphSessionInfo
    {
        return $this->makeGraphNode(
            GraphSessionInfo::class
        );
    }

    /**
     * Convenience method for creating a GraphUser collection.
     *
     * @throws SDKException
     */
    public function makeGraphUser(): GraphUser
    {
        return $this->makeGraphNode(
            GraphUser::class
        );
    }

    /**
     * Convenience method for creating a GraphEvent collection.
     *
     * @throws SDKException
     */
    public function makeGraphEvent(): GraphEvent
    {
        return $this->makeGraphNode(
            GraphEvent::class
        );
    }

    /**
     * Convenience method for creating a GraphGroup collection.
     *
     * @throws SDKException
     */
    public function makeGraphGroup(): GraphGroup
    {
        return $this->makeGraphNode(
            GraphGroup::class
        );
    }

    /**
     * Tries to convert a Response entity into a GraphEdge.
     *
     * @throws SDKException
     */
    public function makeGraphEdge(string $subclassName = null, bool $auto_prefix = true): GraphEdge
    {
        $this->validateResponseAsArray();
        $this->validateResponseCastableAsGraphEdge();

        if ($subclassName && $auto_prefix) {
            $subclassName = static::BASE_GRAPH_OBJECT_PREFIX . $subclassName;
        }

        return $this->castAsGraphNodeOrGraphEdge($this->decodedBody, $subclassName);
    }

    /**
     * Validates that the return data can be cast as a GraphEdge.
     *
     * @throws SDKException
     */
    public function validateResponseCastableAsGraphEdge(): void
    {
        if (!(isset($this->decodedBody['data']) && static::isCastableAsGraphEdge($this->decodedBody['data']))) {
            throw new SDKException(
                'Unable to convert response from Graph to a GraphEdge because the response does not look like a GraphEdge. Try using GraphNodeFactory::makeGraphNode() instead.',
                620
            );
        }
    }
}
