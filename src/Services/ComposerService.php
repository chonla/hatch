<?php

namespace App\Services;

class ComposerService {
    private $path;
    private $executable;

    function set_path($path) {
        $this->path = $path;
        $this->composer = sprintf("%s/%s", $this->path, "/composer.phar");
        $this->composer_installer = sprintf("%s/%s", $this->path, "/composer_install.php");
    }

    function ensure_available() {
        if (!file_exists($this->composer)) {
            return $this->download_composer();
        } else {
            return $this->update_composer();
        }
    }

    function download_composer() {
        $downloader = new DownloadService();
        return $downloader->download("https://getcomposer.org/installer", $this->composer_installer);
    }

    function update_composer() {
        $invoke_command = sprintf('php %s selfupdate', $this->composer);
        $exit_code = $this->run_cmd($invoke_command);
        return ($exit_code === 0);
    }

    function install_composer() {
        $invoke_command = sprintf('php %s --quiet --install-dir="%s"', $this->composer_installer, $this->path);
        $exit_code = $this->run_cmd($invoke_command);
        return ($exit_code === 0);
    }

    function install_deps() {
        $invoke_command = sprintf('php %s install', $this->composer);
        $exit_code = $this->run_cmd($invoke_command);
        return ($exit_code === 0);
    }

    function update_deps() {
        $invoke_command = sprintf('php %s update', $this->composer);
        $exit_code = $this->run_cmd($invoke_command);
        return ($exit_code === 0);
    }

    function run_cmd($cmd) {
        $output = [];
        $exit_code = 0;
        exec($cmd, $output, $exit_code);
        return $exit_code;
    }
}