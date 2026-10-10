<?php
// proxy-check-batch.php — параллельная проверка пачки прокси через curl_multi.
// Этап 1: перебор протоколов (указанный в адресе — первым) на «Цели проверки».
// Этап 2 (параллельно, не удлиняет пачку): скорость — 1 МБ по HTTPS; проверка TLS — маленький HTTPS-запрос
// с проверкой сертификата (ловит подмену); для HTTP/HTTPS-прокси — запрос к судье заголовков.
// Итоговый статус анонимности собирает браузер: tls + hdr отсюда + пометки ip-api из proxy-geoip.php.

require __DIR__ . '/proxy-lib.php';
header('Content-Type: application/json; charset=utf-8');
cli_request();

if (function_exists('set_time_limit')) {
    @set_time_limit(300);
}

$input_data = json_decode(request_body(), true) ?: $_REQUEST;

$proxies_list    = $input_data['proxies'] ?? [];
$target_url      = trim($input_data['target_url'] ?? $input_data['target'] ?? 'http://cp.cloudflare.com/generate_204');
$timeout_param   = intval($input_data['timeout'] ?? 6);
$timeout         = max(5, min(20, $timeout_param));

if (!$target_url || $target_url === 'cf' || $target_url === 'cloudflare') {
    $target_url = 'http://cp.cloudflare.com/generate_204';
} elseif ($target_url === 'web' || $target_url === 'google') {
    $target_url = 'https://www.google.com/generate_204';
} elseif ($target_url === 'tg' || $target_url === 'telegram') {
    $target_url = 'https://api.telegram.org';
} elseif (!preg_match('#^https?://#i', $target_url)) {
    $target_url = 'http://' . $target_url;
}

const SPEED_TEST_URL = 'https://speed.cloudflare.com/__down?bytes=1000000';
const TLS_CHECK_URL  = 'https://speed.cloudflare.com/__down?bytes=0';
// Судья заголовков: отдаёт полученные заголовки строками «HTTP_ИМЯ = значение» и REMOTE_ADDR.
// Только чистый HTTP: в HTTPS (туннель CONNECT) прокси заголовки добавить не может. Свой судья на сайте не подходит:
// Cloudflare перенаправляет HTTP на HTTPS и сам добавляет X-Forwarded-For.
const JUDGE_URL = 'http://azenv.net/';
// Заголовки, которыми прокси выдаёт себя (в формате судьи)
const PROXY_HEADERS = [
    'HTTP_VIA', 'HTTP_X_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_X_REAL_IP',
    'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_ORIGINATING_IP', 'HTTP_X_CLUSTER_CLIENT_IP',
    'HTTP_X_FORWARDED_HOST', 'HTTP_X_FORWARDED_SERVER', 'HTTP_X_PROXY_ID', 'HTTP_PROXY_CONNECTION',
    'HTTP_X_PROXY_CONNECTION', 'HTTP_X_BLUECOAT_VIA',
];
const CURLE_TLS_MITM = 60; // CURLE_PEER_FAILED_VERIFICATION: сертификат сайта не прошёл проверку
const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

if (!is_array($proxies_list) || empty($proxies_list)) {
    echo json_encode(['success' => false, 'error' => 'Список прокси пуст', 'results' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

function create_curl_handle(string $addr, string $proto, string $url, int $timeout, bool $verify_tls = false) {
    $PROTO_MAP = [
        'http'   => CURLPROXY_HTTP,
        'https'  => CURLPROXY_HTTPS,
        'socks4' => CURLPROXY_SOCKS4A,
        'socks5' => CURLPROXY_SOCKS5_HOSTNAME,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_PROXY          => $addr,
        CURLOPT_PROXYTYPE      => $PROTO_MAP[$proto],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_NOSIGNAL       => 1,
        CURLOPT_SSL_VERIFYPEER => $verify_tls,
        CURLOPT_SSL_VERIFYHOST => $verify_tls ? 2 : 0,
        // Сертификат самого HTTPS-прокси не проверяем: у бесплатных он почти всегда самоподписанный
        CURLOPT_PROXY_SSL_VERIFYPEER => false,
        CURLOPT_PROXY_SSL_VERIFYHOST => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_USERAGENT      => USER_AGENT,
    ]);
    return $ch;
}

// Прямой запрос с сервера (без прокси): [код ошибки curl, тело]. IPv4 — к прокси сервер тоже ходит по IPv4.
function direct_get(string $url, bool $verify_tls): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 3,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
        CURLOPT_SSL_VERIFYPEER => $verify_tls,
        CURLOPT_SSL_VERIFYHOST => $verify_tls ? 2 : 0,
        CURLOPT_USERAGENT      => USER_AGENT,
    ]);
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    curl_close($ch);
    return [$errno, is_string($body) ? $body : ''];
}

