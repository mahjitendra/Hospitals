<?php

namespace App\Libraries\Core;

/**
 * Class Response
 *
 * Handles the outgoing HTTP response.
 */
class Response
{
    /**
     * Sets the HTTP response status code.
     *
     * @param int $code The HTTP status code (e.g., 200, 404, 500).
     */
    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    /**
     * Redirects to another URL.
     *
     * @param string $url The URL to redirect to.
     */
    public function redirect(string $url)
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Sends a JSON response.
     *
     * @param mixed $data The data to encode as JSON.
     * @param int $statusCode The HTTP status code.
     */
    public function json($data, int $statusCode = 200)
    {
        $this->setStatusCode($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
