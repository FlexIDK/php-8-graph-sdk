<?php

namespace One23\GraphSdk\GraphNodes;

class GraphCoverPhoto extends GraphNode
{
    /**
     * Returns the id of cover if it exists
     */
    public function getId(): ?string
    {
        return self::mapType(
            $this->getField('id'),
            'str'
        );
    }

    /**
     * Returns the source of cover if it exists
     */
    public function getSource(): ?string
    {
        return self::mapType(
            $this->getField('source'),
            'str'
        );
    }

    /**
     * Returns the offset_x of cover if it exists
     */
    public function getOffsetX(): ?int
    {
        return self::mapType(
            $this->getField('offset_x'),
            'int'
        );
    }

    /**
     * Returns the offset_y of cover if it exists
     */
    public function getOffsetY(): ?int
    {
        return self::mapType(
            $this->getField('offset_y'),
            'int'
        );
    }
}
