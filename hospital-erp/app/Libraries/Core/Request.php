<?php

namespace App\Libraries\Core;

/**
 * Class Request
 *
 * Handles the incoming HTTP request information.
 */
class Request
{
    /**
     * Gets the request URI path.
     *
     * @return string The URI path (e.g., "/users/profile").
     */
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');

        if ($position === false) {
            return $path;
        }

        return substr($path, 0, $position);
    }

    /**
     * Gets the HTTP method of the request.
     *
     * @return string The request method (e.g., "GET", "POST").
     */
    public function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Get a specific value from the GET superglobal.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get a specific value from the POST superglobal.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
}
