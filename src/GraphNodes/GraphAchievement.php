<?php

namespace One23\GraphSdk\GraphNodes;

/**
 * Class GraphAchievement

 */
class GraphAchievement extends GraphNode
{
    /**
     * @var array Maps object key names to Graph object types.
     */
    protected static $graphObjectMap = [
        'from' => '\One23\GraphSdk\GraphNodes\GraphUser',
        'application' => '\One23\GraphSdk\GraphNodes\GraphApplication',
    ];

    /**
     * Returns the ID for the achievement.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getField('id');
    }

    /**
     * Returns the user who achieved this.
     *
     * @return GraphUser|null
     */
    public function getFrom()
    {
        return $this->getField('from');
    }

    /**
     * Returns the time at which this was achieved.
     *
     * @return \DateTime|null
     */
    public function getPublishTime()
    {
        return $this->getField('publish_time');
    }

    /**
     * Returns the app in which the user achieved this.
     *
     * @return GraphApplication|null
     */
    public function getApplication()
    {
        return $this->getField('application');
    }

    /**
     * Returns information about the achievement type this instance is connected with.
     *
     * @return array|null
     */
    public function getData()
    {
        return $this->getField('data');
    }

    /**
     * Returns the type of achievement.
     *
     * @see https://developers.facebook.com/docs/graph-api/reference/achievement
     *
     * @return string
     */
    public function getType()
    {
        return 'game.achievement';
    }

    /**
     * Indicates whether gaining the achievement published a feed story for the user.
     *
     * @return boolean|null
     */
    public function isNoFeedStory()
    {
        return $this->getField('no_feed_story');
    }
}
