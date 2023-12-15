<?php 

namespace app\Services\api\user_app;
use app\Services\api\BaseService;
use Exception;
use Yii;

class IikoConfigService extends BaseService
{
    protected string $IIKO_API_KEY;
    protected string $IIKO_ORG_ID;
    protected string $IIKO_TERMINAL_GROUP_ID;
    protected string $IIKO_BASE_URL;

    public function __construct()
    {
        $this->IIKO_API_KEY = $_ENV['IIKO_API_KEY'];
        $this->IIKO_ORG_ID = $_ENV['IIKO_ORG_ID'];
        $this->IIKO_TERMINAL_GROUP_ID = $_ENV['IIKO_TERMINAL_GROUP_ID'];
        $this->IIKO_BASE_URL = $_ENV['IIKO_BASE_URL'];
    }

    /**
     * Retrieves the organization ID.
     *
     * This function retrieves the organization ID by making a request to the 'organizations' endpoint
     * using the provided API key. It returns an array containing the HTTP status code and the organization ID.
     *
     * @return array Returns an array containing the HTTP status code and the organization ID.
     */
    public function getOrganizationId(): array
    {
        $token = $this->getKey();

        $url = 'organizations';
        $data = '{}';
        try {
            $outData = $this->gateWay($url, $data, $token);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }
        return array(self::HTTP_OK, $outData['organizations'][0]['id']);
    }

    /**
     * Retrieves the terminal ID.
     *
     * @return array Returns an array containing the HTTP status code and the terminal data.
     */
    public function getTerminalId(): array
    {
        $token = $this->getKey();

        $url = 'terminal_groups';
        $data = ['organizationIds' => [$this->IIKO_ORG_ID]];
        try {
            $outData = $this->gateWay($url, $data, $token);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }

        return array(self::HTTP_OK, $outData);
    }

    /**
     * Retrieves the webhook settings from the API.
     *
     * @return array The response from the API containing the HTTP status code and the webhook data.
     */
    public function getWebhook(): array
    {
        $token = $this->getKey();

        $url = 'webhooks/settings';
        $data = ['organizationId' => $this->IIKO_ORG_ID];

        try {
            $outData = $this->gateWay($url, $data, $token);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }
        return array(self::HTTP_OK, $outData);
    }

    /**
     * Sets the webhook for updating settings.
     *
     * @return array Returns an array with the HTTP status code and the response data.
     */
    public function setWebhook()
    {
        $token = $this->getKey();
        $url = 'webhooks/update_settings';
        $data = [
            'organizationId' => $this->IIKO_ORG_ID,
            'webHooksUri' => 'http://' . $_SERVER['HTTP_HOST'] . '/api/user_app/iiko/webhook',
            'authToken' => md5($_ENV['API_PASSWORD']),
            'webHooksFilter' => [
                'stopListUpdateFilter' => [
                    'updates' => true
                ]
            ]
        ];
        try {
            $outData = $this->gateWay($url, $data, $token);
        } catch (Exception $e) {
            return array(self::HTTP_BAD_REQUEST, $e->getMessage());
        }
        return array(self::HTTP_OK, $outData);
    }

    /**
     * Sends a request to the specified URL using the HTTP POST method and returns the response.
     *
     * @param string $url The URL to send the request to.
     * @param array|string $data The data to be sent in the request body.
     * @param string|null $token The authorization token to include in the request header. Defaults to null.
     * @throws Exception If there is an error executing the cURL request.
     * @return array The decoded response data from the server.
     */
    public function gateWay($url, $data, $token = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->IIKO_BASE_URL . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $headers = ["Content-Type: application/json"];
        if ($token) {
            $headers[] = "Authorization: Bearer " . $token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $outData = curl_exec($ch);
        if ($outData === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: $error");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < self::HTTP_OK || $httpCode >= 300) {
            throw new Exception("HTTP Error: Status code $httpCode \n" . print_r(json_decode($outData, true), true));
        }

        $decodedData = json_decode($outData, true);
        if ($decodedData === null && json_last_error() != JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }

        if (!$decodedData) {
            throw new Exception("Feailed to retrieve data from Iiko");
        }

        return $decodedData;
    }

    /**
     * Retrieves the API token.
     *
     * This function retrieves the API token from the session. If the token is not set or has expired,
     * it refreshes the token by sending a request to the access_token endpoint. If the refresh is successful,
     * it updates the token and its expiration timestamp in the session and returns the new token.
     *
     * @return string The API token
     * @throws Exception If the token cannot be refreshed
     */
    public function getKey(): string
    {
        $session = Yii::$app->session;
        $expirationTimestamp = $session->get('apiTokenExpiration');
        $apiToken = $session->get('apiToken');

        // Check if the token is not set or has expired
        if (!$apiToken || !$expirationTimestamp || $expirationTimestamp <= time()) {
            // Token is not set or has been expired, refresh it
            $data = ['apiLogin' => $this->IIKO_API_KEY];
            $url = 'access_token';

            $outData = $this->gateWay($url, $data);
            if (!$outData || !isset($outData['token'])) {
                throw new Exception('Cannot refresh token');
            }

            // Set the new token and its expiration timestamp in the session
            $apiToken = $outData['token'];
            $expirationTimestamp = time() + 60 * 60; // standart life time of token is 1 hour due to documentation
            $session->set('apiToken', $apiToken);
            $session->set('apiTokenExpiration', $expirationTimestamp);
        }
        return $apiToken;
    }
}