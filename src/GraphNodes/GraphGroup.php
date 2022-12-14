<?php

namespace One23\GraphSdk\GraphNodes;

class GraphGroup extends GraphNode
{
    protected static array $graphObjectMap = [
        'cover' => GraphCoverPhoto::class,
        'venue' => GraphLocation::class,
    ];

    /**
     * Returns the `id` (The Group ID) as string if present.
     */
    public function getId(): ?string
    {
        return self::mapType(
            $this->getField('id'),
            'str'
        );
    }

    /**
     * Returns the `cover` (The cover photo of the Group) as GraphCoverPhoto if present.
     */
    public function getCover(): ?GraphCoverPhoto
    {
        return self::mapType(
            $this->getField('cover'),
            GraphCoverPhoto::class
        );
    }

    /**
     * Returns the `description` (A brief description of the Group) as string if present.
     */
    public function getDescription(): ?string
    {
        return self::mapType(
            $this->getField('description'),
            'str'
        );
    }

    /**
     * Returns the `email` (The email address to upload content to the Group. Only current members of the Group can use this) as string if present.
     */
    public function getEmail(): ?string
    {
        return self::mapType(
            $this->getField('email'),
            'email'
        );
    }

    /**
     * Returns the `icon` (The URL for the Group's icon) as string if present.
     */
    public function getIcon(): ?string
    {
        return self::mapType(
            $this->getField('icon'),
            'str'
        );
    }

    /**
     * Returns the `link` (The Group's website) as string if present.
     */
    public function getLink(): ?string
    {
        return self::mapType(
            $this->getField('link'),
            'url'
        );
    }

    /**
     * Returns the `name` (The name of the Group) as string if present.
     */
    public function getName(): ?string
    {
        return self::mapType(
            $this->getField('name'),
            'str'
        );
    }

    /**
     * Returns the `member_request_count` (Number of people asking to join the group.) as int if present.
     */
    public function getMemberRequestCount(): ?int
    {
        return self::mapType(
            $this->getField('member_request_count'),
            'intGte0'
        );
    }

    /**
     * Returns the `owner` (The profile that created this Group) as GraphNode if present.
     */
    public function getOwner(): ?GraphNode
    {
        return self::mapType(
            $this->getField('owner'),
            GraphNode::class
        );
    }

    /**
     * Returns the `parent` (The parent Group of this Group, if it exists) as GraphNode if present.
     */
    public function getParent(): ?GraphNode
    {
        return self::mapType(
            $this->getField('parent'),
            GraphNode::class
        );
    }

    /**
     * Returns the `privacy` (The privacy setting of the Group) as string if present.
     */
    public function getPrivacy(): ?string
    {
        return self::mapType(
            $this->getField('privacy'),
            'str'
        );
    }

    /**
     * Returns the `updated_time` (The last time the Group was updated (this includes changes in the Group's properties and changes in posts and comments if user can see them)) as \DateTime if present.
     */
    public function getUpdatedTime(): ?\DateTime
    {
        return self::mapType(
            $this->getField('updated_time'),
            \DateTime::class
        );
    }

    /**
     * Returns the `venue` (The location for the Group) as GraphLocation if present.
     */
    public function getVenue(): ?GraphLocation
    {
        return self::mapType(
            $this->getField('venue'),
            GraphLocation::class
        );
    }
}
