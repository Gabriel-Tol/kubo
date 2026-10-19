<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});


Events::on('login', static function ($user): void {
    if ($user->inGroup('customer') && session()->has('guest_cart_key')) {
        try {
            (new CartService(db_connect()))->mergeGuestCart((int) $user->id);
        } catch (\Throwable) {
            // Keep the customer's login successful if stale guest-cart items need manual correction.
        }
    }
});

Events::on('register', static function ($user): void {
    $request = service('request');
    $name = trim((string) $request->getPost('full_name'));
    $phone = preg_replace('/\D+/', '', (string) $request->getPost('phone')) ?? '';
    if ($name !== '' && $phone !== '') {
        db_connect()->table('customer_profiles')->insert([
            'user_id' => (int) $user->id, 'full_name' => mb_substr($name, 0, 120),
            'phone' => mb_substr($phone, 0, 30), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
});