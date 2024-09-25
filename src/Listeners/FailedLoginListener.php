<?php

namespace Rappasoft\LaravelAuthenticationLog\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rappasoft\LaravelAuthenticationLog\Notifications\FailedLogin;

class FailedLoginListener
{
    public Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle($event): void
    {
        $listener = config('authentication-log.events.failed', Failed::class);
        if (! $event instanceof $listener) {
            return;
        }

        if ($event->user) {
            $log = $event->user->authentications()->create([
                'ip_address' => $ip = $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'login_at' => now(),
                'login_successful' => false,
                'username' => $this->request->get('username'),
                'password' => $this->request->get('password'),
                'device_name' => $this->request->get('device_name'),
//                'location' => config('authentication-log.notifications.new-device.location') ? optional(geoip()->getLocation($ip))->toArray() : null,
            ]);

            // if (config('authentication-log.notifications.failed-login.enabled')) {
            //     $failedLogin = config('authentication-log.notifications.failed-login.template') ?? FailedLogin::class;
            //     $event->user->notify(new $failedLogin($log));
            // }
        }else {
            DB::table('authentication_log')->insert([
                'ip_address' => $ip = $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'login_at' => now(),
                'login_successful' => false,
                'username' => $this->request->get('username'),
                'password' => null,
                'device_name' => $this->request->get('device_name'),
//                'location' => config('authentication-log.notifications.new-device.location') ? optional(geoip()->getLocation($ip))->toArray() : null,
            ]);
        }
    }
}
