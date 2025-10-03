@extends('statamic::layout')

@section('title', 'Asyntai AI Chatbot')

@php
    $connected = !empty($site_id);
    $expectedOrigin = 'https://asyntai.com';
    $connectUrl = $expectedOrigin.'/wp-auth?platform=statamic';
@endphp

@section('content')
<div class="flex flex-col gap-4">
    <h1 class="flex items-center text-2xl font-bold">Asyntai AI Chatbot</h1>

    <p id="asyntai-status">
        Status: <span style="color: {{ $connected ? '#008a20' : '#a00' }};">{{ $connected ? 'Connected' : 'Not connected' }}</span>@if($connected && $account_email) as {{ $account_email }} @endif
        @if($connected)
            <button id="asyntai-reset" class="btn btn-sm ml-2">Reset</button>
        @endif
    </p>

    <div id="asyntai-alert" class="text-sm px-3 py-2 rounded" style="display:none;border-left:4px solid #72aee6;background:#f0f6fc;"></div>

    <div id="asyntai-connected-box" style="display: {{ $connected ? 'block' : 'none' }};">
        <div style="max-width:820px;margin:20px 0;padding:20px;border:1px solid #ddd;border-radius:8px;background:#fff;text-align:center;">
            <div style="font-size:20px;font-weight:700;margin-bottom:8px;">Asyntai is now enabled</div>
            <div style="font-size:16px;margin-bottom:16px;">Set up your AI chatbot, review chat logs and more:</div>
            <a class="btn-primary btn" href="https://asyntai.com/dashboard" target="_blank" rel="noopener">Open Asyntai Panel</a>
            <div style="margin-top:16px;font-size:14px;color:#666;">
                <strong>Tip:</strong> If you want to change how the AI answers, please <a href="https://asyntai.com/dashboard#setup" target="_blank" rel="noopener" style="color:#3182ce;text-decoration:underline;">go here</a>.
            </div>
        </div>
    </div>

    <div id="asyntai-popup-wrap" style="display: {{ $connected ? 'none' : 'block' }};">
        <div style="max-width:960px;padding:24px;border:1px solid #ddd;border-radius:8px;background:#fff;text-align:center;">
            <div style="font-size:18px;margin-bottom:12px;">Create a free Asyntai account or sign in to enable the chatbot</div>
            <button id="asyntai-connect-btn" class="btn-primary btn">Get started</button>
            <div style="margin-top:12px;color:#666;">If it doesn't work, <a href="{{ $connectUrl }}" target="_blank" rel="noopener">open the connect window</a>.</div>
        </div>
    </div>
</div>

<script>
(function(){
    function showAlert(msg, ok){
        var el=document.getElementById('asyntai-alert'); if(!el) return;
        el.style.display='block'; el.style.borderLeftColor = ok ? '#00a32a' : '#d63638';
        el.textContent=msg;
    }
    function openPopup(){
        var state='statamic_'+Math.random().toString(36).substr(2,9);
        var base='{{ $connectUrl }}';
        var url=base+(base.indexOf('?')>-1?'&':'?')+'state='+encodeURIComponent(state);
        var w=800,h=720;var y=window.top.outerHeight/2+window.top.screenY-(h/2);var x=window.top.outerWidth/2+window.top.screenX-(w/2);
        var pop=window.open(url,'asyntai_connect','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width='+w+',height='+h+',top='+y+',left='+x);
        if(!pop){ showAlert('Popup blocked. Please allow popups or use the link below.', false); return; }
        pollForConnection(state);
    }
    function pollForConnection(state){
        var attempts=0;
        function check(){
            if(attempts++>60) return;
            var script=document.createElement('script');
            var cb='asyntai_cb_'+Date.now();
            script.src='{{ $expectedOrigin }}'+'/connect-status.js?state='+encodeURIComponent(state)+'&cb='+cb;
            window[cb]=function(data){ try{ delete window[cb]; }catch(e){}
                if(data && data.site_id){ saveConnection(data); return; }
                setTimeout(check, 500);
            };
            script.onerror=function(){ setTimeout(check, 1000); };
            document.head.appendChild(script);
        }
        setTimeout(check, 800);
    }
    function saveConnection(data){
        showAlert('Asyntai connected. Saving…', true);
        var payload={ site_id: data.site_id||'' };
        if(data.script_url) payload.script_url=data.script_url;
        if(data.account_email) payload.account_email=data.account_email;
        fetch('{{ cp_route('asyntai.save') }}', {
            method:'POST', headers:{ 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}' }, credentials:'same-origin',
            body: JSON.stringify(payload)
        }).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }).then(function(json){
            if(!json || !json.success) throw new Error(json && json.error || 'Save failed');
            showAlert('Asyntai connected. Chatbot enabled on all pages.', true);
            var status=document.getElementById('asyntai-status');
            if(status){
                var html='Status: <span style="color:#008a20;">Connected</span>';
                if(payload.account_email){ html+=' as '+payload.account_email; }
                html += ' <button id="asyntai-reset" class="btn btn-sm ml-2">Reset</button>';
                status.innerHTML=html;
            }
            var box=document.getElementById('asyntai-connected-box'); if(box) box.style.display='block';
            var wrap=document.getElementById('asyntai-popup-wrap'); if(wrap) wrap.style.display='none';
        }).catch(function(err){ showAlert('Could not save settings: '+(err && err.message || err), false); });
    }
    function resetConnection(){
        fetch('{{ cp_route('asyntai.reset') }}', { method:'POST', headers:{ 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':'{{ csrf_token() }}' }, credentials:'same-origin' })
        .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
        .then(function(){ window.location.reload(); })
        .catch(function(err){ showAlert('Reset failed: '+(err && err.message || err), false); });
    }
    document.addEventListener('click', function(ev){ var t=ev.target; if(t && t.id==='asyntai-connect-btn'){ ev.preventDefault(); openPopup(); }
        if(t && t.id==='asyntai-reset'){ ev.preventDefault(); resetConnection(); }});
})();
</script>
@endsection


