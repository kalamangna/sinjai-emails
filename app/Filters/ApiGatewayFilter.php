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
        $validToken = env('API_GATEWAY_TOKEN');

        if (empty($validToken)) {
            return Services::response()
                ->setStatusCode(500)
                ->setJSON(['status' => 'error', 'message' => 'API Gateway is not properly configured (token missing in .env).']);
        }

        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized: Bearer token is missing in the header.']);
        }

        $providedToken = $matches[1];

        if ($providedToken !== $validToken) {
            return Services::response()
                ->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden: Invalid API Gateway token.']);
        }
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
