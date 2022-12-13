<?php

namespace One23\GraphSdk\GraphNodes;

/**
 * Class GraphCoverPhoto

 */
class GraphCoverPhoto extends GraphNode
{
    /**
     * Returns the id of cover if it exists
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getField('id');
    }

    /**
     * Returns the source of cover if it exists
     *
     * @return string|null
     */
    public function getSource()
    {
        return $this->getField('source');
    }

    /**
     * Returns the offset_x of cover if it exists
     *
     * @return int|null
     */
    public function getOffsetX()
    {
        return $this->getField('offset_x');
    }

    /**
     * Returns the offset_y of cover if it exists
     *
     * @return int|null
     */
    public function getOffsetY()
    {
        return $this->getField('offset_y');
    }
}
