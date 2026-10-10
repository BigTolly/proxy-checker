<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proxy Checker</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Courier New', monospace;
            font-size: 13px;
            background: #0d1117;
            color: #c9d1d9;
            padding: 16px 20px;
            line-height: 1.5;
            max-width: 1400px;
            margin: 0 auto;
        }
        h1 { color: #f0f6fc; margin-bottom: 12px; font-size: 20px; font-weight: 600; }

        .site-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .site-header h1 { margin-bottom: 0; }
        .language-switch { display: flex; align-items: center; gap: 5px; }
        .language-switch .btn { padding: 3px 8px; }
        .language-switch [aria-pressed="true"] { color: #58a6ff; border-color: #58a6ff; }

        /* Карточки */
        .card {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 14px;
        }

        .flex-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        label { color: #8b949e; font-size: 12px; font-weight: 500; }
        select, input[type="text"], textarea {
            background: #0d1117;
            color: #c9d1d9;
            border: 1px solid #30363d;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
            outline: none;
        }
        select:focus, input:focus, textarea:focus { border-color: #58a6ff; }

        /* Кастомные чекбоксы на SVG */
        .custom-checkbox {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #f0f6fc;
            font-weight: 500;
            user-select: none;
        }
        .custom-checkbox input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            background: transparent;
            border: 1px solid #58a6ff;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
            transition: all 0.15s ease;
            position: relative;
        }
        .custom-checkbox input[type="checkbox"]:checked {
            background-color: transparent;
            border-color: #58a6ff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2358a6ff'%3E%3Cpath d='M13.78 4.22a.75.75 0 0 1 0 1.06l-7.25 7.25a.75.75 0 0 1-1.06 0L2.22 9.28a.751.751 0 0 1 .018-1.042.751.751 0 0 1 1.042-.018L6 10.94l6.72-6.72a.75.75 0 0 1 1.06 0Z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 12px;
        }

        /* Стильные кнопки с глубокими темными градиентами без пульсаций */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.2s ease;
            user-select: none;
            white-space: nowrap;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
        }
        .btn:disabled { opacity: 0.35; cursor: not-allowed; animation: none !important; box-shadow: none; }

        .btn-green {
            background: linear-gradient(180deg, #134e23 0%, #0d3819 100%);
            color: #ffffff;
            border-color: #1a632d;
        }
        .btn-green:hover:not(:disabled) {
            background: linear-gradient(180deg, #1a632d 0%, #134e23 100%);
        }

        .btn-primary {
            background: linear-gradient(180deg, #123e7a 0%, #0c2b57 100%);
            color: #ffffff;
            border-color: #19529e;
        }
        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(180deg, #19529e 0%, #123e7a 100%);
        }

        .btn-secondary {
            background: linear-gradient(180deg, #21262d 0%, #161b22 100%);
            color: #c9d1d9;
            border-color: #30363d;
        }
        .btn-secondary:hover:not(:disabled) {
            background: linear-gradient(180deg, #30363d 0%, #21262d 100%);
            color: #ffffff;
        }

        /* Темные томатные кнопки для Стоп и Очистить список */
        .btn-tomato {
            background: linear-gradient(180deg, #8a1f14 0%, #631209 100%);
            color: #ffffff !important;
            border-color: #a3281b;
        }
        .btn-tomato:hover:not(:disabled) {
            background: linear-gradient(180deg, #a3281b 0%, #7a180e 100%);
            color: #ffffff !important;
        }

        .btn-warning {
            background: linear-gradient(180deg, #593d03 0%, #3d2900 100%);
            color: #ffffff;
            border-color: #7a5404;
        }
        .btn-warning:hover:not(:disabled) {
            background: linear-gradient(180deg, #7a5404 0%, #593d03 100%);
        }

        /* Плавная элегантная анимация градиента без пульсации */
        @keyframes gradient-flow {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .btn-active-flow {
            background-size: 200% 200% !important;
            animation: gradient-flow 3s ease infinite !important;
        }

        /* Липкая панель управления и статистики */
        .sticky-controls {
            position: sticky;
            top: 10px;
            z-index: 100;
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 14px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
        }

        /* Статистика */
        .stats { display: flex; gap: 10px; margin: 10px 0; flex-wrap: wrap; }
        .stat-box {
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 7px 14px;
            min-width: 95px;
        }
        .stat-box .val { font-size: 19px; font-weight: 700; font-family: monospace; }
        .stat-box .lbl { font-size: 11px; color: #8b949e; margin-top: 1px; }

        /* Статусы анонимности: SVG-круги в таблице и в легенде */
        .dot { display: inline-block; flex: none; width: 18px; height: 18px; vertical-align: middle; }
        .dot circle { stroke-width: 1.5; }
        .dot path { fill: none; stroke: #ffffff; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }
        td .dot { cursor: pointer; }
        .dot-5 circle { fill: #238636; stroke: #2ea043; }
        .dot-4 circle { fill: #9e6a03; stroke: #d29922; }
        .dot-3 circle { fill: #bd561d; stroke: #f0883e; }
        .dot-2 circle { fill: #30363d; stroke: #8b949e; }
        .dot-2 path   { stroke: #c9d1d9; }
        .dot-1 circle { fill: #183ea7; stroke: #3c52fd; }
        .dot-0 circle { fill: #da3633; stroke: #f85149; }
        .legend {
            display: grid; grid-template-columns: repeat(3, auto); gap: 3px 14px;
            align-content: center; cursor: pointer; padding: 3px 14px;
        }
        .legend-item { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; color: #8b949e; white-space: nowrap; }
        .legend-row { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 10px; white-space: normal; }
        .legend-row .dot { margin-top: 1px; }
        /* На узком экране подписи легенды прячутся: видны по наведению и по клику */
        @media (max-width: 1000px) {
            .legend { grid-template-columns: repeat(6, auto); gap: 6px; }
            .legend-text { display: none; }
        }

        .green  { color: #3fb950; }
        .red    { color: #f85149; }
        .gray   { color: #8b949e; }
        .yellow { color: #d29922; }

        .progress-wrap {
            background: #21262d;
            border-radius: 4px;
            height: 5px;
            margin-bottom: 8px;
            overflow: hidden;
        }
        .progress-bar { height: 100%; background: #238636; width: 0%; transition: width 0.2s ease; }

        #status { font-size: 12px; color: #8b949e; }

        /* Предупреждение о HTTPS цели для HTTP прокси */
        .warn-tip {
            display: none;
            font-size: 12px;
            color: #d29922;
            background: #272115;
            border: 1px solid #bb8009;
            border-radius: 4px;
            padding: 7px 12px;
            margin-top: 10px;
            line-height: 1.4;
        }
        .warn-tip b { color: #f0883e; }

        /* Таблица */
        .table-wrap {
            overflow-x: auto;
            border: 1px solid #30363d;
            border-radius: 6px;
            background: #161b22;
        }
        table { width: 100%; border-collapse: collapse; font-family: 'Courier New', monospace; font-size: 12px; }
        th {
            background: #161b22;
            color: #8b949e;
            text-align: left;
            padding: 9px 12px;
            border-bottom: 1px solid #30363d;
            font-weight: 600;
            position: sticky;
            top: 0;
            user-select: none;
            z-index: 2;
        }
        th.sortable { cursor: pointer; color: #58a6ff; }
        th.sortable:hover { background: #21262d; }
        th.status-col, td.status-col { text-align: right; }
        td { padding: 7px 12px; border-bottom: 1px solid #21262d; vertical-align: middle; }
        tr:hover td { background: #1c2128; }
        tr.done td { transition: background 0.3s ease; }
        tr.row-hidden { display: none !important; }

        /* Контрастные темные бейджи */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .badge-ok      { background: #0f2d18; color: #3fb950; border: 1px solid #1f6f34; }
        .badge-fail    { background: #331416; color: #f85149; border: 1px solid #b62324; }
        .badge-wait    { background: #2b1f0b; color: #e3b341; border: 1px solid #8a5d02; }
        .badge-country { background: #161b22; color: #c9d1d9; border: 1px solid #30363d; font-size: 11px; }

        .time-ok  { color: #3fb950; font-weight: 600; }
        .time-slow{ color: #d29922; }

        .speed-fast   { color: #3fb950; font-weight: 600; }
        .speed-slow   { color: #d29922; }

        .spinner {
            display: inline-block; width: 10px; height: 10px;
            border: 2px solid #30363d; border-top-color: #58a6ff;
            border-radius: 50%; animation: spin 0.7s linear infinite;
            vertical-align: middle; margin-right: 4px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Модальные окна */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }
        .modal-box {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 8px;
            width: 90%;
            max-width: 550px;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
        }
        .modal-box h3 { margin-bottom: 10px; color: #f0f6fc; font-size: 15px; }
        .modal-box textarea { width: 100%; height: 200px; font-family: monospace; font-size: 12px; margin-bottom: 15px; }
        .modal-msg { color: #c9d1d9; font-size: 13px; line-height: 1.5; margin-bottom: 20px; white-space: pre-line; }

        /* Жадный блок цели проверки */
        .target-block {
            flex: 1;
            display: flex;
            gap: 10px;
            align-items: flex-end;
            min-width: 320px;
        }
        .target-preset-wrap {
            flex: 0 0 260px;
            min-width: 240px;
        }
        .target-url-wrap {
            flex: 1;
            min-width: 250px;
        }

        /* Перенос блока цели при ширине меньше 1300px */
        @media (max-width: 1300px) {
            .target-block {
                width: 100%;
                flex: 1 1 100% !important;
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <h1>Proxy Checker</h1>
    <div class="language-switch" role="group" aria-label="Language / Язык">
        <button type="button" class="btn btn-secondary" id="lang-en" onclick="setLanguage('en')" aria-pressed="false">en</button>
        <span>/</span>
        <button type="button" class="btn btn-secondary" id="lang-ru" onclick="setLanguage('ru')" aria-pressed="true">ru</button>
    </div>
</header>

<!-- ПАНЕЛЬ: ИСТОЧНИК И ЦЕЛЬ ПРОВЕРКИ -->
<div class="card">
    <div class="flex-row" style="row-gap: 12px; align-items: flex-end;">
        <!-- Источник и кнопка скачать -->
        <div>
            <label data-i18n="Источник:">Источник:</label><br>
            <div style="display: inline-flex; gap: 8px;">
                <select id="fetch-source">
                    <option value="stormsia">Stormsia (Pre-verified)</option>
                    <option value="geonode">Geonode API</option>
                    <option value="dinoz0rg" selected>Dinoz0rg (Checked)</option>
                    <option value="proxifly">Proxifly</option>
                    <option value="thespeedx">TheSpeedX</option>
                </select>
                <button id="btn-fetch" class="btn btn-primary" onclick="fetchProxies()" data-i18n="Скачать прокси">
                    Скачать прокси
                </button>
            </div>
        </div>

        <!-- Жадный блок выбора и ввода целевого URL во всю ширину -->
        <div class="target-block">
            <div class="target-preset-wrap">
                <label data-i18n="Цель проверки:">Цель проверки:</label><br>
                <select id="target-preset" onchange="onPresetChange()" style="width: 100%;">
                    <option value="http://cp.cloudflare.com/generate_204" selected data-i18n="Cloudflare HTTP (204) — реком.">Cloudflare HTTP (204) — реком.</option>
                    <option value="https://www.google.com/generate_204">Google HTTPS (204)</option>
                    <option value="http://httpbin.org/ip">Httpbin HTTP (IP)</option>
                    <option value="https://cloudflare.com/cdn-cgi/trace">Cloudflare HTTPS (Trace)</option>
                    <option value="https://api.telegram.org">Telegram API (HTTPS)</option>
                    <option value="custom" data-i18n="Свой URL...">Свой URL...</option>
                </select>
            </div>
            <div class="target-url-wrap">
                <label>URL:</label><br>
                <input id="target-url-input" type="text" value="http://cp.cloudflare.com/generate_204" oninput="checkHttpsWarning()" style="width: 100%;">
            </div>
        </div>
    </div>

    <!-- Предупреждение о выборе HTTPS для проверки HTTP-прокси -->
    <div id="https-warning" class="warn-tip" data-i18n-html="https-warning">
        <b>Внимание:</b> Выбрана цель с HTTPS, при этом проверяются HTTP-прокси. Большинство публичных HTTP-прокси не поддерживают SSL-туннелирование (CONNECT порт 443) и будут отсеяны. Для чистого тестирования HTTP-прокси рекомендуем цель на чистом HTTP (например, <b>Cloudflare HTTP 204</b>).
    </div>
</div>

<!-- ЕДИНЫЙ ЛИПКИЙ БЛОК УПРАВЛЕНИЯ И СТАТИСТИКИ -->
<div class="sticky-controls">
    <!-- Кнопки управления -->
    <div class="flex-row">
        <button id="btn-start" class="btn btn-green" onclick="startCheck()" data-i18n="Начать проверку">
            Начать проверку
        </button>
        <button id="btn-resume" class="btn btn-primary" onclick="resumeCheck()" disabled data-i18n="Продолжить">
            Продолжить
        </button>
        <button id="btn-stop" class="btn btn-tomato" onclick="stopCheck()" disabled data-i18n="Стоп">
            Стоп
        </button>

        <button class="btn btn-warning" onclick="removeBadProxies()" id="btn-clean-bad" title="Удалить только проверенные нерабочие прокси" data-i18n-title="Удалить только проверенные нерабочие прокси" data-i18n="Удалить мертвые">
            Удалить мертвые
        </button>
        <button class="btn btn-warning" onclick="removeDangerousProxies()" id="btn-clean-danger" title="Удалить прокси с подменой TLS и прокси, которые не удалось проверить" data-i18n-title="Удалить прокси с подменой TLS и прокси, которые не удалось проверить" data-i18n="Удалить опасные">
            Удалить опасные
        </button>

        <button class="btn btn-secondary" onclick="copyWorkingToClipboard()" data-i18n="Скопировать в буфер">
            Скопировать в буфер
        </button>
        <button class="btn btn-secondary" onclick="openPasteModal()" data-i18n="Вставить из буфера">
            Вставить из буфера
        </button>
        <button class="btn btn-tomato" onclick="confirmClearAllProxies()" data-i18n="Очистить список">
            Очистить список
        </button>
    </div>

    <!-- Статистика с плавной анимацией цифр -->
    <div class="stats">
        <div class="stat-box">
            <div class="val gray" id="s-total" data-val="0">0</div>
            <div class="lbl" data-i18n="В очереди">В очереди</div>
        </div>
        <div class="stat-box">
            <div class="val yellow" id="s-checked" data-val="0">0</div>
            <div class="lbl" data-i18n="Проверено">Проверено</div>
        </div>
        <div class="stat-box">
            <div class="val green" id="s-ok" data-val="0">0</div>
            <div class="lbl" data-i18n="Рабочих">Рабочих</div>
        </div>
        <div class="stat-box">
            <div class="val red" id="s-fail" data-val="0">0</div>
            <div class="lbl" data-i18n="Не работает">Не работает</div>
        </div>
        <div class="stat-box legend" id="anon-legend" onclick="showAnonLegend()"></div>
    </div>

    <div class="progress-wrap">
        <div class="progress-bar" id="progress"></div>
    </div>

    <!-- Статус и фильтры (Протоколы слева от стран) -->
    <div class="flex-row" style="justify-content: space-between;">
        <div id="status" data-i18n="Готов к запуску">Готов к запуску</div>
        <div class="flex-row" style="gap: 16px;">
            <!-- Фильтр по протоколам (СЛЕВА) -->
            <div class="flex-row" style="gap: 10px;">
                <label style="color: #8b949e;" data-i18n="Протоколы:">Протоколы:</label>
                <label class="custom-checkbox">
                    <input type="checkbox" id="proto-http" onchange="onProtocolFilterChange()" checked> HTTP
                </label>
                <label class="custom-checkbox">
                    <input type="checkbox" id="proto-https" onchange="onProtocolFilterChange()" checked> HTTPS
                </label>
                <label class="custom-checkbox">
                    <input type="checkbox" id="proto-socks4" onchange="onProtocolFilterChange()" checked> SOCKS4
                </label>
                <label class="custom-checkbox">
                    <input type="checkbox" id="proto-socks5" onchange="onProtocolFilterChange()" checked> SOCKS5
                </label>
            </div>

            <!-- Фильтр по стране (СПРАВА) -->
            <div class="flex-row" style="gap: 6px;">
                <label style="color: #8b949e;" data-i18n="Фильтр по стране:">Фильтр по стране:</label>
                <select id="filter-country" onchange="applyFilters()">
                    <option value="ALL" data-i18n="Все страны">Все страны</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- ТАБЛИЦА РЕЗУЛЬТАТОВ (IP 330px, Задержка 100px, Страна 150px, Скорость 120px, Статус жадный справа) -->
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th class="sortable" style="width: 330px; max-width: 330px;" onclick="toggleSortIp()" title="Кликните для сортировки по IP" data-i18n-title="Кликните для сортировки по IP">
                    <span data-i18n="Прокси (IP)">Прокси (IP)</span> <span id="sort-ip-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-left: 2px;"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg></span>
                </th>
                <th class="sortable" style="width: 110px;" onclick="toggleSortTime()" title="Кликните для сортировки по задержке" data-i18n-title="Кликните для сортировки по задержке">
                    <span data-i18n="Задержка">Задержка</span> <span id="sort-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-left: 2px;"><path d="m18 15-6-6-6 6"/></svg></span>
                </th>
                <th class="sortable" style="width: 150px;" onclick="toggleSortCountry()" title="Кликните для сортировки по стране" data-i18n-title="Кликните для сортировки по стране">
                    <span data-i18n="Страна">Страна</span> <span id="sort-country-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-left: 2px;"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg></span>
                </th>
                <th class="sortable" style="width: 120px;" onclick="toggleSortSpeed()" title="Кликните для сортировки по скорости" data-i18n-title="Кликните для сортировки по скорости">
                    <span data-i18n="Скорость">Скорость</span> <span id="sort-speed-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-left: 2px;"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg></span>
                </th>
                <th class="sortable" style="width: 130px; text-align: center;" onclick="toggleSortAnon()" title="Кликните для сортировки по анонимности" data-i18n-title="Кликните для сортировки по анонимности">
                    <span data-i18n="Анонимный">Анонимный</span> <span id="sort-anon-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-left: 2px;"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg></span>
                </th>
                <th class="sortable status-col" onclick="toggleSortStatus()" title="Кликните для сортировки по статусу" data-i18n-title="Кликните для сортировки по статусу">
                    <span data-i18n="Статус">Статус</span> <span id="sort-status-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-left: 2px;"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg></span>
                </th>
            </tr>
        </thead>
        <tbody id="tbody">
        </tbody>
    </table>
</div>

<p style="margin-top:15px; color:#484f58; font-size:12px;" id="footer"></p>

<!-- МОДАЛЬНОЕ ОКНО ДЛЯ ВСТАВКИ ПРОКСИ ИЗ БУФЕРА -->
<div id="paste-modal" class="modal-overlay">
    <div class="modal-box">
        <h3 data-i18n="Вставить список прокси">Вставить список прокси</h3>
        <p data-i18n-html="paste-help" style="color: #8b949e; font-size: 12px; margin-bottom: 10px;">
            Вставьте прокси по одному на строку (например: <code>socks5://1.2.3.4:1080</code> или <code>1.2.3.4:8080</code>).
        </p>
        <textarea id="paste-textarea" placeholder="socks5://1.2.3.4:1080&#10;http://5.6.7.8:8080&#10;..."></textarea>
        <div class="flex-row" style="justify-content: flex-end; gap: 8px;">
            <button class="btn btn-secondary" onclick="closePasteModal()" data-i18n="Отмена">Отмена</button>
            <button class="btn btn-primary" onclick="submitPasteProxies(true)" data-i18n="Добавить к текущим">Добавить к текущим</button>
            <button class="btn btn-green" onclick="submitPasteProxies(false)" data-i18n="Заменить весь список">Заменить весь список</button>
        </div>
    </div>
</div>

<!-- ЕДИНОЕ СТИЛЬНОЕ МОДАЛЬНОЕ ОКНО ДИАЛОГОВ (ВМЕСТО ALERT / CONFIRM) -->
<div id="dialog-modal" class="modal-overlay">
    <div class="modal-box" style="max-width: 440px;">
        <h3 id="dialog-title" data-i18n="Сообщение">Сообщение</h3>
        <div id="dialog-message" class="modal-msg"></div>
        <div class="flex-row" style="justify-content: flex-end; gap: 8px;" id="dialog-buttons"></div>
    </div>
</div>

<script>
// Перевод интерфейса без дополнительных сетевых запросов.
const LANG_KEY = 'proxy-checker.lang';
let lang = 'ru';
try { if (localStorage.getItem(LANG_KEY) === 'en') lang = 'en'; } catch (e) {}
const EN = {
    "Список не сохранён": "List not saved",
    "Браузер не дал сохранить список ({0}). Таблица работает, но после перезагрузки страницы список и результаты будут прежними.": "The browser could not save the list ({0}). The table still works, but reloading will restore the previous list and results.",
    "Элитный, рекомендуемый": "Elite, recommended",
    "TLS-сертификат подлинный, заголовки не выдают прокси, ip-api не знает этот IP как прокси или хостинг.": "The TLS certificate is authentic, headers do not reveal a proxy, and ip-api does not flag this IP as a proxy or hosting address.",
    "Известно, что IP хостера": "Known hosting IP",
    "ip-api знает этот IP как адрес хостинга (дата-центра): сайты могут догадаться, что это прокси.": "ip-api identifies this IP as a hosting (data center) address: websites may suspect a proxy.",
    "IP в базе proxy/VPN": "IP in proxy/VPN database",
    "Сайт узнает, что вы через прокси: прокси сам сообщает об этом заголовками (Via, X-Forwarded-For без вашего IP и т. п.) или ip-api знает этот IP как прокси/VPN. Ваш IP при этом не виден.": "Websites can detect the proxy: it reveals itself through headers (Via, X-Forwarded-For without your IP, etc.) or ip-api flags this IP as a proxy/VPN. Your IP is not exposed.",
    "Не удалось проверить": "Could not verify",
    "Часть проверок не дала ответа даже при повторе: безопасность не подтверждена. Удаляется кнопкой «Удалить опасные».": "Some checks failed to respond even after retrying: safety is not confirmed. Removed by ‘Remove unsafe’.",
    "Раскрывает IP сервера": "Exposes server IP",
    "Прокси передаёт сайту IP того, кто к нему подключился (при проверке — IP сервера): сайт увидит ваш настоящий IP. Трафик при этом не перехватывается.": "The proxy sends the connecting client's IP to websites (the server IP during this test): a website will see your real IP. This does not mean traffic is intercepted.",
    "Возможен перехват трафика": "Possible traffic interception",
    "Прокси подменяет TLS-сертификат: может читать и менять HTTPS-трафик, включая пароли. Удаляется кнопкой «Удалить опасные».": "The proxy replaces the TLS certificate and may read or modify HTTPS traffic, including passwords. Removed by ‘Remove unsafe’.",
    "сертификат подлинный": "authentic certificate",
    "сертификат подменён — перехват HTTPS": "replaced certificate — HTTPS interception",
    "не проверено: проверочный HTTPS-запрос через прокси не прошёл": "not verified: the HTTPS test request through the proxy failed",
    "не проверено: на сервере нет корневых сертификатов": "not verified: the server has no root certificates",
    "чистые": "clean",
    "не меняются (SOCKS)": "unchanged (SOCKS)",
    "выдают прокси": "reveal a proxy",
    "раскрывают IP": "expose IP",
    "не проверены: судья через прокси не открылся": "not verified: the header judge could not be reached through the proxy",
    "не проверены: судья недоступен с сервера": "not verified: the header judge is unavailable from the server",
    "ждём ответа": "waiting for response",
    "не ответил": "no response",
    "не числится в базах прокси и хостинга": "not listed as a proxy or hosting IP",
    "IP в базе прокси/VPN": "IP in proxy/VPN database",
    "IP хостинга (дата-центра)": "Hosting IP (data center)",
    "{0}\n\nTLS: {1}\nЗаголовки: {2}\nip-api: {3}": "{0}\n\nTLS: {1}\nHeaders: {2}\nip-api: {3}",
    "Статусы анонимности": "Anonymity statuses",
    " Мбит/с": " Mbps",
    " Кбит/с": " Kbps",
    "Уведомление": "Notification",
    "ОК": "OK",
    "Отмена": "Cancel",
    "Проверка активна": "Check in progress",
    "Сначала остановите проверку кнопкой «Стоп»!": "Stop the check using ‘Stop’ first!",
    "Загрузка прокси": "Download proxies",
    "В текущем списке уже есть {0} прокси. Добавить новые к текущим или полностью заменить список?": "The current list already contains {0} proxies. Append new proxies or replace the entire list?",
    "Добавить к текущим": "Append to current list",
    "Заменить весь список": "Replace entire list",
    "<span class=\"spinner\"></span>Скачивание...": "<span class=\"spinner\"></span>Downloading...",
    "Ошибка загрузки": "Download error",
    "Неизвестная ошибка": "Unknown error",
    "Ошибка сети": "Network error",
    "Скачать прокси": "Download proxies",
    "<span class=\"badge badge-wait\">Ожидание</span>": "<span class=\"badge badge-wait\">Waiting</span>",
    "<span class=\"badge badge-fail\">МЕРТВЫЙ</span>": "<span class=\"badge badge-fail\">DEAD</span>",
    " мс": " ms",
    "Список пуст": "Empty list",
    "Список прокси пуст! Сначала загрузите или вставьте прокси.": "The proxy list is empty! Download or paste proxies first.",
    "Проверка завершена": "Check complete",
    "Все прокси в списке уже проверены!": "All proxies in the list have already been checked!",
    "Проверка остановлена": "Check stopped",
    "{0} {1}с": "{0} {1}s",
    "Ошибка ответа сервера": "Server response error",
    "Завершено: ": "Completed: ",
    " &nbsp;·&nbsp; <span style=\"color: #58a6ff;\"><span class=\"spinner\"></span>Определение страны...{0}</span>": " &nbsp;·&nbsp; <span style=\"color: #58a6ff;\"><span class=\"spinner\"></span>Looking up countries...{0}</span>",
    "<span class=\"spinner\"></span>Проверка... Готово: <b>{0}</b> из <b>{1}</b>{2}": "<span class=\"spinner\"></span>Checking... Done: <b>{0}</b> of <b>{1}</b>{2}",
    "Проверка остановлена (проверено {0} из {1}){2}": "Check stopped (checked {0} of {1}){2}",
    "Проверка завершена. Найдено рабочих: <b>{0}</b> из <b>{1}</b>{2}": "Check complete. Working proxies: <b>{0}</b> of <b>{1}</b>{2}",
    "Готов к запуску (в очереди: {0})": "Ready (queued: {0})",
    "<option value=\"ALL\">Все страны</option>": "<option value=\"ALL\">All countries</option>",
    "В списке нет мертвых прокси.": "There are no dead proxies in the list.",
    "Удалено мертвых: <b>{0}</b>. Осталось: <b>{1}</b>": "Dead proxies removed: <b>{0}</b>. Remaining: <b>{1}</b>",
    "Подождите": "Please wait",
    "ip-api ещё проверяет рабочие прокси. Пока он не ответил, их статус «Не удалось проверить» временный.": "ip-api is still checking working proxies. Until it responds, their ‘Could not verify’ status is temporary.",
    "Удаление опасных": "Remove unsafe proxies",
    "Среди рабочих прокси нет опасных: с подменой TLS или не прошедших проверку.": "No working proxies are unsafe: none replace TLS certificates or have unverified checks.",
    "Удалить опасные прокси?": "Remove unsafe proxies?",
    "Будет удалено {0} прокси: подменяют TLS-сертификат или их не удалось проверить.\nОстанется {1}.": "{0} proxies will be removed: they replace TLS certificates or could not be verified.\nRemaining: {1}.",
    "Удалить опасные": "Remove unsafe",
    "Удалено <b>{0}</b> опасных прокси. Осталось: <b>{1}</b>": "Removed <b>{0}</b> unsafe proxies. Remaining: <b>{1}</b>",
    "Буфер обмена": "Clipboard",
    "В таблице пока нет проверенных рабочих прокси для копирования!": "There are no verified working proxies to copy yet!",
    "Скопировано": "Copied",
    "Скопировано {0} уникальных рабочих прокси в буфер обмена!": "Copied {0} unique working proxies to the clipboard!",
    "Ошибка буфера": "Clipboard error",
    "Скопируйте рабочие прокси вручную:\n": "Copy the working proxies manually:\n",
    "Очистить список?": "Clear the list?",
    "Вы уверены, что хотите полностью очистить список прокси (в таблице и сохранённый в браузере)?": "Are you sure you want to clear the entire proxy list (both the table and the list saved in the browser)?",
    "Очистить": "Clear",
    "Пустой ввод": "Empty input",
    "Введите или вставьте хотя бы одну строку прокси!": "Enter or paste at least one proxy address!",
    "Нет корректных адресов": "No valid addresses",
    "Источник:": "Source:",
    "Цель проверки:": "Test target:",
    "Cloudflare HTTP (204) — реком.": "Cloudflare HTTP (204) — recommended",
    "Свой URL...": "Custom URL...",
    "Начать проверку": "Start check",
    "Продолжить": "Resume",
    "Стоп": "Stop",
    "Удалить мертвые": "Remove dead",
    "Скопировать в буфер": "Copy to clipboard",
    "Вставить из буфера": "Paste from clipboard",
    "Очистить список": "Clear list",
    "В очереди": "Queued",
    "Проверено": "Checked",
    "Рабочих": "Working",
    "Не работает": "Dead",
    "Готов к запуску": "Ready",
    "Протоколы:": "Protocols:",
    "Фильтр по стране:": "Country filter:",
    "Все страны": "All countries",
    "Прокси (IP)": "Proxy (IP)",
    "Задержка": "Latency",
    "Страна": "Country",
    "Скорость": "Speed",
    "Анонимный": "Anonymous",
    "Статус": "Status",
    "Вставить список прокси": "Paste proxy list",
    "Сообщение": "Message",
    "Удалить только проверенные нерабочие прокси": "Remove only checked dead proxies",
    "Удалить прокси с подменой TLS и прокси, которые не удалось проверить": "Remove proxies that replace TLS certificates or could not be verified",
    "Кликните для сортировки по IP": "Click to sort by IP",
    "Кликните для сортировки по задержке": "Click to sort by latency",
    "Кликните для сортировки по стране": "Click to sort by country",
    "Кликните для сортировки по скорости": "Click to sort by speed",
    "Кликните для сортировки по анонимности": "Click to sort by anonymity",
    "Кликните для сортировки по статусу": "Click to sort by status",
    "https-warning": "<b>Warning:</b> The target uses HTTPS while HTTP proxies are enabled. Most public HTTP proxies do not support SSL tunneling (CONNECT on port 443) and will fail. For HTTP proxy testing, use a plain HTTP target (for example, <b>Cloudflare HTTP 204</b>).",
    "paste-help": "Paste one proxy per line (for example: <code>socks5://1.2.3.4:1080</code> or <code>1.2.3.4:8080</code>)."
};
const RICH_RU = {"https-warning": "<b>Внимание:</b> Выбрана цель с HTTPS, при этом проверяются HTTP-прокси. Большинство публичных HTTP-прокси не поддерживают SSL-туннелирование (CONNECT порт 443) и будут отсеяны. Для чистого тестирования HTTP-прокси рекомендуем цель на чистом HTTP (например, <b>Cloudflare HTTP 204</b>).", "paste-help": "Вставьте прокси по одному на строку (например: <code>socks5://1.2.3.4:1080</code> или <code>1.2.3.4:8080</code>)."};
function t(text, ...args) {
    const translated = lang === 'en' ? (EN[text] ?? text) : (RICH_RU[text] ?? text);
    return translated.replace(/\{(\d+)\}/g, (match, i) => i < args.length ? String(args[i]) : match);
}
function serverError(text) {
    if (lang !== 'en' || !text) return text;
    if (text.startsWith('Неизвестный источник: ')) return 'Unknown source: ' + text.slice('Неизвестный источник: '.length);
    const source = text.match(/^Не удалось получить прокси из источника (.+) \(источник недоступен или пуст\)$/);
    if (source) return 'Could not retrieve proxies from ' + source[1] + ' (source unavailable or empty)';
    if (text.startsWith('Не найдено строк вида IP:ПОРТ')) return 'No valid IP:PORT or PROTOCOL://IP:PORT addresses found (public IPv4, without username or password)';
    if (text === 'Список прокси пуст') return 'The proxy list is empty';
    return t(text);
}
let completedAt = null;
let regionNames = null;
function countryName(r) {
    const code = r.country_code || '';
    const shortRu = { US: 'США', ZA: 'ЮАР', AE: 'ОАЭ' };
    if (lang === 'ru' && shortRu[code]) return shortRu[code];
    if (/^[A-Z]{2}$/.test(code) && regionNames) {
        try { return regionNames.of(code) || r.country || ''; } catch (e) {}
    }
    return r.country || '';
}
function applyLanguage() {
    document.documentElement.lang = lang;
    try { regionNames = new Intl.DisplayNames([lang], { type: 'region' }); } catch (e) { regionNames = null; }
    document.querySelectorAll('[data-i18n]').forEach(el => { el.textContent = t(el.dataset.i18n); });
    document.querySelectorAll('[data-i18n-title]').forEach(el => { el.title = t(el.dataset.i18nTitle); });
    document.querySelectorAll('[data-i18n-html]').forEach(el => { el.innerHTML = t(el.dataset.i18nHtml); });
    for (const code of ['en', 'ru']) document.getElementById('lang-' + code).setAttribute('aria-pressed', String(lang === code));
}
function setLanguage(value) {
    if (!['en', 'ru'].includes(value) || value === lang) return;
    lang = value;
    try { localStorage.setItem(LANG_KEY, lang); } catch (e) {}
    applyLanguage();
    renderAnonLegend();
    // Не пересоздаём таблицу: текущая проверка и отсчёт продолжаются.
    results.forEach((r, i) => {
        const tr = document.getElementById('r' + i);
        if (!tr) return;
        if (r && r.ok) {
            tr.cells[1].textContent = r.time + t(' мс');
            tr.cells[2].innerHTML = countryBadge(countryName(r));
            tr.cells[3].textContent = formatSpeed(r.speed_bits);
        } else if (r || !isRunning) {
            tr.cells[5].innerHTML = r ? t('<span class="badge badge-fail">МЕРТВЫЙ</span>') : t('<span class="badge badge-wait">Ожидание</span>');
        }
    });
    updateCountryFilterOptions();
    applyFilters();
    applyCurrentSort();
    updateStatusText();
    renderFooter();
}
function renderFooter() {
    document.getElementById('footer').textContent = completedAt ? t('Завершено: ') + completedAt.toLocaleString(lang) : '';
}

// Список и результаты проверки хранятся в localStorage браузера (строка на прокси); сервер ничего не хранит
const STORAGE_KEY = 'proxy-checker.list';
const PROXY_LIMIT = 100000; // жёсткий лимит списка: лишнее отбрасывается без предупреждения

// Строка хранилища: «адрес» — не проверен, «адрес\t0» — не работает,
// «адрес\t1\tвремя\tбит/с\ttls\thdr\thdr_names\tкод страны\tстрана\tgeo» — рабочий.
// geo — ответ ip-api: '' ждём, '?' не ответил, '-' чисто, 'p' прокси/VPN, 'h' хостинг, 'ph' оба
function serializeEntry(p, r) {
    if (!r) return p;
    if (!r.ok) return p + '\t0';
    return [p, 1, r.time, r.speed_bits, r.tls, r.hdr, r.hdr_names, r.country_code, r.country, r.geo]
        .map(v => v ?? '').join('\t');
}

function parseEntry(line, index) {
    const f = line.split('\t');
    const proxy = f[0];
    if (f.length < 2) return [proxy, null];
    const r = { index, proxy, ok: false, proto: '—', time: 0, speed_bits: 0, tls: '', hdr: '', hdr_names: '', finished: true };
    if (f[1] === '1') {
        Object.assign(r, {
            ok: true, proto: proxy.includes('://') ? proxy.split('://')[0].toUpperCase() : '',
            time: parseInt(f[2]) || 0, speed_bits: parseInt(f[3]) || 0, tls: f[4] || '', hdr: f[5] || '',
            hdr_names: f[6] || '', country_code: f[7] || '', country: f[8] || '', geo: f[9] || '',
        });
    }
    return [proxy, r];
}

function loadState() {
    let lines = [];
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        lines = saved ? saved.split('\n').filter(Boolean) : [];
    } catch (e) {}
    const parsed = lines.map(parseEntry);
    return [parsed.map(x => x[0]), parsed.map(x => x[1])];
}

// Сохранение после каждой пачки: F5 не сбрасывает проверку. Ошибку (например, QuotaExceededError) показываем один раз
let storageWarned = false;
function saveState() {
    try {
        localStorage.setItem(STORAGE_KEY, proxies.map((p, i) => serializeEntry(p, results[i])).join('\n'));
    } catch (e) {
        if (storageWarned) return;
        storageWarned = true;
        showModalDialog({ title: t("Список не сохранён"), message: t("Браузер не дал сохранить список ({0}). Таблица работает, но после перезагрузки страницы список и результаты будут прежними.", e.name) });
    }
}

let [proxies, results] = loadState();
let isRunning = false;
let shouldStop = false;
let currentAbortController = null;
let cntOk = 0, cntFail = 0, cntDone = 0;

// Сортировка по умолчанию: Задержка + IP (быстрые сверху, при равном времени/непроверенных — по IP)
let currentSort = 'time-asc';
let sortStatusAsc = true;
let sortSpeedDesc = true;
let sortCountryAsc = true;

const ICON_SORT_DEFAULT = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-left: 2px;"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>`;
const ICON_SORT_UP = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-left: 2px;"><path d="m18 15-6-6-6 6"/></svg>`;
const ICON_SORT_DOWN = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-left: 2px;"><path d="m6 9 6 6 6-6"/></svg>`;

// Фоновая асинхронная очередь GeoIP
let geoipQueue = [];
let isGeoipRunning = false;
const geoipCache = new Map();

function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// Статусы анонимности: код = ранг сортировки (больше — лучше); -1 — без значка (не работает или не проверен).
// [название, пояснение]
const ANON_STATUS = {
    5: ['Элитный, рекомендуемый', 'TLS-сертификат подлинный, заголовки не выдают прокси, ip-api не знает этот IP как прокси или хостинг.'],
    4: ['Известно, что IP хостера', 'ip-api знает этот IP как адрес хостинга (дата-центра): сайты могут догадаться, что это прокси.'],
    3: ['IP в базе proxy/VPN', 'Сайт узнает, что вы через прокси: прокси сам сообщает об этом заголовками (Via, X-Forwarded-For без вашего IP и т. п.) или ip-api знает этот IP как прокси/VPN. Ваш IP при этом не виден.'],
    2: ['Не удалось проверить', 'Часть проверок не дала ответа даже при повторе: безопасность не подтверждена. Удаляется кнопкой «Удалить опасные».'],
    1: ['Раскрывает IP сервера', 'Прокси передаёт сайту IP того, кто к нему подключился (при проверке — IP сервера): сайт увидит ваш настоящий IP. Трафик при этом не перехватывается.'],
    0: ['Возможен перехват трафика', 'Прокси подменяет TLS-сертификат: может читать и менять HTTPS-трафик, включая пароли. Удаляется кнопкой «Удалить опасные».'],
};
// Порядок в легенде: сначала проверенные, «Не удалось проверить» — последним
const LEGEND_ORDER = [5, 4, 3, 1, 0, 2];
// Значки внутри круга (viewBox 24×24); у 4 и 3 — только цвет
const DOT_GLYPHS = {
    5: '<path d="M7.5 12.5l3 3 6-6.5"/>',
    2: '<path d="M9.6 9.6a2.5 2.5 0 1 1 3.4 2.3c-.6.3-1 .8-1 1.5v.4M12 16.9v.01"/>',
    1: '<path d="M12 7.3v5.6M12 16.7v.01"/>',
    0: '<path d="M9 9l6 6M15 9l-6 6"/>',
};
const TLS_TEXT = {
    ok: 'сертификат подлинный',
    mitm: 'сертификат подменён — перехват HTTPS',
    na: 'не проверено: проверочный HTTPS-запрос через прокси не прошёл',
    noca: 'не проверено: на сервере нет корневых сертификатов',
};
const HDR_TEXT = {
    clean: 'чистые',
    socks: 'не меняются (SOCKS)',
    proxy: 'выдают прокси',
    ip: 'раскрывают IP',
    na: 'не проверены: судья через прокси не открылся',
    nojudge: 'не проверены: судья недоступен с сервера',
};

function anonLevel(d) {
    if (!d || !d.ok) return -1;
    const geo = d.geo || '';
    if (d.tls === 'mitm') return 0;
    if (d.hdr === 'ip') return 1;
    if (d.hdr === 'proxy' || geo.includes('p')) return 3;
    if (geo.includes('h')) return 4;
    if (d.tls !== 'ok' || d.hdr === 'na' || d.hdr === 'nojudge' || geo === '' || geo === '?') return 2;
    return 5;
}

function dotHtml(level) {
    return `<svg class="dot dot-${level}" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10.5"/>${DOT_GLYPHS[level] || ''}</svg>`;
}

// «Удалить опасные»: перехват TLS и непроверенные — неизвестный результат не значит безопасный.
// «Раскрывает IP» трафик не перехватывает — остаётся
function isDangerous(d) {
    const level = anonLevel(d);
    return level === 0 || level === 2;
}

// Ответ без проверки TLS или заголовков (сбой одного запроса) — перепроверяем один раз в следующей пачке
function needsRecheck(d) {
    return !!(d && d.ok && (d.tls === 'na' || d.hdr === 'na'));
}

// Без всплывающей подсказки: подробности — по клику на круг (делегирование на tbody)
function anonCellHtml(d) {
    const level = anonLevel(d);
    return level < 0 ? '' : dotHtml(level);
}

function geoText(geo) {
    if (geo === '') return t("ждём ответа");
    if (geo === '?') return t("не ответил");
    if (geo === '-') return t("не числится в базах прокси и хостинга");
    return [geo.includes('p') && t("IP в базе прокси/VPN"), geo.includes('h') && t("IP хостинга (дата-центра)")].filter(Boolean).join(', ');
}

function showAnonDetails(d) {
    const level = anonLevel(d);
    if (level < 0) return;
    const hdr = t(HDR_TEXT[d.hdr] || '—') + (d.hdr_names ? ': ' + d.hdr_names : '');
    showModalDialog({
        title: t(ANON_STATUS[level][0]),
        message: t("{0}\n\nTLS: {1}\nЗаголовки: {2}\nip-api: {3}", t(ANON_STATUS[level][1]), t(TLS_TEXT[d.tls] || '—'), hdr, geoText(d.geo || '')),
    });
}

function renderAnonLegend() {
    document.getElementById('anon-legend').innerHTML = LEGEND_ORDER.map(level =>
        `<span class="legend-item" title="${esc(t(ANON_STATUS[level][0]))}">${dotHtml(level)}<span class="legend-text">${esc(t(ANON_STATUS[level][0]))}</span></span>`
    ).join('');
}

function showAnonLegend() {
    showModalDialog({
        title: t("Статусы анонимности"),
        html: LEGEND_ORDER.map(level =>
            `<div class="legend-row">${dotHtml(level)}<div><b>${esc(t(ANON_STATUS[level][0]))}</b><br>${esc(t(ANON_STATUS[level][1]))}</div></div>`
        ).join(''),
    });
}

function formatSpeed(bits) {
    if (bits >= 1e6) return (bits / 1e6).toFixed(1) + t(" Мбит/с");
    return Math.round(bits / 1e3) + t(" Кбит/с");
}

// Плавная анимация изменения цифр за 1.5 секунды
function animateValue(elemId, endVal, duration = 1500) {
    const obj = document.getElementById(elemId);
    if (!obj) return;
    const startVal = parseInt(obj.getAttribute('data-val')) || 0;
    if (startVal === endVal) {
        obj.textContent = endVal;
        return;
    }

    obj.setAttribute('data-val', endVal);
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(startVal + (endVal - startVal) * ease);
        obj.textContent = current;
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            obj.textContent = endVal;
        }
    };
    window.requestAnimationFrame(step);
}

// Замена alert и confirm на стильное модальное окно
function showModalDialog({ title = t("Уведомление"), message = '', html = '', isConfirm = false, confirmText = t("ОК"), cancelText = t("Отмена"), onConfirm = null, buttons = null }) {
    const modal = document.getElementById('dialog-modal');
    document.getElementById('dialog-title').textContent = title;
    const msgElem = document.getElementById('dialog-message');
    if (html) msgElem.innerHTML = html; else msgElem.textContent = message;

    const btnContainer = document.getElementById('dialog-buttons');
    btnContainer.innerHTML = '';

    if (Array.isArray(buttons) && buttons.length > 0) {
        buttons.forEach(b => {
            const btn = document.createElement('button');
            btn.className = b.className || 'btn btn-secondary';
            btn.textContent = b.text || t("ОК");
            btn.onclick = () => {
                modal.style.display = 'none';
                if (typeof b.onClick === 'function') b.onClick();
            };
            btnContainer.appendChild(btn);
        });
    } else if (isConfirm) {
        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn btn-secondary';
        cancelBtn.textContent = cancelText;
        cancelBtn.onclick = () => { modal.style.display = 'none'; };
        btnContainer.appendChild(cancelBtn);

        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'btn btn-tomato';
        confirmBtn.textContent = confirmText;
        confirmBtn.onclick = () => {
            modal.style.display = 'none';
            if (typeof onConfirm === 'function') onConfirm();
        };
        btnContainer.appendChild(confirmBtn);
    } else {
        const okBtn = document.createElement('button');
        okBtn.className = 'btn btn-primary';
        okBtn.textContent = confirmText;
        okBtn.onclick = () => { modal.style.display = 'none'; };
        btnContainer.appendChild(okBtn);
    }

    modal.style.display = 'flex';
}

function onPresetChange() {
    const sel = document.getElementById('target-preset');
    const input = document.getElementById('target-url-input');
    if (sel.value !== 'custom') {
        input.value = sel.value;
    } else {
        input.focus();
    }
    checkHttpsWarning();
}

function checkHttpsWarning() {
    const url = document.getElementById('target-url-input').value.trim().toLowerCase();
    const isHttpsTarget = url.startsWith('https://');
    const hasHttpProto = document.getElementById('proto-http').checked;
    const warnBox = document.getElementById('https-warning');

    if (isHttpsTarget && hasHttpProto) {
        warnBox.style.display = 'block';
    } else {
        warnBox.style.display = 'none';
    }
}

function onProtocolFilterChange() {
    checkHttpsWarning();
    applyFilters();
}

function updateControlButtons(state) {
    const btnStart  = document.getElementById('btn-start');
    const btnResume = document.getElementById('btn-resume');
    const btnStop   = document.getElementById('btn-stop');
    const btnFetch  = document.getElementById('btn-fetch');

    btnStart.classList.remove('btn-active-flow');
    btnResume.classList.remove('btn-active-flow');
    btnStop.classList.remove('btn-active-flow');

    if (state === 'running') {
        btnStart.disabled  = true;
        btnResume.disabled = true;
        btnStop.disabled   = false;
        btnStop.classList.add('btn-active-flow');
        if (btnFetch) btnFetch.disabled = true;
    } else if (state === 'paused') {
        btnStart.disabled  = false;
        btnResume.disabled = false;
        btnResume.classList.add('btn-active-flow');
        btnStop.disabled   = true;
        if (btnFetch) btnFetch.disabled = false;
    } else if (state === 'finished') {
        btnStart.disabled  = false;
        btnResume.disabled = true;
        btnStop.disabled   = true;
        if (btnFetch) btnFetch.disabled = false;
    } else {
        btnStart.disabled  = false;
        btnResume.disabled = true;
        btnStop.disabled   = true;
        if (btnFetch) btnFetch.disabled = false;
    }
}

function fetchProxies() {
    if (isRunning) {
        showModalDialog({ title: t("Проверка активна"), message: t("Сначала остановите проверку кнопкой «Стоп»!") });
        return;
    }

    if (proxies.length > 0) {
        showModalDialog({
            title: t("Загрузка прокси"),
            message: t("В текущем списке уже есть {0} прокси. Добавить новые к текущим или полностью заменить список?", proxies.length),
            buttons: [
                {
                    text: t("Отмена"),
                    className: 'btn btn-secondary',
                    onClick: () => {}
                },
                {
                    text: t("Добавить к текущим"),
                    className: 'btn btn-primary',
                    onClick: () => executeFetch(true)
                },
                {
                    text: t("Заменить весь список"),
                    className: 'btn btn-green',
                    onClick: () => executeFetch(false)
                }
            ]
        });
    } else {
        executeFetch(false);
    }
}

async function executeFetch(isAppend) {
    const btn = document.getElementById('btn-fetch');
    const source = document.getElementById('fetch-source').value;

    btn.disabled = true;
    btn.innerHTML = t("<span class=\"spinner\"></span>Скачивание...");

    try {
        const res = await fetch(`proxy-fetch.php?source=${encodeURIComponent(source)}`);
        const data = await res.json();

        if (data.success && Array.isArray(data.proxies)) {
            setProxyList(mergeProxyLists(isAppend ? proxies : [], data.proxies), isAppend);
        } else {
            showModalDialog({ title: t("Ошибка загрузки"), message: serverError(data.error) || t("Неизвестная ошибка") });
        }
    } catch (e) {
        showModalDialog({ title: t("Ошибка сети"), message: e.message });
    } finally {
        btn.disabled = false;
        btn.innerHTML = t("Скачать прокси");
    }
}

function proxyKey(p) {
    return String(p).replace(/^[a-z0-9]+:\/\//i, '');
}

// Объединение списков: существующие первыми, дубли по ip:port отбрасываются, не больше PROXY_LIMIT, сортировка по IP
function mergeProxyLists(current, incoming) {
    const seen = new Set();
    const out = [];
    for (const p of current.concat(incoming)) {
        const key = proxyKey(p);
        if (seen.has(key)) continue;
        seen.add(key);
        out.push({ p, ...parseIpPort(p) }); // IP разбираем один раз, а не в каждом сравнении
        if (out.length >= PROXY_LIMIT) break;
    }
    return out.sort((a, b) => (a.num - b.num) || (a.port - b.port)).map(x => x.p);
}

// Результаты старого списка для нового (по ip:port): при «Добавить к текущим» проверенные не сбрасываются
function carryResults(oldProxies, oldResults, list) {
    const byKey = new Map();
    oldResults.forEach((r, i) => { if (r) byKey.set(proxyKey(oldProxies[i]), r); });
    return list.map((p, i) => {
        const r = byKey.get(proxyKey(p)) || null;
        if (r) r.index = i;
        return r;
    });
}

// Новый список: таблица с нуля + сохранение. keepResults — оставить результаты уже проверенных
function setProxyList(list, keepResults = false) {
    completedAt = null;
    renderFooter();
    results = keepResults ? carryResults(proxies, results, list) : new Array(list.length).fill(null);
    proxies = list;
    renderInitialTable();
    updateControlButtons(idleButtonsState());
    saveState();
    enqueueMissingGeo(false);
}

// Строка таблицы по proxies[i] и results[i] (null — ещё не проверен)
function buildRow(i) {
    const p = proxies[i];
    const d = results[i];
    const ok = !!(d && d.ok);
    const tr = document.createElement('tr');
    tr.id = 'r' + i;
    if (d) tr.className = 'done';
    tr.setAttribute('data-index', i);
    tr.setAttribute('data-proxy', p);
    tr.setAttribute('data-proto', ok ? d.proto : '');
    tr.setAttribute('data-ok', ok ? '1' : '0');
    tr.setAttribute('data-time', ok ? d.time : 999999);
    tr.setAttribute('data-speed', ok ? d.speed_bits : 0);
    tr.setAttribute('data-country', ok ? (d.country_code || d.country || '') : '');
    tr.setAttribute('data-country-code', ok ? (d.country_code || '') : '');
    tr.setAttribute('data-anon', anonLevel(d));

    const status = !d ? t("<span class=\"badge badge-wait\">Ожидание</span>")
        : ok ? `<span class="badge badge-ok">${esc(d.proto)}</span>`
        : t("<span class=\"badge badge-fail\">МЕРТВЫЙ</span>");
    tr.innerHTML = `
        <td id="p${i}" style="width: 330px; max-width: 330px; word-break: break-all;">${esc(p)}</td>
        <td class="${ok ? (d.time <= 1000 ? 'time-ok' : 'time-slow') : ''}">${ok ? d.time + t(" мс") : ''}</td>
        <td>${ok ? countryBadge(countryName(d)) : ''}</td>
        <td class="${ok ? (d.speed_bits >= 500000 ? 'speed-fast' : 'speed-slow') : ''}">${ok ? formatSpeed(d.speed_bits) : ''}</td>
        <td style="text-align: center;">${anonCellHtml(d)}</td>
        <td class="status-col">${status}</td>`;
    return tr;
}

function countryBadge(country) {
    return country && country !== '—' ? `<span class="badge badge-country">${esc(country)}</span>` : '';
}

function renderInitialTable() {
    const tbody = document.getElementById('tbody');
    tbody.innerHTML = '';
    const frag = document.createDocumentFragment();

    cntOk = 0; cntFail = 0; cntDone = 0;
    proxies.forEach((_, i) => {
        frag.appendChild(buildRow(i));
        const r = results[i];
        if (r) {
            cntDone++;
            if (r.ok) cntOk++; else cntFail++;
        }
    });
    tbody.appendChild(frag);

    setStats(cntDone);
    updateStatusText();
    updateCountryFilterOptions();
    applyFilters();
    applyCurrentSort();
}

// Состояние кнопок по результатам (после загрузки страницы и чистки списка)
function idleButtonsState() {
    if (cntDone === 0) return 'idle';
    return cntDone >= proxies.length ? 'finished' : 'paused';
}

// Страну и пометки ip-api запрашиваем для рабочих, у которых их нет: после чистки списка (индексы сменились)
// и после F5 — тогда повторяем и прошлые сбои ip-api (retryFailed)
function enqueueMissingGeo(retryFailed) {
    geoipQueue = [];
    results.forEach((r, i) => {
        if (r && r.ok && (!r.geo || (retryFailed && r.geo === '?'))) {
            geoipQueue.push({ index: i, proxyStr: proxies[i] });
        }
    });
    if (geoipQueue.length) processGeoipQueue();
}

function startCheck() {
    if (proxies.length === 0) {
        showModalDialog({ title: t("Список пуст"), message: t("Список прокси пуст! Сначала загрузите или вставьте прокси.") });
        return;
    }

    completedAt = null;
    renderFooter();
    cntOk = 0; cntFail = 0; cntDone = 0;
    results = new Array(proxies.length).fill(null);
    geoipQueue = [];
    renderInitialTable();

    const queue = proxies.map((_, i) => i);
    runPool(queue);
}

function resumeCheck() {
    if (proxies.length === 0) return;

    const queue = [];
    for (let i = 0; i < proxies.length; i++) {
        if (!results[i] || !results[i].finished) {
            queue.push(i);
        }
    }

    if (queue.length === 0) {
        showModalDialog({ title: t("Проверка завершена"), message: t("Все прокси в списке уже проверены!") });
        updateControlButtons('finished');
        return;
    }

    runPool(queue);
}

function stopCheck() {
    shouldStop = true;
    if (currentAbortController) {
        currentAbortController.abort();
    }
    document.getElementById('status').innerHTML = t("Проверка остановлена");
    updateControlButtons('paused');
}

// Пакетная проверка (пачка 500, таймаут 6 сек на каждый протокол)
async function runPool(queue) {
    if (queue.length === 0) return;

    const batchSize = 500;
    const targetUrl = document.getElementById('target-url-input').value.trim() || 'http://cp.cloudflare.com/generate_204';
    const timeoutSec = 6;

    isRunning = true;
    shouldStop = false;
    currentAbortController = new AbortController();
    updateControlButtons('running');

    processGeoipQueue();
    const rechecked = new Set(); // индексы, уже отправленные на повторную проверку в этом запуске

    while (queue.length > 0 && !shouldStop) {
        const batchIndices = queue.splice(0, batchSize);
        const batchPayload = {
            proxies: batchIndices.map(idx => ({ index: idx, proxy: proxies[idx] })),
            target_url: targetUrl,
            timeout: timeoutSec,
        };

        // Декоративный отсчёт в ячейке статуса: HTTP, HTTPS, SOCKS4, SOCKS5 по 5 с (5…1), затем «SPEED…» до ответа сервера
        const probeProtos = ['HTTP', 'HTTPS', 'SOCKS4', 'SOCKS5'];
        const stageSec = 5;
        let stage = 0;
        let sec = stageSec;
        const showStage = () => batchIndices.forEach(idx => {
            const tr = document.getElementById('r' + idx);
            if (tr && tr.cells[5]) {
                const label = stage < probeProtos.length ? t("{0} {1}с", probeProtos[stage], sec) : 'SPEED…';
                tr.cells[5].innerHTML = `<span class="badge badge-wait"><span class="spinner"></span>${label}</span>`;
            }
        });
        showStage();

        const timerId = setInterval(() => {
            if (stage >= probeProtos.length) return;
            sec--;
            if (sec < 1) {
                stage++;
                sec = stageSec;
            }
            showStage();
        }, 1000);

        const localTimeoutCtrl = new AbortController();
        // На сервере максимум: калибровка 2×3с + 4 протокола × timeoutSec + скорость/судья timeoutSec; +6с запас на сеть
        const localTimeoutId = setTimeout(() => localTimeoutCtrl.abort(), (timeoutSec * 5 + 12) * 1000);

        function onGlobalAbort() {
            localTimeoutCtrl.abort();
        }
        if (currentAbortController) {
            currentAbortController.signal.addEventListener('abort', onGlobalAbort, { once: true });
        }

        updateStatusText();

        try {
            const res = await fetch('proxy-check-batch.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(batchPayload),
                signal: localTimeoutCtrl.signal
            });
            const data = await res.json();

            clearInterval(timerId);
            clearTimeout(localTimeoutId);
            if (currentAbortController) {
                currentAbortController.signal.removeEventListener('abort', onGlobalAbort);
            }

            if (data && data.success && Array.isArray(data.results)) {
                const recheckNext = [];
                data.results.forEach(item => {
                    const idx = item.index;
                    const prev = results[idx];
                    item.finished = true;

                    // Повторная проверка: счётчики не меняются; не ответил — оставляем прошлый результат
                    if (rechecked.has(idx) && prev) {
                        if (!item.ok) {
                            renderRow(idx);
                            return;
                        }
                        if (item.tls === 'na') item.tls = prev.tls;
                        if (item.hdr === 'na') Object.assign(item, { hdr: prev.hdr, hdr_names: prev.hdr_names });
                        Object.assign(item, { country: prev.country, country_code: prev.country_code, geo: prev.geo });
                        results[idx] = item;
                        fillRow(idx, item);
                        return;
                    }

                    results[idx] = item;
                    fillRow(idx, item);
                    cntDone++;
                    if (item.ok) {
                        cntOk++;
                        enqueueGeoip(idx, item.proxy);
                    } else {
                        cntFail++;
                    }
                    if (needsRecheck(item)) {
                        rechecked.add(idx);
                        recheckNext.push(idx);
                    }
                });
                queue.unshift(...recheckNext); // в следующую пачку
            } else {
                throw new Error(serverError(data.error) || t("Ошибка ответа сервера"));
            }
        } catch(e) {
            clearInterval(timerId);
            clearTimeout(localTimeoutId);
            if (currentAbortController) {
                currentAbortController.signal.removeEventListener('abort', onGlobalAbort);
            }

            if (shouldStop) {
                batchIndices.forEach(idx => {
                    if (!rechecked.has(idx)) results[idx] = null; // у перепроверяемых остаётся прошлый результат
                    renderRow(idx);
                });
                break;
            }

            batchIndices.forEach(idx => {
                if (rechecked.has(idx) && results[idx]) {
                    renderRow(idx);
                    return;
                }
                const failData = {
                    index: idx,
                    proxy: proxies[idx],
                    ok: false,
                    proto: '—',
                    time: 0,
                    speed_bits: 0,
                    tls: '',
                    hdr: '',
                    hdr_names: '',
                    finished: true
                };
                results[idx] = failData;
                fillRow(idx, failData);
                cntDone++;
                cntFail++;
            });
        }

        setStats(cntDone);
        updateStatusText();
        saveState();
    }

    isRunning = false;
    saveState(); // в том числе после остановки: прерванная пачка снова «Ожидание»

    if (shouldStop) {
        updateControlButtons('paused');
    } else {
        completedAt = new Date();
        renderFooter();
        updateControlButtons(cntDone >= proxies.length ? 'finished' : 'paused');
    }

    updateCountryFilterOptions();
    applyFilters();
    updateStatusText();
}

function updateStatusText() {
    const statusElem = document.getElementById('status');
    if (!statusElem) return;

    const remainingGeo = geoipQueue.length;
    const isGeoActive = isGeoipRunning || remainingGeo > 0;
    const geoHtml = isGeoActive
        ? t(" &nbsp;·&nbsp; <span style=\"color: #58a6ff;\"><span class=\"spinner\"></span>Определение страны...{0}</span>", remainingGeo > 0 ? ' (' + remainingGeo + ')' : '')
        : '';

    if (isRunning) {
        statusElem.innerHTML = t("<span class=\"spinner\"></span>Проверка... Готово: <b>{0}</b> из <b>{1}</b>{2}", cntDone, proxies.length, geoHtml);
    } else if (shouldStop || (cntDone > 0 && cntDone < proxies.length)) {
        statusElem.innerHTML = t("Проверка остановлена (проверено {0} из {1}){2}", cntDone, proxies.length, geoHtml);
    } else if (cntDone > 0) {
        statusElem.innerHTML = t("Проверка завершена. Найдено рабочих: <b>{0}</b> из <b>{1}</b>{2}", cntOk, proxies.length, geoHtml);
    } else {
        statusElem.innerHTML = t("Готов к запуску (в очереди: {0})", proxies.length);
    }
}

// Перерисовать строку по results[i], сохранив скрытие фильтром
function renderRow(i) {
    const old = document.getElementById('r' + i);
    if (!old) return null;
    const tr = buildRow(i);
    if (old.classList.contains('row-hidden')) tr.classList.add('row-hidden');
    old.replaceWith(tr);
    return tr;
}

// Результат проверки в таблицу; счётчики считает runPool и анимирует setStats() раз на пачку
function fillRow(i, d) {
    if (d.ok) proxies[i] = d.proxy; // сервер возвращает адрес с найденным протоколом
    const tr = renderRow(i);
    if (!tr) return;

    // В реальном времени перемещаем найденный рабочий прокси выше
    if (d.ok) {
        if (currentSort === 'time-asc') {
            repositionRowByTime(tr, true);
        } else if (currentSort === 'speed') {
            repositionRowBySpeed(tr, sortSpeedDesc);
        }
    }
}

// Плавное перемещение одной строки без моргания
function repositionRowByTime(tr, asc) {
    const tbody = document.getElementById('tbody');
    const myTime = parseInt(tr.getAttribute('data-time')) || 999999;
    const allRows = Array.from(tbody.children);

    let target = null;
    for (let r of allRows) {
        if (r === tr) continue;
        const rOk = r.getAttribute('data-ok') === '1';
        if (!rOk) {
            target = r;
            break;
        }
        const rTime = parseInt(r.getAttribute('data-time')) || 999999;
        if (asc ? (myTime < rTime) : (myTime > rTime)) {
            target = r;
            break;
        }
    }

    if (target && target !== tr.nextSibling) {
        tbody.insertBefore(tr, target);
    } else if (!target && tr !== tbody.lastElementChild) {
        tbody.appendChild(tr);
    }
}

// Плавное перемещение строки по скорости
function repositionRowBySpeed(tr, desc) {
    const tbody = document.getElementById('tbody');
    const mySpeed = parseInt(tr.getAttribute('data-speed')) || 0;
    const allRows = Array.from(tbody.children);

    let target = null;
    for (let r of allRows) {
        if (r === tr) continue;
        const rOk = r.getAttribute('data-ok') === '1';
        if (!rOk) {
            target = r;
            break;
        }
        const rSpeed = parseInt(r.getAttribute('data-speed')) || 0;
        if (desc ? (mySpeed > rSpeed) : (mySpeed < rSpeed)) {
            target = r;
            break;
        }
    }

    if (target && target !== tr.nextSibling) {
        tbody.insertBefore(tr, target);
    } else if (!target && tr !== tbody.lastElementChild) {
        tbody.appendChild(tr);
    }
}

// Асинхронное фоновое определение GeoIP
function enqueueGeoip(index, proxyStr) {
    geoipQueue.push({ index, proxyStr });
    updateStatusText();
    if (!isGeoipRunning) {
        processGeoipQueue();
    }
}

// Ответ ip-api для прокси; geo = null — ip-api не ответил
function applyGeoToItem(item, geo) {
    const r = results[item.index];
    // Пока шёл запрос, список могли заменить — тогда этот индекс уже про другой прокси
    if (!r || !r.ok || proxies[item.index] !== item.proxyStr) return;
    if (geo) {
        r.country = geo.country;
        r.country_code = geo.country_code;
        r.geo = (geo.proxy ? 'p' : '') + (geo.hosting ? 'h' : '') || '-';
    } else {
        r.geo = '?';
    }
    const tr = document.getElementById('r' + item.index);
    if (!tr) return;
    tr.setAttribute('data-country', r.country_code || r.country || '');
    tr.setAttribute('data-country-code', r.country_code || '');
    tr.setAttribute('data-anon', anonLevel(r));
    tr.cells[2].innerHTML = countryBadge(countryName(r));
    tr.cells[4].innerHTML = anonCellHtml(r);
}

async function processGeoipQueue() {
    if (isGeoipRunning) return;
    isGeoipRunning = true;
    updateStatusText();

    while (geoipQueue.length > 0) {
        // Забираем пачку до 100 прокси за один сетевой запрос к пакетному GeoIP API
        const batch = geoipQueue.splice(0, 100);
        updateStatusText();

        const needFetchIps = new Set();
        const ipToItems = new Map();

        batch.forEach(item => {
            const clean = item.proxyStr.replace(/^[a-z0-9]+:\/\//i, '');
            const ip = clean.split(':')[0];
            if (!ip) return;

            if (!ipToItems.has(ip)) {
                ipToItems.set(ip, []);
            }
            ipToItems.get(ip).push(item);

            const cached = geoipCache.get(ip);
            if (cached) {
                applyGeoToItem(item, cached);
            } else {
                needFetchIps.add(ip);
            }
        });

        let pause = 200;
        if (needFetchIps.size > 0) {
            let found = {};
            let retryAfter = 0;
            try {
                const res = await fetch('proxy-geoip.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ips: Array.from(needFetchIps) })
                });
                const data = await res.json();
                if (data && data.success && data.results) {
                    found = data.results;
                    retryAfter = data.retry_after || 0;
                }
            } catch(e) {}

            // IP нет в ответе — ip-api не ответил (сеть, лимит запросов): повторяем один раз.
            // Страна «—» — ip-api ответил отказом (например, зарезервированный адрес): сбой, в кэш не кладём
            let requeued = false;
            for (const ip of needFetchIps) {
                const geo = found[ip];
                if (geo === undefined) {
                    ipToItems.get(ip).forEach(it => {
                        if (it.retry) {
                            applyGeoToItem(it, null);
                        } else {
                            geoipQueue.push({ ...it, retry: true });
                            requeued = true;
                        }
                    });
                    continue;
                }
                const ok = geo.country && geo.country !== '—';
                const geoObj = ok ? { country: geo.country, country_code: geo.country_code || '', proxy: !!geo.proxy, hosting: !!geo.hosting } : null;
                if (geoObj) geoipCache.set(ip, geoObj);
                ipToItems.get(ip).forEach(it => applyGeoToItem(it, geoObj));
            }
            // Лимит ip-api исчерпан — ждём, сколько он сказал (иначе бан до часа); сбой сети — пауза перед повтором
            if (retryAfter) pause = retryAfter * 1000;
            else if (requeued) pause = 3000;
        }

        updateCountryFilterOptions();
        applyFilters();
        saveState();

        if (geoipQueue.length > 0) {
            await new Promise(r => setTimeout(r, pause));
        }
    }

    isGeoipRunning = false;
    updateStatusText();
}

function setStats(checked) {
    animateValue('s-checked', checked);
    const remaining = Math.max(0, proxies.length - checked);
    animateValue('s-total', remaining);
    animateValue('s-ok', cntOk);
    animateValue('s-fail', cntFail);
    const pct = proxies.length > 0 ? (checked / proxies.length * 100) : 0;
    document.getElementById('progress').style.width = pct + '%';
}

function updateCountryFilterOptions() {
    const sel = document.getElementById('filter-country');
    const curr = sel.value;
    const countries = new Map();

    results.forEach(r => {
        if (r && r.ok && r.country && r.country !== '—') {
            countries.set(r.country_code || r.country, countryName(r));
        }
    });

    sel.innerHTML = t("<option value=\"ALL\">Все страны</option>");
    const sorted = Array.from(countries.keys()).sort((a, b) => countries.get(a).localeCompare(countries.get(b), lang));
    sorted.forEach((name) => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = countries.get(name);
        sel.appendChild(opt);
    });
    sel.value = countries.has(curr) ? curr : 'ALL';
}

// Фильтрация по стране и чекбоксам протоколов
function applyFilters() {
    const selectedCountry = document.getElementById('filter-country').value;
    const activeProtos = new Set();
    if (document.getElementById('proto-http').checked)   activeProtos.add('HTTP');
    if (document.getElementById('proto-https').checked)  activeProtos.add('HTTPS');
    if (document.getElementById('proto-socks4').checked) activeProtos.add('SOCKS4');
    if (document.getElementById('proto-socks5').checked) activeProtos.add('SOCKS5');

    const rows = document.querySelectorAll('#tbody tr');
    rows.forEach(tr => {
        const c = tr.getAttribute('data-country') || '';
        const p = tr.getAttribute('data-proto') || '';
        const isOk = tr.getAttribute('data-ok') === '1';

        const matchCountry = (selectedCountry === 'ALL' || c === selectedCountry);
        let matchProto = true;
        if (isOk && p) {
            matchProto = activeProtos.has(p);
        } else {
            matchProto = (activeProtos.size > 0);
        }

        if (matchCountry && matchProto) {
            tr.classList.remove('row-hidden');
        } else {
            tr.classList.add('row-hidden');
        }
    });
}

// Сортировка по задержке + IP
// Сброс иконок сортировки
function resetSortArrows(exceptId, activeHtml) {
    const arrows = ['sort-arrow', 'sort-ip-arrow', 'sort-country-arrow', 'sort-speed-arrow', 'sort-anon-arrow', 'sort-status-arrow'];
    arrows.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = (id === exceptId) ? activeHtml : ICON_SORT_DEFAULT;
    });
}

function toggleSortTime() {
    if (currentSort === 'time-asc') {
        currentSort = 'time-desc';
        resetSortArrows('sort-arrow', ICON_SORT_DOWN);
    } else {
        currentSort = 'time-asc';
        resetSortArrows('sort-arrow', ICON_SORT_UP);
    }
    sortByLatencyAndIp();
}

function sortByLatencyAndIp() {
    const tbody = document.getElementById('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const asc = (currentSort === 'time-asc');

    rows.sort((a, b) => {
        const aOk = a.getAttribute('data-ok') === '1' ? 1 : 0;
        const bOk = b.getAttribute('data-ok') === '1' ? 1 : 0;
        if (aOk !== bOk) return bOk - aOk;

        if (aOk && bOk) {
            const aTime = parseInt(a.getAttribute('data-time')) || 999999;
            const bTime = parseInt(b.getAttribute('data-time')) || 999999;
            if (aTime !== bTime) {
                return asc ? (aTime - bTime) : (bTime - aTime);
            }
        }

        const pA = parseIpPort(a.getAttribute('data-proxy') || '');
        const pB = parseIpPort(b.getAttribute('data-proxy') || '');
        if (pA.num !== pB.num) {
            return pA.num - pB.num;
        }
        return pA.port - pB.port;
    });

    const frag = document.createDocumentFragment();
    rows.forEach(tr => frag.appendChild(tr));
    tbody.appendChild(frag);
}

// 2. Сортировка по стране
function toggleSortCountry() {
    sortCountryAsc = !sortCountryAsc;
    currentSort = sortCountryAsc ? 'country-asc' : 'country-desc';
    resetSortArrows('sort-country-arrow', sortCountryAsc ? ICON_SORT_UP : ICON_SORT_DOWN);
    sortByCountry();
}

function sortByCountry() {
    const tbody = document.getElementById('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aOk = a.getAttribute('data-ok') === '1' ? 1 : 0;
        const bOk = b.getAttribute('data-ok') === '1' ? 1 : 0;
        if (aOk !== bOk) return bOk - aOk;

        const aC = countryName({ country: a.getAttribute('data-country'), country_code: a.getAttribute('data-country-code') });
        const bC = countryName({ country: b.getAttribute('data-country'), country_code: b.getAttribute('data-country-code') });

        if (!aC && bC) return 1;
        if (aC && !bC) return -1;
        if (aC && bC && aC !== bC) {
            return sortCountryAsc ? aC.localeCompare(bC, lang) : bC.localeCompare(aC, lang);
        }

        const aTime = parseInt(a.getAttribute('data-time')) || 999999;
        const bTime = parseInt(b.getAttribute('data-time')) || 999999;
        if (aTime !== bTime) {
            return aTime - bTime;
        }

        const pA = parseIpPort(a.getAttribute('data-proxy') || '');
        const pB = parseIpPort(b.getAttribute('data-proxy') || '');
        if (pA.num !== pB.num) {
            return pA.num - pB.num;
        }
        return pA.port - pB.port;
    });

    const frag = document.createDocumentFragment();
    rows.forEach(tr => frag.appendChild(tr));
    tbody.appendChild(frag);
}

// 3. Сортировка по скорости скачивания
function toggleSortSpeed() {
    sortSpeedDesc = !sortSpeedDesc;
    currentSort = 'speed';
    resetSortArrows('sort-speed-arrow', sortSpeedDesc ? ICON_SORT_DOWN : ICON_SORT_UP);
    sortBySpeed();
}

function sortBySpeed() {
    const tbody = document.getElementById('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aOk = a.getAttribute('data-ok') === '1' ? 1 : 0;
        const bOk = b.getAttribute('data-ok') === '1' ? 1 : 0;
        if (aOk !== bOk) return bOk - aOk;

        const aSpd = parseInt(a.getAttribute('data-speed')) || 0;
        const bSpd = parseInt(b.getAttribute('data-speed')) || 0;
        if (aSpd !== bSpd) {
            return sortSpeedDesc ? (bSpd - aSpd) : (aSpd - bSpd);
        }

        const aTime = parseInt(a.getAttribute('data-time')) || 999999;
        const bTime = parseInt(b.getAttribute('data-time')) || 999999;
        return aTime - bTime;
    });

    const frag = document.createDocumentFragment();
    rows.forEach(tr => frag.appendChild(tr));
    tbody.appendChild(frag);
}

// 4. Сортировка по статусу (протоколу)
function toggleSortStatus() {
    sortStatusAsc = !sortStatusAsc;
    currentSort = 'status';
    resetSortArrows('sort-status-arrow', sortStatusAsc ? ICON_SORT_UP : ICON_SORT_DOWN);
    sortByStatus();
}

function sortByStatus() {
    const tbody = document.getElementById('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aOk = a.getAttribute('data-ok') === '1' ? 1 : 0;
        const bOk = b.getAttribute('data-ok') === '1' ? 1 : 0;
        if (aOk !== bOk) return sortStatusAsc ? (bOk - aOk) : (aOk - bOk);

        const aP = a.getAttribute('data-proto') || '';
        const bP = b.getAttribute('data-proto') || '';
        if (aP !== bP) {
            return sortStatusAsc ? aP.localeCompare(bP) : bP.localeCompare(aP);
        }

        const aTime = parseInt(a.getAttribute('data-time')) || 999999;
        const bTime = parseInt(b.getAttribute('data-time')) || 999999;
        return aTime - bTime;
    });

    const frag = document.createDocumentFragment();
    rows.forEach(tr => frag.appendChild(tr));
    tbody.appendChild(frag);
}

// 5. Сортировка по анонимности
let sortAnonDesc = true;
function toggleSortAnon() {
    sortAnonDesc = !sortAnonDesc;
    currentSort = 'anon';
    resetSortArrows('sort-anon-arrow', sortAnonDesc ? ICON_SORT_DOWN : ICON_SORT_UP);
    sortByAnon();
}

function sortByAnon() {
    const tbody = document.getElementById('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aOk = a.getAttribute('data-ok') === '1' ? 1 : 0;
        const bOk = b.getAttribute('data-ok') === '1' ? 1 : 0;
        if (aOk !== bOk) return bOk - aOk;

        const aAnon = parseInt(a.getAttribute('data-anon') ?? '-1');
        const bAnon = parseInt(b.getAttribute('data-anon') ?? '-1');
        if (aAnon !== bAnon) {
            return sortAnonDesc ? (bAnon - aAnon) : (aAnon - bAnon);
        }

        const aTime = parseInt(a.getAttribute('data-time')) || 999999;
        const bTime = parseInt(b.getAttribute('data-time')) || 999999;
        return aTime - bTime;
    });

    const frag = document.createDocumentFragment();
    rows.forEach(tr => frag.appendChild(tr));
    tbody.appendChild(frag);
}

function parseIpPort(str) {
    const clean = String(str || '').replace(/^[a-z0-9]+:\/\//i, '');
    const m = clean.match(/^([0-9]{1,3}(?:\.[0-9]{1,3}){3})(?::([0-9]+))?/);
    if (!m) return { num: 0, port: 0, raw: clean };
    const parts = m[1].split('.').map(Number);
    const num = ((parts[0] || 0) * 16777216) + ((parts[1] || 0) * 65536) + ((parts[2] || 0) * 256) + (parts[3] || 0);
    return { num, port: parseInt(m[2]) || 0, raw: clean };
}

// 5. Сортировка по IP
function toggleSortIp() {
    currentSort = (currentSort === 'ip-asc') ? 'ip-desc' : 'ip-asc';
    resetSortArrows('sort-ip-arrow', currentSort === 'ip-asc' ? ICON_SORT_UP : ICON_SORT_DOWN);
    sortByIp();
}

function sortByIp() {
    const asc = (currentSort === 'ip-asc');
    const tbody = document.getElementById('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aOk = a.getAttribute('data-ok') === '1' ? 1 : 0;
        const bOk = b.getAttribute('data-ok') === '1' ? 1 : 0;
        if (aOk !== bOk) return bOk - aOk;

        const pA = parseIpPort(a.getAttribute('data-proxy') || '');
        const pB = parseIpPort(b.getAttribute('data-proxy') || '');
        if (pA.num !== pB.num) {
            return asc ? (pA.num - pB.num) : (pB.num - pA.num);
        }
        return asc ? (pA.port - pB.port) : (pB.port - pA.port);
    });

    const frag = document.createDocumentFragment();
    rows.forEach(tr => frag.appendChild(tr));
    tbody.appendChild(frag);
}

// Единая функция применения активной сортировки
function applyCurrentSort() {
    if (currentSort === 'time-asc' || currentSort === 'time-desc') {
        sortByLatencyAndIp();
    } else if (currentSort === 'speed') {
        sortBySpeed();
    } else if (currentSort === 'status') {
        sortByStatus();
    } else if (currentSort === 'ip-asc' || currentSort === 'ip-desc') {
        sortByIp();
    } else if (currentSort === 'country-asc' || currentSort === 'country-desc') {
        sortByCountry();
    } else if (currentSort === 'anon') {
        sortByAnon();
    } else {
        sortByLatencyAndIp();
    }
}

// Новый список после чистки: результаты сохраняются (в том числе непроверенные и нерабочие, если их оставили)
function applyFilteredList(targetProxies, targetResults, msg) {
    proxies = targetProxies;
    results = targetResults;
    results.forEach((r, i) => { if (r) r.index = i; });

    renderInitialTable(); // строки, счётчики, фильтры и текущая сортировка
    const tbody = document.getElementById('tbody');

    // Синхронизируем proxies и results с отсортированным порядком строк
    const sortedProxies = [];
    const sortedResults = [];
    tbody.querySelectorAll('tr').forEach((tr, newIdx) => {
        const oldIdx = parseInt(tr.getAttribute('data-index'));
        const r = results[oldIdx];
        if (r) r.index = newIdx;
        sortedProxies.push(proxies[oldIdx]);
        sortedResults.push(r);
        tr.setAttribute('data-index', newIdx);
        tr.id = 'r' + newIdx;
        if (tr.cells[0]) tr.cells[0].id = 'p' + newIdx;
    });
    proxies = sortedProxies;
    results = sortedResults;

    updateControlButtons(idleButtonsState());
    saveState();
    enqueueMissingGeo(false);
    document.getElementById('status').innerHTML = msg;
}

function removeBadProxies() {
    if (isRunning) {
        document.getElementById('status').textContent = t("Сначала остановите проверку кнопкой «Стоп»!");
        return;
    }
    const keepProxies = [];
    const keepResults = [];
    for (let i = 0; i < proxies.length; i++) {
        if (results[i] && !results[i].ok) continue;
        keepProxies.push(proxies[i]);
        keepResults.push(results[i]);
    }
    const removed = proxies.length - keepProxies.length;
    if (!removed) {
        document.getElementById('status').textContent = t("В списке нет мертвых прокси.");
        return;
    }
    applyFilteredList(keepProxies, keepResults, t("Удалено мертвых: <b>{0}</b>. Осталось: <b>{1}</b>", removed, keepProxies.length));
}

// Опасные — см. isDangerous(): подмена TLS и «Не удалось проверить»; всё остальное остаётся
function removeDangerousProxies() {
    if (isRunning) {
        showModalDialog({ title: t("Проверка активна"), message: t("Сначала остановите проверку кнопкой «Стоп»!") });
        return;
    }
    // До ответа ip-api статус «Не удалось проверить» временный — не удаляем такие прокси раньше времени
    if (isGeoipRunning || geoipQueue.length > 0) {
        showModalDialog({ title: t("Подождите"), message: t("ip-api ещё проверяет рабочие прокси. Пока он не ответил, их статус «Не удалось проверить» временный.") });
        return;
    }

    const keepProxies = [];
    const keepResults = [];
    for (let i = 0; i < proxies.length; i++) {
        if (isDangerous(results[i])) continue;
        keepProxies.push(proxies[i]);
        keepResults.push(results[i]);
    }

    const removedCount = proxies.length - keepProxies.length;
    if (removedCount === 0) {
        showModalDialog({ title: t("Удаление опасных"), message: t("Среди рабочих прокси нет опасных: с подменой TLS или не прошедших проверку.") });
        return;
    }

    showModalDialog({
        title: t("Удалить опасные прокси?"),
        message: t("Будет удалено {0} прокси: подменяют TLS-сертификат или их не удалось проверить.\nОстанется {1}.", removedCount, keepProxies.length),
        isConfirm: true,
        confirmText: t("Удалить опасные"),
        onConfirm: () => {
            applyFilteredList(keepProxies, keepResults, t("Удалено <b>{0}</b> опасных прокси. Осталось: <b>{1}</b>", removedCount, keepProxies.length));
        }
    });
}

function copyWorkingToClipboard() {
    const working = [];
    const seen = new Set();
    const cleanKey = (p) => String(p).toLowerCase().replace(/^[a-z0-9]+:\/\//, '').trim();
    const rows = document.querySelectorAll('#tbody tr');
    rows.forEach(tr => {
        if (tr.getAttribute('data-ok') === '1') {
            const p = tr.getAttribute('data-proxy');
            if (p) {
                const key = cleanKey(p);
                if (!seen.has(key)) {
                    seen.add(key);
                    working.push(p);
                }
            }
        }
    });

    if (working.length === 0) {
        showModalDialog({ title: t("Буфер обмена"), message: t("В таблице пока нет проверенных рабочих прокси для копирования!") });
        return;
    }

    navigator.clipboard.writeText(working.join('\n')).then(() => {
        showModalDialog({ title: t("Скопировано"), message: t("Скопировано {0} уникальных рабочих прокси в буфер обмена!", working.length) });
    }).catch(err => {
        showModalDialog({ title: t("Ошибка буфера"), message: t("Скопируйте рабочие прокси вручную:\n") + working.join('\n') });
    });
}

function confirmClearAllProxies() {
    if (isRunning) {
        showModalDialog({ title: t("Проверка активна"), message: t("Сначала остановите проверку кнопкой «Стоп»!") });
        return;
    }
    showModalDialog({
        title: t("Очистить список?"),
        message: t("Вы уверены, что хотите полностью очистить список прокси (в таблице и сохранённый в браузере)?"),
        isConfirm: true,
        confirmText: t("Очистить"),
        onConfirm: () => setProxyList([])
    });
}

function openPasteModal() {
    if (navigator.clipboard && navigator.clipboard.readText) {
        // Без разрешения на чтение буфера просто открываем пустое окно (промис отклоняется — try/catch его не ловит)
        navigator.clipboard.readText().then(clipText => {
            if (clipText && clipText.includes(':')) {
                document.getElementById('paste-textarea').value = clipText.trim();
            }
        }).catch(() => {});
    }
    document.getElementById('paste-modal').style.display = 'flex';
}

function closePasteModal() {
    document.getElementById('paste-modal').style.display = 'none';
    document.getElementById('paste-textarea').value = '';
}

async function submitPasteProxies(isAppend) {
    if (isRunning) {
        showModalDialog({ title: t("Проверка активна"), message: t("Сначала остановите проверку кнопкой «Стоп»!") });
        return;
    }
    const text = document.getElementById('paste-textarea').value.trim();
    if (!text) {
        showModalDialog({ title: t("Пустой ввод"), message: t("Введите или вставьте хотя бы одну строку прокси!") });
        return;
    }

    // Маска адресов — на сервере (одна для вставки и скачивания, proxy-lib.php)
    try {
        const res = await fetch('proxy-fetch.php?source=paste', {
            method: 'POST',
            headers: { 'Content-Type': 'text/plain; charset=utf-8' },
            body: text
        });
        const data = await res.json();
        if (!data.success || !Array.isArray(data.proxies)) {
            showModalDialog({ title: t("Нет корректных адресов"), message: serverError(data.error) || t("Неизвестная ошибка") });
            return;
        }
        closePasteModal();
        setProxyList(mergeProxyLists(isAppend ? proxies : [], data.proxies), isAppend);
    } catch(e) {
        showModalDialog({ title: t("Ошибка сети"), message: e.message });
    }
}

// Подробности статуса — по клику на круг в колонке «Анонимный»
document.getElementById('tbody').addEventListener('click', (e) => {
    const dot = e.target.closest('.dot');
    if (!dot) return;
    showAnonDetails(results[parseInt(dot.closest('tr').getAttribute('data-index'))]);
});

// Инициализация: список и результаты из localStorage
applyLanguage();
renderAnonLegend();
renderInitialTable();
checkHttpsWarning();
updateControlButtons(idleButtonsState());
enqueueMissingGeo(true);
</script>

</body>
</html>
