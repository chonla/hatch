<?php

namespace App\Services;

class DownloadService {
    public function get_remote_content($url) {
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        $data = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        curl_close ($ch);

        if ($errno) {
            echo $errmsg;
            return FALSE;
        }

        return $data;
    }

    public function download($url, $to) {
        $result = $this->get_remote_content($url);

        if ($result === FALSE) {
            return FALSE;
        }

        $result = file_put_contents($to, $result);

        if ($result === FALSE) {
            return FALSE;
        }
        return TRUE;
    }
}
