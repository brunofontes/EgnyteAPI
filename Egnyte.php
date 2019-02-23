<?php
class Egnyte
{
    protected $token;

    protected function curl(string $partUrl, $params = ['header' => null, 'json' => null, 'get' => null])
    {
        $defaults = [
            CURLOPT_URL => 'https://oxo.egnyte.com' . $partUrl,
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true
        ];

        if (!empty($params['get'])) {
            $defaults[CURLOPT_URL] = 'https://oxo.egnyte.com' . $partUrl . '?' . http_build_query($params['get']);
        }

        if (!empty($params['json'])) {
            $json = json_encode($params['json']);
            $params['header'][] = 'Content-Type: application/json';
            $params['header'][] = 'Content-Length: ' . strlen($json);
            $defaults[CURLOPT_CUSTOMREQUEST] = 'POST';
            $defaults[CURLOPT_POSTFIELDS] = $json;
        }

        if (isset($this->token)) {
            $params['header'][] = "Authorization: Bearer {$this->token}";
        }

        if (!empty($params['header'])) {
            $defaults[CURLOPT_HTTPHEADER] = $params['header'];
        }

        $ch = curl_init();
        curl_setopt_array($ch, ($defaults));
        $jsonResult = curl_exec($ch);
        curl_close($ch);
        return json_decode($jsonResult, true);
    }

    public function getToken($id, $user, $pass)
    {
        if (!$this->token) {
            $url = '/puboauth/token';
            $params['get'] = ['client_id' => $id, 'username' => $user, 'password' => $pass, 'grant_type' => 'password'];
            $this->token = $this->curl($url, $params)['access_token'];
        }
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getUserInfo()
    {
        $url = '/pubapi/v1/userinfo';
        return $this->curl($url);
    }

    public function createFolder($path)
    {
        $url = '/pubapi/v1/fs{$path}';
        $params['json'] = ['action' => 'add_folder'];
        $this->curl($url, $params);
    }

    public function createMultipleFolders(array $folders)
    {
        $newFolders = array_reduce($folders, function ($carry, $item) {
        });
    }

    public function getUserPermission($user, $folder)
    {
        $url = "/pubapi/v1/perms/user/{$user}";
        $params['get'] = ['folder' => $folder];
        return $this->curl($url, $params);
        // return $this->curl($url);
    }
}