// Ответ судьи → [ИМЯ => значение]; null — это не ответ судьи (ошибка, заглушка прокси)
function parse_judge(string $body): ?array {
    preg_match_all('/^([A-Z0-9_]+) = (.*)$/m', $body, $m);
    $vars = array_combine($m[1], array_map('trim', $m[2]));
    return isset($vars['REMOTE_ADDR']) ? $vars : null;
}

// Заголовки у судьи через прокси в сравнении с прямым запросом: [hdr, имена заголовков]
// ip — в заголовке IP сервера, proxy — прокси выдаёт себя заголовками, clean — чисто
function judge_verdict(array $vars, array $baseline): array {
    $ip_re = '/(?<![\d.])' . preg_quote($baseline['REMOTE_ADDR'], '/') . '(?![\d.])/';
    $found = [];
    foreach ($vars as $name => $value) {
        if (!str_starts_with($name, 'HTTP_')) continue;
        $header = ucwords(strtolower(str_replace('_', '-', substr($name, 5))), '-');
        if (preg_match($ip_re, $value)) {
            return ['ip', $header];
        }
        if (in_array($name, PROXY_HEADERS, true) && !isset($baseline[$name])) {
            $found[] = $header;
        }
    }
    return $found ? ['proxy', implode(', ', $found)] : ['clean', ''];
}

// Итог по одному прокси. ok — живой и качает; опасные тоже ok (статус им ставит браузер).
// tls: ok | mitm — подмена сертификата | na — запрос не прошёл | noca — сервер не умеет проверять сертификаты
// hdr: clean | socks — SOCKS не меняет заголовки | proxy | ip | na — судья через прокси не открылся | nojudge — судья недоступен с сервера
function build_result(array $s, bool $tls_checked, ?array $baseline): array {
    $r = [
        'index' => $s['index'], 'proxy' => $s['raw'], 'ok' => false, 'proto' => '—', 'time' => 0,
        'speed_bits' => 0, 'tls' => '', 'hdr' => '', 'hdr_names' => '',
    ];
    $sp = $s['speed'];
    if (!$s['proto'] || !$sp) return $r;

    // Живой, если скачал 1 МБ или до таймаута успел скачать ощутимый блок (≥ 30 КБ)
    $timeout_with_data = $sp['errno'] === CURLE_OPERATION_TIMEDOUT && $sp['size'] >= 30000;
    if (!($sp['errno'] === 0 || $timeout_with_data) || $sp['code'] < 200 || $sp['code'] >= 400 || $sp['size'] <= 0) {
        return $r;
    }

    $rate = $sp['rate'] > 0 ? $sp['rate'] : $sp['size'] / max(0.001, $sp['total']);

    if ($s['proto'] === 'socks4' || $s['proto'] === 'socks5') {
        [$hdr, $names] = ['socks', ''];
    } elseif (!$baseline) {
        [$hdr, $names] = ['nojudge', ''];
    } elseif (!$s['judge']) {
        [$hdr, $names] = ['na', ''];
    } else {
        [$hdr, $names] = judge_verdict($s['judge'], $baseline);
    }

    return array_merge($r, [
        'ok'         => true,
        'proxy'      => $s['proto'] . '://' . $s['addr'],
        'proto'      => strtoupper($s['proto']),
        'time'       => $s['time'],
        'speed_bits' => (int)round($rate * 8),
        'tls'        => $tls_checked ? $s['tls'] : 'noca',
        'hdr'        => $hdr,
        'hdr_names'  => $names,
    ]);
}

// Калибровка перед пачкой: способен ли сервер вообще проверять сертификаты (на Windows без CA — нет)
// и какие заголовки судья видит без прокси (и IP сервера — REMOTE_ADDR)
$tls_checked = direct_get(TLS_CHECK_URL, true)[0] === 0;
$baseline    = parse_judge(direct_get(JUDGE_URL, false)[1]);

