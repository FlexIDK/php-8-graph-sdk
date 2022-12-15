<?php

namespace One23\GraphSdk\Url;

class Manipulator
{
    /**
     * Remove params from a URL.
     */
    public static function removeParamsFromUrl(string $url, array $paramsToFilter): string
    {
        $parts = parse_url($url);

        $query = '';
        if (isset($parts['query'])) {
            $params = [];
            parse_str($parts['query'], $params);

            // Remove query params
            foreach ($paramsToFilter as $paramName) {
                unset($params[$paramName]);
            }

            if (count($params) > 0) {
                $query = '?' . http_build_query($params, "", '&');
            }
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme .
            $host .
            $port .
            '/' . ltrim($path, '/') .
            $query .
            $fragment;
    }

    /**
     * Adds the params of the first URL to the second URL.
     *
     * Any params that already exist in the second URL will go untouched.
     */
    public static function mergeUrlParams(string $urlToStealFrom, string $urlToAddTo): string
    {
        $newParams = static::getParamsAsArray($urlToStealFrom);
        // Nothing new to add, return as-is
        if (!$newParams) {
            return $urlToAddTo;
        }

        return static::appendParamsToUrl($urlToAddTo, $newParams);
    }

    /**
     * Returns the params from a URL in the form of an array.
     */
    public static function getParamsAsArray(string $url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if (!$query) {
            return [];
        }
        $params = [];
        parse_str($query, $params);

        return $params;
    }

    /**
     * Gracefully appends params to the URL.
     */
    public static function appendParamsToUrl(string $url, array $newParams = []): string
    {
        if (empty($newParams)) {
            return $url;
        }

        if (!str_contains($url, '?')) {
            return $url . '?' . http_build_query($newParams, "", '&');
        }

        list($path, $query) = explode('?', $url, 2);
        $existingParams = [];
        parse_str($query, $existingParams);

        // Favor params from the original URL over $newParams
        $newParams = array_merge($newParams, $existingParams);

        // Sort for a predicable order
        ksort($newParams);

        return $path . '?' . http_build_query($newParams, "", '&');
    }

    /**
     * Check for a "/" prefix and prepend it if not exists.
     *
     * @param string|null $string
     *
     * @return string|null
     */
    public static function forceSlashPrefix(string $string = null): ?string
    {
        if (!$string) {
            return $string;
        }

        return str_starts_with($string, '/')
            ? $string
            : '/' . $string;
    }

    /**
     * Trims off the hostname and Graph version from a URL.
     */
    public static function baseGraphUrlEndpoint(string $urlToTrim): string
    {
        return '/' . preg_replace('/^http(s)?:\/\/.+\.(facebook|fb)\.com(\/v.+?)?\//', '', $urlToTrim);
    }
}
