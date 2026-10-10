<?php
// proxy-geoip.php — пакетное определение страны живых прокси через ip-api.com/batch (до 100 IP за запрос).
// POST {"ips": [...]} → {"success": true, "results": {ip: {country, country_code, proxy, hosting}}, "retry_after": секунд}
// proxy/hosting — признаки ip-api «известный прокси/VPN» и «дата-центр»; клиент по ним понижает статус.
// IP, по которым ip-api не ответил (сеть, лимит), в results нет — браузер повторит их через retry_after секунд.
// Лимит batch-запросов ip-api — 15 в минуту (заголовки X-Rl — осталось, X-Ttl — секунд до сброса); при превышениях бан до часа.

require __DIR__ . '/proxy-lib.php';
header('Content-Type: application/json; charset=utf-8');
cli_request();

$ips_param = json_decode(request_body(), true)['ips'] ?? $_REQUEST['ips'] ?? [];
$ips_list  = is_array($ips_param) ? $ips_param : explode(',', (string)$ips_param);

const GEO_EMPTY = ['country' => '—', 'country_code' => '', 'proxy' => false, 'hosting' => false];

// Каноническое имя страны — английское; интерфейс переводит его по country_code через Intl.DisplayNames.

function parse_clean_ip(string $raw): string {
    return preg_match('#(?:[a-z0-9]+://)?(\d{1,3}(?:\.\d{1,3}){3})#i', trim($raw), $m) ? $m[1] : trim($raw);
}

function fetch_batch_geoip(array $ips, int &$retry_after): array {
    $results = [];
    $to_fetch = [];

    foreach ($ips as $raw) {
        $ip = parse_clean_ip((string)$raw);
        // Локальные и зарезервированные адреса в ip-api не отправляем
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_GLOBAL_RANGE)) {
            $to_fetch[$ip] = true;
        } else {
            $results[$ip] = GEO_EMPTY;
        }
    }

    foreach (array_chunk(array_keys($to_fetch), 100) as $chunk) {
        if ($retry_after) break; // лимит исчерпан — остальные IP браузер повторит позже
        $limit = [];
        $ch = curl_init('http://ip-api.com/batch?lang=en&fields=status,country,countryCode,proxy,hosting,query');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(array_map(fn($ip) => ['query' => $ip], $chunk)),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'User-Agent: ProxyGeoIP/1.0'],
            CURLOPT_HEADERFUNCTION => function ($c, $line) use (&$limit) {
                if (preg_match('/^(X-Rl|X-Ttl):\s*(\d+)/i', $line, $m)) $limit[strtolower($m[1])] = (int)$m[2];
                return strlen($line);
            },
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 429 || ($limit['x-rl'] ?? 1) === 0) {
            $retry_after = max(1, $limit['x-ttl'] ?? 60);
        }
        $data = ($response && $http_code >= 200 && $http_code < 400) ? json_decode($response, true) : null;
        foreach (is_array($data) ? $data : [] as $item) {
            $q = $item['query'] ?? '';
            if (!$q) continue;
            if (($item['status'] ?? '') !== 'success') {
                $results[$q] = GEO_EMPTY;
                continue;
            }
            $code = strtoupper($item['countryCode'] ?? '');
            $results[$q] = [
                'country'      => ($item['country'] ?? '') ?: '—',
                'country_code' => $code,
                'proxy'        => !empty($item['proxy']),
                'hosting'      => !empty($item['hosting']),
            ];
        }
    }

    return $results;
}

$retry_after = 0;
$results = fetch_batch_geoip($ips_list, $retry_after);
echo json_encode(['success' => true, 'results' => (object)$results, 'retry_after' => $retry_after], JSON_UNESCAPED_UNICODE);