$state   = [];
$handles = []; // id дескриптора => [индекс прокси, этап: probe | speed | tls | judge]
$mh = curl_multi_init();

$add = function ($ch, int $idx, string $kind) use ($mh, &$handles): void {
    curl_multi_add_handle($mh, $ch);
    $handles[(int)$ch] = [$idx, $kind];
};

foreach ($proxies_list as $idx => $item) {
    $raw  = trim((string)(is_array($item) ? ($item['proxy'] ?? '') : $item));
    $norm = normalize_proxy($raw);
    $state[$idx] = [
        'index' => is_array($item) ? ($item['index'] ?? $idx) : $idx,
        'raw'   => $raw,
        'addr'  => '',
        'protos' => [],
        'pi'    => 0,
        'proto' => '',
        'time'  => 0,
        'speed' => null,
        'tls'   => 'na',
        'judge' => null,
    ];
    if ($norm === null) continue; // не прошёл маску — сразу нерабочий

    $hint = str_contains($norm, '://') ? strstr($norm, '://', true) : '';
    $state[$idx]['addr']   = proxy_key($norm);
    $state[$idx]['protos'] = array_values(array_unique(array_filter([$hint, 'http', 'https', 'socks4', 'socks5'])));
    $add(create_curl_handle($state[$idx]['addr'], $state[$idx]['protos'][0], $target_url, $timeout), $idx, 'probe');
}

// Цикл идёт, пока есть незавершённые запросы (в том числе добавленные по ходу)
while ($handles) {
    curl_multi_exec($mh, $active);

    while ($info = curl_multi_info_read($mh)) {
        $ch = $info['handle'];
        [$idx, $kind] = $handles[(int)$ch];
        unset($handles[(int)$ch]);
        $errno = $info['result'];
        $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $total = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $body  = $kind === 'judge' ? (string)curl_multi_getcontent($ch) : '';
        $size  = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        $rate  = curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);

        $s = &$state[$idx];
        if ($kind === 'probe') {
            $proto = $s['protos'][$s['pi']];
            if ($errno === 0 && $code >= 200 && $code < 400) {
                $s['proto'] = $proto;
                $s['time']  = (int)round($total * 1000);

                // Скорость — без проверки сертификата: прокси с подменой TLS тоже рабочий, его пометит этап tls
                $speed_ch = create_curl_handle($s['addr'], $proto, SPEED_TEST_URL, $timeout);
                // Тело не нужно — только объём и скорость; не держим 1 МБ на каждый прокси в памяти
                curl_setopt($speed_ch, CURLOPT_WRITEFUNCTION, fn($c, $data) => strlen($data));
                $add($speed_ch, $idx, 'speed');
                if ($tls_checked) {
                    $add(create_curl_handle($s['addr'], $proto, TLS_CHECK_URL, $timeout, true), $idx, 'tls');
                }
                // SOCKS заголовки не меняет; без эталона сравнивать не с чем
                if ($baseline && ($proto === 'http' || $proto === 'https')) {
                    $add(create_curl_handle($s['addr'], $proto, JUDGE_URL, $timeout), $idx, 'judge');
                }
            } elseif (++$s['pi'] < count($s['protos'])) {
                $add(create_curl_handle($s['addr'], $s['protos'][$s['pi']], $target_url, $timeout), $idx, 'probe');
            }
        } elseif ($kind === 'speed') {
            $s['speed'] = ['errno' => $errno, 'code' => $code, 'size' => $size, 'rate' => $rate, 'total' => $total];
        } elseif ($kind === 'tls') {
            $s['tls'] = match ($errno) { 0 => 'ok', CURLE_TLS_MITM => 'mitm', default => 'na' };
        } else {
            $s['judge'] = ($errno === 0 && $code === 200) ? parse_judge($body) : null;
        }
        unset($s);
    }

    if ($handles && curl_multi_select($mh, 0.5) === -1) {
        usleep(10000);
    }
}

curl_multi_close($mh);

$final_results = array_values(array_map(fn($s) => build_result($s, $tls_checked, $baseline), $state));

echo json_encode([
    'success' => true,
    'count'   => count($final_results),
    'results' => $final_results,
], JSON_UNESCAPED_UNICODE);
