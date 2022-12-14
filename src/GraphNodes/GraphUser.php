<?php

namespace One23\GraphSdk\GraphNodes;

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
     */
    public function getName(): ?string
    {
        return self::mapType(
            $this->getField('name'),
            'str'
        );
    }

    /**
     * Returns the first name for the user as a string if present.
     */
    public function getFirstName(): ?string
    {
        return self::mapType(
            $this->getField('first_name'),
            'str'
        );
    }

    /**
     * Returns the middle name for the user as a string if present.
     */
    public function getMiddleName(): ?string
    {
        return self::mapType(
            $this->getField('middle_name'),
            'str'
        );
    }

    /**
     * Returns the last name for the user as a string if present.
     */
    public function getLastName(): ?string
    {
        return self::mapType(
            $this->getField('last_name'),
            'str'
        );
    }

    /**
     * Returns the email for the user as a string if present.
     */
    public function getEmail(): ?string
    {
        return self::mapType(
            $this->getField('email'),
            'email'
        );
    }

    /**
     * Returns the gender for the user as a string if present.
     */
    public function getGender(): ?string
    {
        return self::mapType(
            $this->getField('gender'),
            'str'
        );
    }

    /**
     * Returns the Facebook URL for the user as a string if available.
     */
    public function getLink(): ?string
    {
        return self::mapType(
            $this->getField('link'),
            'url'
        );
    }

    /**
     * Returns the users birthday, if available.
     */
    public function getBirthday(): ?Birthday
    {
        return self::mapType(
            $this->getField('birthday'),
            Birthday::class
        );
    }

    /**
     * Returns the current location of the user as a GraphPage.
     */
    public function getLocation(): ?GraphPage
    {
        return self::mapType(
            $this->getField('location'),
            GraphPage::class
        );
    }

    /**
     * Returns the current location of the user as a GraphPage.
     */
    public function getHometown(): ?GraphPage
    {
        return self::mapType(
            $this->getField('hometown'),
            GraphPage::class
        );
    }

    /**
     * Returns the current location of the user as a GraphUser.
     */
    public function getSignificantOther(): ?GraphUser
    {
        return self::mapType(
            $this->getField('significant_other'),
            GraphUser::class
        );
    }

    /**
     * Returns the picture of the user as a GraphPicture
     */
    public function getPicture(): ?GraphPicture
    {
        return self::mapType(
            $this->getField('picture'),
            GraphPicture::class
        );
    }
}
