<?php
// proxy-fetch.php — список прокси из открытого источника (?source=...) или из вставленного текста (?source=paste, текст в теле POST).
// Возвращает очищенный маской и дедуплицированный список; хранит его браузер (localStorage), сервер ничего не пишет.

require __DIR__ . '/proxy-lib.php';
header('Content-Type: application/json; charset=utf-8');
cli_request();

$source = strtolower(trim($_REQUEST['source'] ?? 'stormsia'));

// Текстовые списки: протокол (если в строке его нет) => URL
$GH = 'https://raw.githubusercontent.com';
$LIST_SOURCES = [
    'stormsia' => [
        '' => "$GH/stormsia/proxy-list/main/working_proxies.txt",
    ],
    'dinoz0rg' => [
        'socks5' => "$GH/dinoz0rg/proxy-list/main/checked_proxies/socks5.txt",
        'http'   => "$GH/dinoz0rg/proxy-list/main/checked_proxies/http.txt",
        'socks4' => "$GH/dinoz0rg/proxy-list/main/checked_proxies/socks4.txt",
    ],
    'proxifly' => [
        'socks5' => "$GH/proxifly/free-proxy-list/main/proxies/protocols/socks5/data.txt",
        'http'   => "$GH/proxifly/free-proxy-list/main/proxies/protocols/http/data.txt",
        'socks4' => "$GH/proxifly/free-proxy-list/main/proxies/protocols/socks4/data.txt",
    ],
    'thespeedx' => [
        'socks5' => "$GH/TheSpeedX/SOCKS-List/master/socks5.txt",
        'socks4' => "$GH/TheSpeedX/SOCKS-List/master/socks4.txt",
        'http'   => "$GH/TheSpeedX/SOCKS-List/master/http.txt",
    ],
];

function json_out(array $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function http_get(string $url, int $timeout = 20): ?string {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ProxyChecker/1.0',
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code >= 200 && $code < 400 && $res !== false) ? $res : null;
}

function split_lines(string $text): array {
    return preg_split('/\R/', $text, -1, PREG_SPLIT_NO_EMPTY);
}

$lines = [];

if ($source === 'paste') {
    $lines = split_lines(request_body());
} elseif ($source === 'geonode') {
    // API отдаёт не больше 500 за страницу; берём две самые свежие
    for ($page = 1; $page <= 2; $page++) {
        $raw = http_get('https://proxylist.geonode.com/api/proxy-list?' . http_build_query([
            'limit' => 500, 'page' => $page, 'sort_by' => 'lastChecked', 'sort_type' => 'desc',
        ]));
        $data = $raw ? json_decode($raw, true)['data'] ?? null : null;
        if (!is_array($data) || !$data) break;
        foreach ($data as $item) {
            $protos = $item['protocols'] ?? [];
            $proto = '';
            foreach (['socks5', 'socks4', 'https', 'http'] as $p) {
                if (in_array($p, $protos, true)) { $proto = "$p://"; break; }
            }
            $lines[] = $proto . ($item['ip'] ?? '') . ':' . ($item['port'] ?? '');
        }
        if (count($data) < 500) break;
    }
} elseif (isset($LIST_SOURCES[$source])) {
    foreach ($LIST_SOURCES[$source] as $proto => $url) {
        $raw = http_get($url);
        if (!$raw) continue;
        foreach (split_lines($raw) as $line) {
            $line = trim($line);
            $lines[] = ($proto !== '' && !str_contains($line, '://')) ? "$proto://$line" : $line;
        }
    }
} else {
    json_out(['success' => false, 'error' => 'Неизвестный источник: ' . $source]);
}

$proxies = clean_proxy_list($lines);

if (!$proxies) {
    json_out(['success' => false, 'error' => $source === 'paste'
        ? 'Не найдено строк вида IP:ПОРТ или ПРОТОКОЛ://IP:ПОРТ (публичный IPv4, без логина и пароля)'
        : "Не удалось получить прокси из источника $source (источник недоступен или пуст)"]);
}

json_out(['success' => true, 'source' => $source, 'count' => count($proxies), 'proxies' => $proxies]);
