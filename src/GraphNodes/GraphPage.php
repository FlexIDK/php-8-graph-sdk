<?php

namespace One23\GraphSdk\GraphNodes;

use One23\GraphSdk\Authentication\AccessToken;/**
 * Class GraphPage
 */
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
     *
     * @return string|null
     */
    public function getCategory()
    {
        return $this->getField('category');
    }

    /**
     * Returns the Name of the user's page as a string if present.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getField('name');
    }

    /**
     * Returns the best available Page on Facebook.
     *
     * @return GraphPage|null
     */
    public function getBestPage()
    {
        return $this->getField('best_page');
    }

    /**
     * Returns the brand's global (parent) Page.
     *
     * @return GraphPage|null
     */
    public function getGlobalBrandParentPage()
    {
        return $this->getField('global_brand_parent_page');
    }

    /**
     * Returns the location of this place.
     *
     * @return GraphLocation|null
     */
    public function getLocation()
    {
        return $this->getField('location');
    }

    /**
     * Returns CoverPhoto of the Page.
     *
     * @return GraphCoverPhoto|null
     */
    public function getCover()
    {
        return $this->getField('cover');
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
     *
     * @return array|null
     */
    public function getPerms()
    {
        return $this->getField('perms');
    }

    /**
     * Returns the `fan_count` (Number of people who likes to page) as int if present.
     *
     * @return int|null
     */
    public function getFanCount()
    {
        return $this->getField('fan_count');
    }
}
