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
            $params['header'][] = 'Content-Type: application/json';
            $defaults[CURLOPT_CUSTOMREQUEST] = 'POST';
            $defaults[CURLOPT_POSTFIELDS] = json_encode($params['json']);
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
        $url = "/pubapi/v1/fs{$path}";
        $params['json'] = ['action' => 'add_folder'];
        return $this->curl($url, $params);
    }

    public function createMultipleFolders(array $folders)
    {
        $folders = $this->cleanupFolderCreationList($folders);
        $log = $this->createFolders($folders);
    }

    /**
     * Removes parent folders when subfolders are present as
     * they does not need to be manually created.
     *
     * @param array $folders List of folders to be created
     * @return array List of folders with no parent folders if subfolder is present on list
     */
    private function cleanupFolderCreationList(array $folders): array
    {
        $folders = array_unique($folders);
        rsort($folders, SORT_STRING);

        $newFolders = array_reduce($folders, function ($carry, $item) {
            if (empty($carry)) {
                $carry[] = $item;
            } elseif (strpos(end($carry), $item) === false) {
                $carry[] = $item;
            }
            return $carry;
        });
        return $newFolders;
    }

    protected function createFolders(array $folders)
    {
        $result = [];
        array_map(
            function ($dir) {
                $result[$dir] = $this->createFolder($dir);
            },
            $folders
        );
    }

    public function getUserPermission($user, $folder)
    {
        $url = "/pubapi/v1/perms/user/{$user}";
        $params['get'] = ['folder' => $folder];
        return $this->curl($url, $params);
    }
}
