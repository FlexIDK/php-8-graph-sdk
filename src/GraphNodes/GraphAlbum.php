<?php

namespace One23\GraphSdk\GraphNodes;

/**
 * Class GraphAlbum

 */

class GraphAlbum extends GraphNode
{
    protected static array $graphObjectMap = [
        'from'  => GraphUser::class,
        'place' => GraphPage::class,
    ];

    /**
     * Returns the ID for the album.
     */
    public function getId(): ?string
    {
        return self::mapType(
            $this->getField('id'),
            'str'
        );
    }

    /**
     * Returns whether the viewer can upload photos to this album.
     */
    public function getCanUpload(): ?bool
    {
        return self::mapType(
            $this->getField('can_upload'),
            'boolOrNull'
        );
    }

    /**
     * Returns the number of photos in this album.
     */
    public function getCount(): ?int
    {
        return self::mapType(
            $this->getField('count'),
            'intGte0'
        );
    }

    /**
     * Returns the ID of the album's cover photo.
     */
    public function getCoverPhoto(): ?string
    {
        return self::mapType(
            $this->getField('cover_photo'),
            'str'
        );
    }

    /**
     * Returns the time the album was initially created.
     */
    public function getCreatedTime(): ?\DateTime
    {
        return self::mapType(
            $this->getField('created_time'),
            \DateTime::class
        );
    }

    /**
     * Returns the time the album was updated.
     */
    public function getUpdatedTime(): ?\DateTime
    {
        return self::mapType(
            $this->getField('updated_time'),
            \DateTime::class
        );
    }

    /**
     * Returns the description of the album.
     */
    public function getDescription(): ?string
    {
        return self::mapType(
            $this->getField('description'),
            'str'
        );
    }

    /**
     * Returns profile that created the album.
     */
    public function getFrom(): ?GraphUser
    {
        return self::mapType(
            $this->getField('from'),
            GraphUser::class
        );
    }

    /**
     * Returns profile that created the album.
     */
    public function getPlace(): ?GraphPage
    {
        return self::mapType(
            $this->getField('place'),
            GraphPage::class
        );
    }

    /**
     * Returns a link to this album on Facebook.
     */
    public function getLink(): ?string
    {
        return self::mapType(
            $this->getField('link'),
            'url'
        );
    }

    /**
     * Returns the textual location of the album.
     */
    public function getLocation(): ?string
    {
        return self::mapType(
            $this->getField('location'),
            'str'
        );
    }

    /**
     * Returns the title of the album.
     */
    public function getName(): ?string
    {
        return self::mapType(
            $this->getField('name'),
            'str'
        );
    }

    /**
     * Returns the privacy settings for the album.
     */
    public function getPrivacy(): ?string
    {
        return self::mapType(
            $this->getField('privacy'),
            'str'
        );
    }

    /**
     * Returns the type of the album.
     *
     * enum{ profile, mobile, wall, normal, album }
     */
    public function getType(): ?string
    {
        return self::mapType(
            $this->getField('type'),
            'str'
        );
    }
}
