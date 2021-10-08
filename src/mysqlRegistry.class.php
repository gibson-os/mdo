<?php
declare(strict_types=1);

class mysqlRegistry
{
    /**
     * @var mysqlRegistry|null
     */
    private static $instance;

    /**
     * @var array
     */
    private $registry = [];

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
        if (array_key_exists($name, $_SESSION)) {
            $this->registry = $_SESSION[$name];

            return true;
        }

        return false;
    }

    public function saveToSession(string $name = 'REGISTRY'): void
    {
        $_SESSION[$name] = $this->registry;
    }

    /**
     * @return bool|mixed
     */
    public function get(string $key)
    {
        if (array_key_exists($key, $this->registry)) {
            return $this->registry[$key];
        }

        return false;
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value)
    {
        $this->registry[$key] = $value;
    }
}
