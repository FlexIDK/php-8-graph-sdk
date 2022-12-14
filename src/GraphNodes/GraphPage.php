<?php

namespace One23\GraphSdk\GraphNodes;

use One23\GraphSdk\Authentication\AccessToken;

class GraphPage extends GraphNode
{

    protected static array $graphObjectMap = [
        'best_page' => GraphPage::class,
        'global_brand_parent_page' => GraphPage::class,
        'location' => GraphLocation::class,
        'cover' => GraphCoverPhoto::class,
        'picture' => GraphPicture::class,
    ];

    /**
     * Returns the ID for the user's page as a string if present.
     */
    public function getId(): ?string
    {
        return self::mapType(
            $this->getField('id'),
            'str'
        );
    }

    /**
     * Returns the Category for the user's page as a string if present.
     */
    public function getCategory(): ?string
    {
        return self::mapType(
            $this->getField('category'),
            'str'
        );
    }

    /**
     * Returns the Name of the user's page as a string if present.
     */
    public function getName(): ?string
    {
        return self::mapType(
            $this->getField('name'),
            'str'
        );
    }

    /**
     * Returns the best available Page on Facebook.
     */
    public function getBestPage(): ?GraphPage
    {
        return self::mapType(
            $this->getField('best_page'),
            GraphPage::class
        );
    }

    /**
     * Returns the brand's global (parent) Page.
     */
    public function getGlobalBrandParentPage(): ?GraphPage
    {
        return self::mapType(
            $this->getField('global_brand_parent_page'),
            GraphPage::class
        );
    }

    /**
     * Returns the location of this place.
     */
    public function getLocation(): ?GraphLocation
    {
        return self::mapType(
            $this->getField('location'),
            GraphLocation::class
        );
    }

    /**
     * Returns CoverPhoto of the Page.
     */
    public function getCover(): ?GraphCoverPhoto
    {
        return self::mapType(
            $this->getField('cover'),
            GraphCoverPhoto::class
        );
    }

    /**
     * Returns Picture of the Page.
     */
    public function getPicture(): ?GraphPicture
    {
        return self::mapType(
            $this->getField('picture'),
            GraphPicture::class
        );
    }

    /**
     * Returns the page access token for the admin user.
     *
     * Only available in the `/me/accounts` context.
     */
    public function getAccessToken(): AccessToken|string|null
    {
        $accessToken = $this->getField('access_token');

        if ($accessToken instanceof AccessToken) {
            return $accessToken;
        }

        return self::mapType(
            $accessToken,
            'str'
        );
    }

    /**
     * Returns the roles of the page admin user.
     *
     * Only available in the `/me/accounts` context.
     */
    public function getPerms(): ?array
    {
        return self::mapType(
            $this->getField('perms'),
            'arr'
        );
    }

    /**
     * Returns the `fan_count` (Number of people who likes to page) as int if present.
     */
    public function getFanCount(): ?int
    {
        return self::mapType(
            $this->getField('fan_count'),
            'intGte0'
        );
    }
}
