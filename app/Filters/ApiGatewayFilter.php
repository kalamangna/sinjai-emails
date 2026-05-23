<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ApiGatewayFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during normal execution.
     * However, when an abnormal state is found, it should return an instance of
     * ResponseInterface. If it does, script execution will end and that Response
     * will be sent back to the client, allowing for error pages, redirects, etc.
     *
     * @param array|null $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Allow if user is already logged in (Session-based access for browser)
        if (session()->get('isLoggedIn')) {
            return;
        }

        $validToken = env('API_GATEWAY_TOKEN');

        if (empty($validToken)) {
            return Services::response()
                ->setStatusCode(500)
                ->setJSON(['status' => 'error', 'message' => 'API Gateway is not properly configured (token missing in .env).']);
        }

        // 2. Check Bearer Token in Header (for external application access)
        $authHeader = $request->getHeaderLine('Authorization');
        if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $providedToken = $matches[1];
            if ($providedToken === $validToken) {
                return;
            }
            return Services::response()
                ->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden: Invalid API Gateway token.']);
        }

        return Services::response()
            ->setStatusCode(401)
            ->setJSON(['status' => 'error', 'message' => 'Unauthorized: Please log in or provide a valid Bearer token in the header.']);
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param array|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
