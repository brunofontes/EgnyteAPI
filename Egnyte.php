<?php
namespace brunofontes;

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

        if (!empty($params['postUrl'])) {
            $params['header'][] = 'x-www-form-urlencoded';
            $defaults[CURLOPT_CUSTOMREQUEST] = 'POST';
            $defaults[CURLOPT_POSTFIELDS] = http_build_query($params['postUrl']);
        }

        if (isset($this->token)) {
            $params['header'][] = "Authorization: Bearer {$this->token}";
        }

        if (!empty($params['header'])) {
            $defaults[CURLOPT_HTTPHEADER] = $params['header'];
        }

        if (!empty($params['plain'])) {
            $params['header'][] = 'Content-Type: text/plain';
            $defaults[CURLOPT_POSTFIELDS] = $params['plain'];
        }

        $ch = curl_init();
        curl_setopt_array($ch, ($defaults));
        $jsonResult = curl_exec($ch);
        curl_close($ch);
        return json_decode($jsonResult, true) ?? $jsonResult;
    }

    public function getToken($id, $secret, $user, $pass)
    {
        if (!$this->token) {
            $url = '/puboauth/token';
            $params['postUrl'] = ['client_id' => $id, 'client_secret' => $secret, 'username' => $user, 'password' => $pass, 'grant_type' => 'password', 'scope' => 'Egnyte.filesystem Egnyte.user'];
            $ttt = $this->curl($url, $params);
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
        $path = $this->fixUrl($path);
        $url = "/pubapi/v1/fs{$path}";
        $params['json'] = ['action' => 'add_folder'];
        return $this->curl($url, $params);
    }

    public function uploadFile($source, $target)
    {
        $target = $this->fixUrl($target);
        $sha512 = hash('sha512', $source, false);
        $file = new \CURLFile($source, mime_content_type($source));
        $filecontents = file_get_contents($source);
        $url = "/pubapi/v1/fs-content/{$target}";
        $params['header']['X-Sha512-Checksum'] = $sha512;
        $params['json'] = ["file" => $file];
        $params['plain'] = $filecontents;
        return $this->curl($url, $params);
    }

    /**
     * Move a file or folder to a different location.
     * @param string $source Full path to file/folder
     * @param string $target Full absolute destination 
     *        path of file or folder
     */
    public function move(string $source, string $target)
    {
        $source = $this->fixUrl($source);
        $url = "/pubapi/v1/fs/{$source}";
        $params['json'] = [
            'action' => "move",
            'destination' => $target
        ];
        return $this->curl($url, $params);
    }

    /**
     * URL encode the path and fix the wrong slashes and spaces
     */
    private function fixUrl(string $urlPath) :string {
        $urlPath = urlencode($urlPath);
        $urlPath = str_replace("%2F", "/", $urlPath);
        return str_replace("+", "%20", $urlPath);
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

    protected function createFolders(array $folders): array
    {
        $result = [];
        array_map(
            function ($dir) {
                $result[$dir] = $this->createFolder($dir);
            },
            $folders
        );
        return $result;
    }

    public function getUserPermission($user, $folder)
    {
        $url = "/pubapi/v1/perms/user/{$user}";
        $params['get'] = ['folder' => $folder];
        return $this->curl($url, $params);
    }
}
