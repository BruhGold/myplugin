<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Readme file for local customisations
 *
 * @package    local_myplugin
 * @copyright  Dinh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('TOKEN_OBTAIN_SECRET', "secret");

class APIRequest {
    public static $storage = [];

    public $url;
    public $method;
    public $headers;
    public $params;
    public $payload;
    public $ACCESS_TOKEN_URL;
    public $REFRESH_TOKEN_URL;

    public function __construct($url, $method, array $headers = [], array $params = [], array $payload = []) {
        $this->url = $url;
        $this->method = strtoupper($method);
        $this->headers = $headers;
        $this->params = $params;
        $this->payload = $payload;
        $domain = get_config('local_myplugin', 'dmoj_domain');
        $this->ACCESS_TOKEN_URL = $domain . "/api/token/";
        $this->REFRESH_TOKEN_URL = $domain . "/api/token/refresh/";
    }

    public function send() {
        // Add access token to headers if available
        $access_token = self::$storage['access_token'] ?? null;
        if ($access_token) {
            $this->headers['Authorization'] = "Bearer {$access_token}";
        }

        // Prepare headers for cURL
        $formattedHeaders = [];
        foreach ($this->headers as $key => $value) {
            $formattedHeaders[] = "$key: $value";
        }

        // Add query params to URL if GET or DELETE
        if (in_array($this->method, ['GET', 'DELETE']) && !empty($this->params)) {
            $queryString = http_build_query($this->params);
            $this->url .= (strpos($this->url, '?') === false ? '?' : '&') . $queryString;
        }

        $ch = curl_init();
        // For POST or PUT, add JSON payload
        if (in_array($this->method, ['POST', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->payload));
            $formattedHeaders[] = 'Content-Type: application/json';
        }
        $this->url = html_entity_decode($this->url, ENT_QUOTES | ENT_HTML5);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);

        //echo "<br> Headers sent: <pre>". json_encode($formattedHeaders, JSON_PRETTY_PRINT) . "</pre> <br>";
        $responseBody = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        return [
            'status' => $statusCode,
            'body' => $responseBody,
            'error' => $error ?: null
        ];
    }

    public function GetAccessToken() {
        require_login();
        global $USER;
        //echo "User ID: " . $USER->id . "<br>";
        $payload = [
            "api_secret" => TOKEN_OBTAIN_SECRET,
            "provider" => "moodle",
            "uid" => $USER->id
        ];
        $ch = curl_init($this->ACCESS_TOKEN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $responseBody = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        //echo "Tokens: <br>";
        $responseData = json_decode($responseBody, true);
        //echo "<pre>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";

        if ($statusCode === 200 && isset($responseData['access'], $responseData['refresh'])) {
            self::$storage['access_token'] = $responseData['access'];
            self::$storage['refresh_token'] = $responseData['refresh'];
            return $responseData;
        } else {
            throw new Exception("Failed to obtain access token: HTTP status code $statusCode");
        }
    }

    public function TryRefreshToken() {
        $url = self::$REFRESH_TOKEN_URL;
        $payload = [
            "refresh" => self::$storage["refresh_token"]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $responseBody = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: $error");
        }

        $responseData = json_decode($responseBody, true);

        if ($statusCode === 200 && isset($responseData['access'], $responseData['refresh'])) {
            self::$storage['access_token'] = $responseData['access'];
            self::$storage['refresh_token'] = $responseData['refresh'];
            return $responseData;
        } else {
            throw new Exception("Failed to obtain access token: HTTP $statusCode");
        }
    }

    public function ClearTokens() {
        unset(self::$storage['access_token']);
        unset(self::$storage['refresh_token']);
    }
    
    public function run() {
        $this->GetAccessToken();
        $response = $this->send();
        if ($response["status"] == 401){
            try {
                $this->TryRefreshToken();
                $response = $this->send(); # Retry with access token obtained from refresh token
            } catch (Exception $e) {
                echo "Error refreshing token: " . $e;
                $this->ClearTokens();
                try {
                    $this->GetAccessToken();
                    $response = $this->send(); # Retry with new access token, if refresh token is invalid
                } catch (Exception $e) {
                    echo "Error obtaining new access token: " . $e;
                    if ($this->method == "GET"){
                        $response = $this->send();
                    } else {
                        throw new Exception("Cannot send authenticated request without valid token");
                    }
                }
            }
        }
        return $response;
    }
}
