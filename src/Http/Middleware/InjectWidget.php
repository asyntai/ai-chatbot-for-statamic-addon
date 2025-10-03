<?php

namespace Asyntai\Statamic\Chatbot\Http\Middleware;

use Asyntai\Statamic\Chatbot\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Statamic\Statamic;

class InjectWidget
{
    public function handle(Request $request, Closure $next)
    {
        /** @var SymfonyResponse $response */
        $response = $next($request);

        // Skip CP and non-HTML responses
        if (method_exists(Statamic::class, 'isCpRoute') && Statamic::isCpRoute()) {
            return $response;
        }
        $ctype = $response->headers->get('Content-Type');
        if (!$ctype || strpos($ctype, 'text/html') === false) {
            return $response;
        }

        $settings = Settings::instance();
        $siteId = trim((string) $settings->get('site_id', ''));
        if ($siteId === '') {
            return $response;
        }
        $scriptUrl = trim((string) $settings->get('script_url', 'https://asyntai.com/static/js/chat-widget.js'));
        $tag = '<script type="text/javascript">(function(){var s=document.createElement("script");s.async=true;s.defer=true;s.src='.
            json_encode($scriptUrl).';s.setAttribute("data-asyntai-id",'.json_encode($siteId).');s.charset="UTF-8";var f=document.getElementsByTagName("script")[0];if(f&&f.parentNode){f.parentNode.insertBefore(s,f);}else{(document.head||document.documentElement).appendChild(s);}})();</script>';

        $content = $response->getContent();
        if (strpos($content, '</body>') !== false) {
            $content = str_replace('</body>', $tag.'</body>', $content);
        } else {
            $content .= $tag;
        }
        $response->setContent($content);
        return $response;
    }
}


