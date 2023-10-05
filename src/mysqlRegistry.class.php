<?php
declare(strict_types=1);

/**
 * @deprecated
 */
class mysqlRegistry
{
    private static ?mysqlRegistry $instance = null;

    private array $registry = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance(): mysqlRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->registry);
    }

    public function loadFromSession(string $name = 'REGISTRY'): bool
    {
        if (isset($_SESSION[$name])) {
            $this->registry = $_SESSION[$name];

            return true;
        }

        return false;
    }

    public function saveToSession(string $name = 'REGISTRY'): void
    {
        $_SESSION[$name] = $this->registry;
    }

    public function get(string $key): mixed
    {
        if (array_key_exists($key, $this->registry)) {
            return $this->registry[$key];
        }

        return false;
    }

    public function set(string $key, mixed $value)
    {
        $this->registry[$key] = $value;
    }

    public function reset(): void
    {
        $this->registry = [];
    }
}
