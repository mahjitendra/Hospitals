<?php

namespace App\Libraries\Core;

/**
 * Class Session
 *
 * A wrapper for PHP's session handling functions to provide a more
 * object-oriented and convenient way to manage session data.
 */
class Session
{
    /**
     * Session constructor.
     * Starts the session if it hasn't been started already.
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Sets a value in the session.
     *
     * @param string $key The key to store the value under.
     * @param mixed $value The value to store.
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Gets a value from the session.
     *
     * @param string $key The key of the value to retrieve.
     * @param mixed|null $default The default value to return if the key is not found.
     * @return mixed The session value or the default.
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Checks if a key exists in the session.
     *
     * @param string $key The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Removes a key from the session.
     *
     * @param string $key The key to remove.
     */
    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroys the entire session.
     */
    public function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Sets a "flash" message that will be removed after being displayed once.
     *
     * @param string $key The key for the flash message.
     * @param string $message The message content.
     */
    public function flash(string $key, string $message): void
    {
        $this->set('flash_' . $key, $message);
    }

    /**
     * Retrieves a "flash" message and then removes it from the session.
     *
     * @param string $key The key of the flash message to retrieve.
     * @return string|null The message or null if not found.
     */
    public function getFlash(string $key): ?string
    {
        $message = $this->get('flash_' . $key);
        if ($message) {
            $this->remove('flash_' . $key);
            return $message;
        }
        return null;
    }
}
