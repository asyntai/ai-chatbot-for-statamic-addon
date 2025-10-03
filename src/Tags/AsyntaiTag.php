<?php

namespace Asyntai\Statamic\Chatbot\Tags;

use Asyntai\Statamic\Chatbot\Settings;
use Statamic\Tags\Tags;

class AsyntaiTag extends Tags
{
    public function wildcard($tag)
    {
        $settings = Settings::instance();
        $siteId = trim((string) $settings->get('site_id', ''));
        if ($siteId === '') {
            return '';
        }
        $scriptUrl = trim((string) $settings->get('script_url', 'https://asyntai.com/static/js/chat-widget.js'));
        return '<script async defer src="'.e($scriptUrl).'" data-asyntai-id="'.e($siteId).'"></script>';
    }
}


