<?php

namespace Asyntai\Statamic\Chatbot;

use Illuminate\Filesystem\Filesystem;

class Settings
{
    private const DEFAULTS = [
        'site_id' => '',
        'script_url' => 'https://asyntai.com/static/js/chat-widget.js',
        'account_email' => '',
    ];

    private static ?self $instance = null;
    private Filesystem $files;
    private string $path;

    private function __construct()
    {
        $this->files = new Filesystem();
        $dir = storage_path('app/asyntai');
        if (!$this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }
        $this->path = $dir.'/settings.json';
    }

    public static function instance(): self
    {
        if (!static::$instance) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    public function all(): array
    {
        if (!$this->files->exists($this->path)) {
            return self::DEFAULTS;
        }
        $json = $this->files->get($this->path);
        $data = json_decode($json, true) ?: [];
        return array_replace(self::DEFAULTS, $data);
    }

    public function get(string $key, $default = null)
    {
        $all = $this->all();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public function set(array $changes): void
    {
        $data = array_replace(self::DEFAULTS, $this->all(), $changes);
        $this->files->put($this->path, json_encode($data));
    }

    public function reset(): void
    {
        $this->files->put($this->path, json_encode(self::DEFAULTS));
    }
}


