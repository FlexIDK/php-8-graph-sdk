<?php

namespace One23\GraphSdk\GraphNodes;

/**
 * Class GraphAlbum

 */

class GraphAlbum extends GraphNode
{
    /**
     * @var array Maps object key names to Graph object types.
     */
    protected static $graphObjectMap = [
        'from' => '\One23\GraphSdk\GraphNodes\GraphUser',
        'place' => '\One23\GraphSdk\GraphNodes\GraphPage',
    ];

    /**
     * Returns the ID for the album.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getField('id');
    }

    /**
     * Returns whether the viewer can upload photos to this album.
     *
     * @return boolean|null
     */
    public function getCanUpload()
    {
        return $this->getField('can_upload');
    }

    /**
     * Returns the number of photos in this album.
     *
     * @return int|null
     */
    public function getCount()
    {
        return $this->getField('count');
    }

    /**
     * Returns the ID of the album's cover photo.
     *
     * @return string|null
     */
    public function getCoverPhoto()
    {
        return $this->getField('cover_photo');
    }

    /**
     * Returns the time the album was initially created.
     *
     * @return \DateTime|null
     */
    public function getCreatedTime()
    {
        return $this->getField('created_time');
    }

    /**
     * Returns the time the album was updated.
     *
     * @return \DateTime|null
     */
    public function getUpdatedTime()
    {
        return $this->getField('updated_time');
    }

    /**
     * Returns the description of the album.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getField('description');
    }

    /**
     * Returns profile that created the album.
     *
     * @return GraphUser|null
     */
    public function getFrom()
    {
        return $this->getField('from');
    }

    /**
     * Returns profile that created the album.
     *
     * @return GraphPage|null
     */
    public function getPlace()
    {
        return $this->getField('place');
    }

    /**
     * Returns a link to this album on Facebook.
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->getField('link');
    }

    /**
     * Returns the textual location of the album.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->getField('location');
    }

    /**
     * Returns the title of the album.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getField('name');
    }

    /**
     * Returns the privacy settings for the album.
     *
     * @return string|null
     */
    public function getPrivacy()
    {
        return $this->getField('privacy');
    }

    /**
     * Returns the type of the album.
     *
     * enum{ profile, mobile, wall, normal, album }
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->getField('type');
    }
}
