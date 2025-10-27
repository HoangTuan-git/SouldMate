<?php
/**
 * Simple .env file loader for PHP
 * Loads environment variables from .env file into $_ENV and getenv()
 */

class EnvLoader
{
    /**
     * Load .env file from specified path
     * 
     * @param string $path Path to .env file (default: root directory)
     * @return bool True if loaded successfully
     */
    public static function load($path = null)
    {
        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }

        if (!file_exists($path)) {
            // If .env doesn't exist, try to copy from .env.example
            $examplePath = __DIR__ . '/../.env.example';
            if (file_exists($examplePath)) {
                error_log("Warning: .env file not found. Please copy .env.example to .env and configure it.");
            }
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse line: KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                // Set in $_ENV and putenv()
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }

        return true;
    }

    /**
     * Get environment variable value
     * 
     * @param string $key Variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        // Check $_ENV first
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        // Check getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    /**
     * Get boolean environment variable
     * 
     * @param string $key Variable name
     * @param bool $default Default value
     * @return bool
     */
    public static function getBool($key, $default = false)
    {
        $value = self::get($key);
        
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get integer environment variable
     * 
     * @param string $key Variable name
     * @param int $default Default value
     * @return int
     */
    public static function getInt($key, $default = 0)
    {
        $value = self::get($key);
        
        if ($value === null) {
            return $default;
        }

        return (int) $value;
    }
}
