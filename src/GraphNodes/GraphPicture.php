<?php

namespace One23\GraphSdk\GraphNodes;

class GraphPicture extends GraphNode
{
    /**
     * Returns true if user picture is silhouette.
     */
    public function isSilhouette(): ?bool
    {
        return self::mapType(
            $this->getField('is_silhouette'),
            'boolOrNull'
        );
    }

    /**
     * Returns the url of user picture if it exists
     */
    public function getUrl(): ?string
    {
        return self::mapType(
            $this->getField('url'),
            'url'
        );
    }

    /**
     * Returns the width of user picture if it exists
     */
    public function getWidth(): ?int
    {
        return self::mapType(
            $this->getField('width'),
            'intGt0'
        );
    }

    /**
     * Returns the height of user picture if it exists
     */
    public function getHeight(): ?int
    {
        return self::mapType(
            $this->getField('height'),
            'intGt0'
        );
    }
}
