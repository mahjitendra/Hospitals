<?php

namespace App\Controllers\Mobile;

use App\Controllers\BaseController;
use App\Models\Integration\APILog as APILogModel;

/**
 * Class MobileAPIController
 *
 * A placeholder for handling incoming API requests from mobile clients.
 */
class MobileAPIController extends BaseController
{
    private APILogModel $apiLogModel;

    public function __construct()
    {
        $this->apiLogModel = new APILogModel();
    }

    /**
     * A generic handler for mobile API requests.
     *
     * @param string $endpoint
     */
    public function handleRequest(string $endpoint = '')
    {
        // Log the request
        $this->apiLogModel->create([
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'ip_address' => $_SERVER['REMOTE_ADDR'],
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Mobile API endpoint reached.',
            'endpoint' => $endpoint
        ]);
    }
}
