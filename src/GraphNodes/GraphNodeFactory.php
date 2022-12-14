<?php

namespace One23\GraphSdk\GraphNodes;

use One23\GraphSdk\Response;
use One23\GraphSdk\Exceptions\SDKException;

/**
 * Class GraphNodeFactory

 *
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
     * @const string The base graph object class.
     */
    const BASE_GRAPH_NODE_CLASS = '\One23\GraphSdk\GraphNodes\GraphNode';

    /**
     * @const string The base graph edge class.
     */
    const BASE_GRAPH_EDGE_CLASS = '\One23\GraphSdk\GraphNodes\GraphEdge';

    /**
     * @const string The graph object prefix.
     */
    const BASE_GRAPH_OBJECT_PREFIX = '\One23\GraphSdk\GraphNodes\\';

    /**
     * The response entity from Graph.
     */
    protected Response $response;

    /**
     * @var array The decoded body of the Response entity from Graph.
     */
    protected $decodedBody;

    public function __construct(Response $response)
    {
        $this->response = $response;
        $this->decodedBody = $response->getDecodedBody();
    }

    /**
     * Convenience method for creating a GraphAchievement collection.
     *
     * @throws SDKException
     */
    public function makeGraphAchievement(): GraphAchievement
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphAchievement');
    }

    /**
     * Tries to convert a Response entity into a GraphNode.
     *
     * @param string|null $subclassName The GraphNode sub class to cast to.
     *
     * @return GraphNode
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
        if (!is_array($this->decodedBody)) {
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
     *
     * @param array $data
     *
     * @return boolean
     */
    public static function isCastableAsGraphEdge(array $data)
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
     * @param array       $data         The array of data to iterate over.
     * @param string|null $subclassName The subclass to cast this collection to.
     * @param string|null $parentKey    The key of this data (Graph edge).
     * @param string|null $parentNodeId The parent Graph node ID.
     *
     * @return GraphNode|GraphEdge
     *
     * @throws SDKException
     */
    public function castAsGraphNodeOrGraphEdge(array $data, $subclassName = null, $parentKey = null, $parentNodeId = null)
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
     * @param array       $data         The array of data to iterate over.
     * @param string|null $subclassName The GraphNode subclass to cast each item in the list to.
     * @param string|null $parentKey    The key of this data (Graph edge).
     * @param string|null $parentNodeId The parent Graph node ID.
     *
     * @return GraphEdge
     *
     * @throws SDKException
     */
    public function safelyMakeGraphEdge(array $data, $subclassName = null, $parentKey = null, $parentNodeId = null)
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
     * @param array       $data         The array of data to iterate over.
     * @param string|null $subclassName The subclass to cast this collection to.
     *
     * @return GraphNode
     *
     * @throws SDKException
     */
    public function safelyMakeGraphNode(array $data, $subclassName = null)
    {
        /** @var GraphNode $subclassName */
        $subclassName = $subclassName ?: static::BASE_GRAPH_NODE_CLASS;
        static::validateSubclass($subclassName);

        // Remember the parent node ID
        $parentNodeId = isset($data['id']) ? $data['id'] : null;

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
            } else {
                $items[$k] = $v;
            }
        }

        return new $subclassName($items);
    }

    /**
     * Ensures that the subclass in question is valid.
     *
     * @param string $subclassName The GraphNode subclass to validate.
     *
     * @throws SDKException
     */
    public static function validateSubclass($subclassName)
    {
        if ($subclassName == static::BASE_GRAPH_NODE_CLASS || is_subclass_of($subclassName, static::BASE_GRAPH_NODE_CLASS)) {
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
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphAlbum');
    }

    /**
     * Convenience method for creating a GraphPage collection.
     *
     * @throws SDKException
     */
    public function makeGraphPage(): GraphPage
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphPage');
    }

    /**
     * Convenience method for creating a GraphSessionInfo collection.
     *
     * @throws SDKException
     */
    public function makeGraphSessionInfo(): GraphSessionInfo
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphSessionInfo');
    }

    /**
     * Convenience method for creating a GraphUser collection.
     *
     * @throws SDKException
     */
    public function makeGraphUser(): GraphUser
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphUser');
    }

    /**
     * Convenience method for creating a GraphEvent collection.
     *
     * @throws SDKException
     */
    public function makeGraphEvent(): GraphEvent
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphEvent');
    }

    /**
     * Convenience method for creating a GraphGroup collection.
     *
     * @throws SDKException
     */
    public function makeGraphGroup(): GraphGroup
    {
        return $this->makeGraphNode(static::BASE_GRAPH_OBJECT_PREFIX . 'GraphGroup');
    }

    /**
     * Tries to convert a Response entity into a GraphEdge.
     *
     * @param string|null $subclassName The GraphNode sub class to cast the list items to.
     * @param boolean     $auto_prefix  Toggle to auto-prefix the subclass name.
     *
     * @return GraphEdge
     *
     * @throws SDKException
     */
    public function makeGraphEdge($subclassName = null, $auto_prefix = true)
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
    public function validateResponseCastableAsGraphEdge()
    {
        if (!(isset($this->decodedBody['data']) && static::isCastableAsGraphEdge($this->decodedBody['data']))) {
            throw new SDKException(
                'Unable to convert response from Graph to a GraphEdge because the response does not look like a GraphEdge. Try using GraphNodeFactory::makeGraphNode() instead.',
                620
            );
        }
    }
}
