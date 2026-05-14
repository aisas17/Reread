<?php
declare(strict_types=1);

if (!function_exists('reread_lang')) {

    function reread_lang(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $l = $_SESSION['lang'] ?? 'en';
        return $l === 'km' ? 'km' : 'en';
    }

    /**
     * @param array<string, string|int|float> $replace
     */
    function t(string $key, array $replace = []): string
    {
        static $bundles = null;
        if ($bundles === null) {
            $bundles = require __DIR__ . '/i18n_strings.php';
        }
        $lang = reread_lang();
        $str = $bundles[$lang][$key] ?? $bundles['en'][$key] ?? $key;
        if ($replace !== []) {
            foreach ($replace as $k => $v) {
                $str = str_replace('{' . $k . '}', (string) $v, $str);
            }
        }
        return $str;
    }
}
