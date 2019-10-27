<?php
declare(strict_types=1);

class mysqlRegistry
{
    /**
     * @var mysqlRegistry|null
     */
    private static $instance = null;

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

    /**
     * @return mysqlRegistry
     */
    public static function getInstance(): mysqlRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->registry);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function loadFromSession(string $name = 'REGISTRY'): bool
    {
        if (array_key_exists($name, $_SESSION)) {
            $this->registry = $_SESSION[$name];

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     */
    public function saveToSession(string $name = 'REGISTRY'): void
    {
        $_SESSION[$name] = $this->registry;
    }

    /**
     * @param string $key
     *
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
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, $value)
    {
        $this->registry[$key] = $value;
    }
}
