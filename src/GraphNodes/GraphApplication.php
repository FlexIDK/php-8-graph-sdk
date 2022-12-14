<?php

namespace One23\GraphSdk\GraphNodes;

class GraphApplication extends GraphNode
{
    /**
     * Returns the ID for the application.
     */
    public function getId(): ?string
    {
        return self::mapType(
            $this->getField('id'),
            'str'
        );
    }
}
