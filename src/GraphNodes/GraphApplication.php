<?php

namespace One23\GraphSdk\GraphNodes;

/**
 * Class GraphApplication

 */

class GraphApplication extends GraphNode
{
    /**
     * Returns the ID for the application.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getField('id');
    }
}
