<?php

namespace One23\GraphSdk\GraphNodes;

/**
 * Class GraphUser

 */
class GraphUser extends GraphNode
{
    protected static array $graphObjectMap = [
        'hometown' => GraphPage::class,
        'location' => GraphPage::class,
        'significant_other' => GraphUser::class,
        'picture' => GraphPicture::class,
    ];

    /**
     * Returns the ID for the user as a string if present.
     */
    public function getId(): ?string
    {
        return self::mapType(
            $this->getField('id'),
            'str'
        );
    }

    /**
     * Returns the name for the user as a string if present.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getField('name');
    }

    /**
     * Returns the first name for the user as a string if present.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getField('first_name');
    }

    /**
     * Returns the middle name for the user as a string if present.
     *
     * @return string|null
     */
    public function getMiddleName()
    {
        return $this->getField('middle_name');
    }

    /**
     * Returns the last name for the user as a string if present.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getField('last_name');
    }

    /**
     * Returns the email for the user as a string if present.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getField('email');
    }

    /**
     * Returns the gender for the user as a string if present.
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->getField('gender');
    }

    /**
     * Returns the Facebook URL for the user as a string if available.
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->getField('link');
    }

    /**
     * Returns the users birthday, if available.
     *
     * @return Birthday|null
     */
    public function getBirthday()
    {
        return $this->getField('birthday');
    }

    /**
     * Returns the current location of the user as a GraphPage.
     *
     * @return GraphPage|null
     */
    public function getLocation()
    {
        return $this->getField('location');
    }

    /**
     * Returns the current location of the user as a GraphPage.
     *
     * @return GraphPage|null
     */
    public function getHometown()
    {
        return $this->getField('hometown');
    }

    /**
     * Returns the current location of the user as a GraphUser.
     *
     * @return GraphUser|null
     */
    public function getSignificantOther()
    {
        return $this->getField('significant_other');
    }

    /**
     * Returns the picture of the user as a GraphPicture
     *
     * @return GraphPicture|null
     */
    public function getPicture()
    {
        return $this->getField('picture');
    }
}
