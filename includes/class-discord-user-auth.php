<?php

session_start();

class WC_Discord_UserAuth {


    function __construct() {
    }

    function try_getting_access_token() {
        if ($this->url_has_code_param()) {
            $code = $this->get_url_code();
            $token = $this->get_user_authorization_code($code);
            if (isset($token->access_token)) {
                return $token;
            }
        }
        return NULL;
    }

    function try_getting_current_user($token) {
        if (!$token) return NULL;
        $currentUser = $this->get_current_user($token->access_token);
        if (isset($currentUser->id)) {
            return $currentUser;                
        }
    }

    function url_has_code_param() {
        if (isset($_GET['code'])) return true;
        return false;
    }

    function get_url_code() {
        return $_GET['code'];
    }

    function save_token($accessToken) {
        // $_SESSION['discord_access_token'] = $accessToken;
        $this->accessToken = $accessToken;
    }

    function has_token_on_session_storage() {
        // if (isset($_SESSION['discord_access_token'])) return true;
        if (isset($this->accessToken)) return true;
        return false;
    }

    function get_token_from_session_storage() {
        // return $_SESSION['discord_access_token'];
        return $this->accessToken;
    }

    function get_user_authorization_code($code) {
        $API_ENDPOINT = 'https://discordapp.com/api/oauth2/token';

        $client_secret = get_option('client_secret');
        $client_id = get_option('client_id');
        $redirect_uri = get_option('redirect_uri');

        $params = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_uri,
            'scope' => 'identify guilds.join',
            'code' => $code
        );

        $response = $this->apiRequest($API_ENDPOINT, $params, ['Content-Type: application/x-www-form-urlencoded']);

        return json_decode($response);
    }

    function get_current_user ($accessToken) {
        $API_ENDPOINT = 'https://discordapp.com/api/v6/users/@me';
        
        $headers[] = 'Authorization: Bearer ' .$accessToken. '';

        $response = $this->apiRequest($API_ENDPOINT, NULL, $headers);
        
        return json_decode($response);
    }

    function apiRequest($url, $params, $headers) {
        $ch = curl_init($url);
        if (isset($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($ch);

        return $response;
    }

}


?>