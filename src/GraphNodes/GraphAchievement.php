<?php

namespace One23\GraphSdk\GraphNodes;

class GraphAchievement extends GraphNode
{
    protected static array $graphObjectMap = [
        'from'          => GraphUser::class,
        'application'   => GraphApplication::class,
    ];

    /**
     * Returns the ID for the achievement.
     */
    public function getId(): ?string
    {
        return self::mapType(
            $this->getField('id'),
            'str'
        );
    }

    /**
     * Returns the user who achieved this.
     */
    public function getFrom(): ?GraphUser
    {
        return self::mapType(
            $this->getField('publish_time'),
            GraphUser::class
        );
    }

    /**
     * Returns the time at which this was achieved.
     */
    public function getPublishTime(): ?\DateTime
    {
        return self::mapType(
            $this->getField('publish_time'),
            \DateTime::class
        );
    }

    /**
     * Returns the app in which the user achieved this.
     */
    public function getApplication(): ?GraphApplication
    {
        return self::mapType(
            $this->getField('application'),
            GraphApplication::class
        );
    }

    /**
     * Returns information about the achievement type this instance is connected with.
     */
    public function getData(): ?array
    {
        return self::mapType(
            $this->getField('data'),
            'arr'
        );
    }

    /**
     * Returns the type of achievement.
     *
     * @see https://developers.facebook.com/docs/graph-api/reference/achievement
     */
    public function getType(): string
    {
        return 'game.achievement';
    }

    /**
     * Indicates whether gaining the achievement published a feed story for the user.
     */
    public function isNoFeedStory(): ?bool
    {
        return self::mapType(
            $this->getField('no_feed_story'),
            'boolOrNull'
        );
    }
}
