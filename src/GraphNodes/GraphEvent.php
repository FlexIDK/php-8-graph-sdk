<?php

namespace One23\GraphSdk\GraphNodes;

class GraphEvent extends GraphNode
{
    protected static array $graphObjectMap = [
        'cover' => GraphCoverPhoto::class,
        'place' => GraphPage::class,
        'picture' => GraphPicture::class,
        'parent_group' => GraphGroup::class,
    ];

    /**
     * Returns the `id` (The event ID) as string if present.
     */
    public function getId(): ?string
    {
        return self::mapType(
            $this->getField('id'),
            'str'
        );
    }

    /**
     * Returns the `cover` (Cover picture) as GraphCoverPhoto if present.
     */
    public function getCover(): ?GraphCoverPhoto
    {
        return self::mapType(
            $this->getField('cover'),
            GraphCoverPhoto::class
        );
    }

    /**
     * Returns the `description` (Long-form description) as string if present.
     */
    public function getDescription(): ?string
    {
        return self::mapType(
            $this->getField('description'),
            'str'
        );
    }

    /**
     * Returns the `end_time` (End time, if one has been set) as DateTime if present.
     */
    public function getEndTime(): ?\DateTime
    {
        return self::mapType(
            $this->getField('end_time'),
            \DateTime::class
        );
    }

    /**
     * Returns the `is_date_only` (Whether the event only has a date specified, but no time) as bool if present.
     */
    public function getIsDateOnly(): ?bool
    {
        return self::mapType(
            $this->getField('is_date_only'),
            'boolOrNull'
        );
    }

    /**
     * Returns the `name` (Event name) as string if present.
     */
    public function getName(): ?string
    {
        return self::mapType(
            $this->getField('name'),
            'str'
        );
    }

    /**
     * Returns the `owner` (The profile that created the event) as GraphNode if present.
     */
    public function getOwner(): ?GraphNode
    {
        return self::mapType(
            $this->getField('owner'),
            GraphNode::class
        );
    }

    /**
     * Returns the `parent_group` (The group the event belongs to) as GraphGroup if present.
     */
    public function getParentGroup(): ?GraphGroup
    {
        return self::mapType(
            $this->getField('parent_group'),
            GraphGroup::class
        );
    }

    /**
     * Returns the `place` (Event Place information) as GraphPage if present.
     */
    public function getPlace(): ?GraphPage
    {
        return self::mapType(
            $this->getField('place'),
            GraphPage::class
        );
    }

    /**
     * Returns the `privacy` (Who can see the event) as string if present.
     */
    public function getPrivacy(): ?string
    {
        return self::mapType(
            $this->getField('privacy'),
            'str'
        );
    }

    /**
     * Returns the `start_time` (Start time) as DateTime if present.
     */
    public function getStartTime(): ?\DateTime
    {
        return self::mapType(
            $this->getField('start_time'),
            \DateTime::class
        );
    }

    /**
     * Returns the `ticket_uri` (The link users can visit to buy a ticket to this event) as string if present.
     */
    public function getTicketUri(): ?string
    {
        return self::mapType(
            $this->getField('ticket_uri'),
            'str'
        );
    }

    /**
     * Returns the `timezone` (Timezone) as string if present.
     */
    public function getTimezone(): ?string
    {
        return self::mapType(
            $this->getField('timezone'),
            'str'
        );
    }

    /**
     * Returns the `updated_time` (Last update time) as DateTime if present.
     */
    public function getUpdatedTime(): ?\DateTime
    {
        return self::mapType(
            $this->getField('updated_time'),
            \DateTime::class
        );
    }

    /**
     * Returns the `picture` (Event picture) as GraphPicture if present.
     */
    public function getPicture(): ?GraphPicture
    {
        return self::mapType(
            $this->getField('picture'),
            GraphPicture::class
        );
    }

    /**
     * Returns the `attending_count` (Number of people attending the event) as int if present.
     */
    public function getAttendingCount(): ?int
    {
        return self::mapType(
            $this->getField('attending_count'),
            'intGte0'
        );
    }

    /**
     * Returns the `declined_count` (Number of people who declined the event) as int if present.
     */
    public function getDeclinedCount(): ?int
    {
        return self::mapType(
            $this->getField('declined_count'),
            'intGte0'
        );
    }

    /**
     * Returns the `maybe_count` (Number of people who maybe going to the event) as int if present.
     */
    public function getMaybeCount(): ?int
    {
        return self::mapType(
            $this->getField('maybe_count'),
            'intGte0'
        );
    }

    /**
     * Returns the `noreply_count` (Number of people who did not reply to the event) as int if present.
     */
    public function getNoreplyCount(): ?int
    {
        return self::mapType(
            $this->getField('noreply_count'),
            'intGte0'
        );
    }

    /**
     * Returns the `invited_count` (Number of people invited to the event) as int if present.
     */
    public function getInvitedCount(): ?int
    {
        return self::mapType(
            $this->getField('invited_count'),
            'intGte0'
        );
    }
}
