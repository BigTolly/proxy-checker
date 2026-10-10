<?php
// proxy-lib.php — общее для всех эндпоинтов: маска адреса прокси и чтение запроса (в том числе из CLI для тестов)

const PROXY_LIMIT = 100000;

// Допустимые протоколы; socks4a/socks5h — те же socks4/socks5 (имена резолвит прокси), отдельно не проверяем
const PROXY_SCHEMES = [
    'http' => 'http', 'https' => 'https',
    'socks4' => 'socks4', 'socks4a' => 'socks4',
    'socks5' => 'socks5', 'socks5h' => 'socks5',
];

// Строка → «протокол://IPv4:порт» или «IPv4:порт»; хвост после порта (страна, пинг…) отрезается.
// null — не прокси: неизвестный протокол, логин/пароль, локальный или зарезервированный IP, порт вне 1–65535.
function normalize_proxy(string $line): ?string {
    if (!preg_match('#^\s*(?:([a-z0-9]+)://)?(\d{1,3}(?:\.\d{1,3}){3}):(\d{1,5})(?=[\s,;|\#/-]|$)#i', $line, $m)) {
        return null;
    }
    $scheme = strtolower($m[1]);
    if ($scheme !== '' && !isset(PROXY_SCHEMES[$scheme])) return null;
    // GLOBAL_RANGE отсекает частные и зарезервированные сети, но не multicast 224.0.0.0/4 — его отдельно
    if (!filter_var($m[2], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_GLOBAL_RANGE) || (int)$m[2] >= 224) return null;
    $port = (int)$m[3];
    if ($port < 1 || $port > 65535) return null;
    return ($scheme !== '' ? PROXY_SCHEMES[$scheme] . '://' : '') . $m[2] . ':' . $port;
}

// Ключ для дедупликации: ip:port без протокола
function proxy_key(string $proxy): string {
    $pos = strpos($proxy, '://');
    return $pos === false ? $proxy : substr($proxy, $pos + 3);
}

// Маска + дедупликация по ip:port (первое вхождение побеждает) + лимит
function clean_proxy_list(array $lines, int $limit = PROXY_LIMIT): array {
    $out = [];
    foreach ($lines as $line) {
        $proxy = normalize_proxy((string)$line);
        if ($proxy === null) continue;
        $key = proxy_key($proxy);
        if (isset($out[$key])) continue;
        $out[$key] = $proxy;
        if (count($out) >= $limit) break;
    }
    return array_values($out);
}

// CLI: php file.php "a=1&b=2" → $_REQUEST
function cli_request(): void {
    global $argv;
    if (PHP_SAPI === 'cli' && !empty($argv[1])) {
        parse_str($argv[1], $params);
        $_REQUEST = array_merge($_REQUEST, $params);
    }
}

// Тело запроса; в CLI php://input пуст, поэтому читаем stdin
function request_body(): string {
    return (string)file_get_contents(PHP_SAPI === 'cli' ? 'php://stdin' : 'php://input');
}
