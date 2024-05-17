<?php
require_once 'config.php';

/**
 * Class AuthMiddleware
 * 
 * This class provides authentication functionality for API requests.
 */
class AuthMiddleware {
    private $apiKey;

    /**
     * AuthMiddleware constructor.
     * 
     * Initializes the AuthMiddleware object and sets the API key from the configuration file.
     */
    public function __construct() {
        $config = include 'config.php';
        $this->apiKey = $config['internal_api_key'];
    }

    /**
     * Authenticates the API request using the provided API key.
     * 
     * @param string $apiKey The API key to authenticate the request.
     * @return void
     */
    public function authenticate($apiKey) {
        if ($this->apiKey !== $apiKey) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized access']);
            exit;
        }
    }
}
?>