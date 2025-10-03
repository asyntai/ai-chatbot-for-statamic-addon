<?php

namespace Asyntai\Statamic\Chatbot\Http\Controllers\Cp;

use Asyntai\Statamic\Chatbot\Settings;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class AsyntaiController extends CpController
{
    public function index(Request $request)
    {
        $settings = Settings::instance();
        $data = [
            'site_id' => (string) $settings->get('site_id', ''),
            'script_url' => (string) $settings->get('script_url', 'https://asyntai.com/static/js/chat-widget.js'),
            'account_email' => (string) $settings->get('account_email', ''),
        ];

        return view('asyntai::cp.index', $data);
    }

    public function save(Request $request)
    {
        // CP routes are authenticated; keep simple permission model
        $payload = $request->json()->all() ?: $request->all();
        $siteId = isset($payload['site_id']) ? trim((string) $payload['site_id']) : '';
        if ($siteId === '') {
            return response()->json(['success' => false, 'error' => 'missing site_id'], 400);
        }
        $changes = ['site_id' => $siteId];
        if (!empty($payload['script_url'])) {
            $changes['script_url'] = trim((string) $payload['script_url']);
        }
        if (!empty($payload['account_email'])) {
            $changes['account_email'] = trim((string) $payload['account_email']);
        }
        Settings::instance()->set($changes);
        return response()->json(['success' => true]);
    }

    public function reset(Request $request)
    {
        Settings::instance()->reset();
        return response()->json(['success' => true]);
    }
}


