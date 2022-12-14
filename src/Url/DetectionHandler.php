<?php

namespace One23\GraphSdk\Url;

use One23\GraphSdk\MapTypeTrait;

class DetectionHandler implements DetectionInterface
{
    use MapTypeTrait;

    public function getCurrentUrl(): string
    {
        return $this->getHttpScheme() . '://' . $this->getHostName() . $this->getServerVar('REQUEST_URI');
    }

    /**
     * Get the currently active URL scheme.
     */
    protected function getHttpScheme(): string
    {
        return $this->isBehindSsl() ? 'https' : 'http';
    }

    /**
     * Tries to detect if the server is running behind an SSL.
     */
    protected function isBehindSsl(): bool
    {
        // Check for proxy first
        $protocol = $this->getHeader('X_FORWARDED_PROTO');
        if ($protocol) {
            return $this->protocolWithActiveSsl((string)$protocol);
        }

        $protocol = $this->getServerVar('HTTPS');
        if ($protocol) {
            return $this->protocolWithActiveSsl((string)$protocol);
        }

        return !!((string)$this->getServerVar('SERVER_PORT') === '443');
    }

    /**
     * Gets a value from the HTTP request headers.
     */
    protected function getHeader(string $key): string
    {
        return self::mapType(
            $this->getServerVar('HTTP_' . $key),
            'str',
            ''
        );
    }

    /**
     * Returns the a value from the $_SERVER super global.
     */
    protected function getServerVar(string $key): string
    {
        return $_SERVER[$key] ?? '';
    }

    /**
     * Detects an active SSL protocol value.
     */
    protected function protocolWithActiveSsl(string $protocol): bool
    {
        $protocol = strtolower($protocol);

        return in_array($protocol, ['on', '1', 'https', 'ssl'], true);
    }

    /**
     * Tries to detect the host name of the server.
     *
     * Some elements adapted from
     *
     * @see https://github.com/symfony/HttpFoundation/blob/master/Request.php
     */
    protected function getHostName(): string
    {
        // Check for proxy first
        $header = $this->getHeader('X_FORWARDED_HOST');
        if ($header && $this->isValidForwardedHost($header)) {
            $elements = explode(',', $header);
            $host = $elements[count($elements) - 1];
        }
        elseif (!$host = $this->getHeader('HOST')) {
            if (!$host = $this->getServerVar('SERVER_NAME')) {
                $host = $this->getServerVar('SERVER_ADDR');
            }
        }

        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        // Port number
        $scheme = $this->getHttpScheme();
        $port = $this->getCurrentPort();
        $appendPort = ':' . $port;

        // Don't append port number if a normal port.
        if (($scheme == 'http' && $port == '80') || ($scheme == 'https' && $port == '443')) {
            $appendPort = '';
        }

        return $host . $appendPort;
    }

    /**
     * Checks if the value in X_FORWARDED_HOST is a valid hostname
     * Could prevent unintended redirections
     */
    protected function isValidForwardedHost(string $header): bool
    {
        $elements = explode(',', $header);
        $host = $elements[count($elements) - 1];

        return preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $host) //valid chars check
            && 0 < strlen($host) && strlen($host) < 254 //overall length check
            && preg_match("/^[^.]{1,63}(\.[^.]{1,63})*$/", $host); //length of each label
    }

    protected function getCurrentPort(): string
    {
        // Check for proxy first
        $port = $this->getHeader('X_FORWARDED_PORT');
        if ($port) {
            return (string)$port;
        }

        $protocol = (string)$this->getHeader('X_FORWARDED_PROTO');
        if ($protocol === 'https') {
            return '443';
        }

        return (string)$this->getServerVar('SERVER_PORT');
    }
}
