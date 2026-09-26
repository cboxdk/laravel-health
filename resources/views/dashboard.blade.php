<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Dashboard</title>
    <style>
        :root {
            --bg: #f9fafb;
            --surface: #ffffff;
            --surface-muted: #f9fafb;
            --border: #e5e7eb;
            --text: #111827;
            --text-secondary: #4b5563;
            --text-muted: #6b7280;
            --text-faint: #9ca3af;
            --track: #e5e7eb;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);

            --ok: #22c55e;
            --ok-bg: #dcfce7;
            --ok-fg: #166534;
            --ok-hero: #f0fdf4;
            --warning: #eab308;
            --warning-bg: #fef9c3;
            --warning-fg: #854d0e;
            --warning-hero: #fefce8;
            --critical: #ef4444;
            --critical-bg: #fee2e2;
            --critical-fg: #991b1b;
            --critical-hero: #fef2f2;
            --unknown: #6b7280;
            --unknown-bg: #f3f4f6;
            --unknown-fg: #1f2937;
            --unknown-hero: #f9fafb;
            --info-bg: #dbeafe;
            --info-fg: #1e40af;

            --radius: 0.5rem;
            --font: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #111827;
                --surface: #1f2937;
                --surface-muted: #111827;
                --border: #374151;
                --text: #ffffff;
                --text-secondary: #d1d5db;
                --text-muted: #9ca3af;
                --text-faint: #6b7280;
                --track: #374151;
                --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.4);

                --ok-bg: #14532d;
                --ok-fg: #bbf7d0;
                --ok-hero: rgba(20, 83, 45, 0.2);
                --warning-bg: #713f12;
                --warning-fg: #fef08a;
                --warning-hero: rgba(113, 63, 18, 0.2);
                --critical-bg: #7f1d1d;
                --critical-fg: #fecaca;
                --critical-hero: rgba(127, 29, 29, 0.2);
                --unknown-bg: #374151;
                --unknown-fg: #e5e7eb;
                --unknown-hero: #1f2937;
                --info-bg: #1e3a8a;
                --info-fg: #bfdbfe;
            }
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: var(--font);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, p { margin: 0; }

        .container {
            max-width: 80rem;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        @media (min-width: 640px) { .container { padding-left: 1.5rem; padding-right: 1.5rem; } }
        @media (min-width: 1024px) { .container { padding-left: 2rem; padding-right: 2rem; } }

        .section { margin-bottom: 2rem; }

        /* Header */
        .title { font-size: 1.875rem; line-height: 2.25rem; font-weight: 700; }
        .subtitle { margin-top: 0.25rem; font-size: 0.875rem; color: var(--text-muted); }

        /* Tags and badges */
        .tag, .badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            line-height: 1rem;
            font-weight: 500;
        }
        .tag { padding: 0.125rem 0.5rem; border-radius: 0.25rem; }
        .badge { padding: 0.125rem 0.625rem; border-radius: 9999px; }
        .tag--refresh { margin-left: 0.5rem; }

        .is-ok { background: var(--ok-bg); color: var(--ok-fg); }
        .is-warning { background: var(--warning-bg); color: var(--warning-fg); }
        .is-critical { background: var(--critical-bg); color: var(--critical-fg); }
        .is-unknown { background: var(--unknown-bg); color: var(--unknown-fg); }
        .is-info { background: var(--info-bg); color: var(--info-fg); }

        /* Overall status hero */
        .hero {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }
        .hero-icon {
            flex-shrink: 0;
            width: 4rem;
            height: 4rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.875rem;
            line-height: 1;
        }
        .hero-title { font-size: 1.5rem; line-height: 2rem; font-weight: 700; }
        .hero-meta { font-size: 0.875rem; color: var(--text-muted); }

        .hero--ok { background: var(--ok-hero); }
        .hero--ok .hero-icon { background: var(--ok); }
        .hero--ok .hero-title { color: var(--ok); }
        .hero--warning { background: var(--warning-hero); }
        .hero--warning .hero-icon { background: var(--warning); }
        .hero--warning .hero-title { color: var(--warning); }
        .hero--critical { background: var(--critical-hero); }
        .hero--critical .hero-icon { background: var(--critical); }
        .hero--critical .hero-title { color: var(--critical); }
        .hero--unknown { background: var(--unknown-hero); }
        .hero--unknown .hero-icon { background: var(--unknown); }
        .hero--unknown .hero-title { color: var(--unknown); }

        /* Cards */
        .card {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .card-body { padding: 1.5rem; }
        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .card-title { font-size: 1.125rem; line-height: 1.75rem; font-weight: 600; }
        .card-body > .card-title { margin-bottom: 1rem; }
        .card-label {
            margin-bottom: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Grids */
        .grid { display: grid; gap: 1.5rem; grid-template-columns: minmax(0, 1fr); }
        .facts { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .facts--wide { column-gap: 2rem; }
        @media (min-width: 768px) {
            .grid--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .facts--5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
            .facts--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 479px) {
            .facts { grid-template-columns: minmax(0, 1fr); }
        }

        .fact-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; }
        .fact-value { font-size: 0.875rem; font-weight: 600; overflow-wrap: anywhere; }
        .fact-note { font-size: 0.75rem; font-weight: 400; color: var(--text-faint); }

        /* Key/value rows */
        .rows > * + * { margin-top: 0.75rem; }
        .row { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.875rem; }
        .row-label { color: var(--text-secondary); }
        .row-value { font-weight: 600; }
        .rows-footer {
            padding-top: 0.5rem;
            border-top: 1px solid var(--border);
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .details { margin-top: 0.75rem; font-size: 0.75rem; color: var(--text-muted); }
        .details > * + * { margin-top: 0.25rem; }
        .details .row { font-size: 0.75rem; }

        /* Progress bars */
        .bar { width: 100%; height: 0.5rem; background: var(--track); border-radius: 9999px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 9999px; }
        .bar-fill--ok { background: var(--ok); }
        .bar-fill--warning { background: var(--warning); }
        .bar-fill--critical { background: var(--critical); }
        .meter { display: flex; align-items: center; gap: 0.75rem; }
        .meter .bar { flex: 1; min-width: 4rem; }
        .meter-value { width: 3rem; text-align: right; font-size: 0.875rem; font-weight: 500; }
        .usage-head { margin-bottom: 0.25rem; }

        /* Tables */
        .table-wrap { overflow-x: auto; }
        .table { min-width: 100%; border-collapse: collapse; }
        .table thead { background: var(--surface-muted); }
        .table th {
            padding: 0.75rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        .table td {
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            color: var(--text-muted);
            white-space: nowrap;
            border-top: 1px solid var(--border);
        }
        .table--compact td { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .table td.cell-strong { font-weight: 500; color: var(--text); }
        .table .num { text-align: right; }
        .col-usage { width: 25%; }

        .footer { text-align: center; font-size: 0.875rem; color: var(--text-muted); }
    </style>
</head>
<body>
    <div class="container" id="dashboard">
        {{-- Header --}}
        <div class="section">
            <h1 class="title">Health Dashboard</h1>
            <p class="subtitle">
                @if($hostname)
                    <span class="tag is-unknown">{{ $hostname }}</span>
                    &middot;
                @endif
                Last updated: <span id="last-updated">{{ now()->format('H:i:s') }}</span>
                <span class="tag tag--refresh is-ok" id="auto-refresh-badge">
                    Auto-refresh: 10s
                </span>
            </p>
        </div>

        {{-- Overall Status Hero --}}
        @php
            $overallStatus = $readiness->status->value;
            $statusConfig = match($overallStatus) {
                'ok' => ['modifier' => 'ok', 'icon' => '&#10003;', 'label' => 'All Systems Operational'],
                'warning' => ['modifier' => 'warning', 'icon' => '&#9888;', 'label' => 'Degraded Performance'],
                'critical' => ['modifier' => 'critical', 'icon' => '&#10007;', 'label' => 'System Outage'],
                default => ['modifier' => 'unknown', 'icon' => '?', 'label' => 'Unknown'],
            };
        @endphp

        <div class="section hero hero--{{ $statusConfig['modifier'] }}">
            <div class="hero-icon">
                <span>{!! $statusConfig['icon'] !!}</span>
            </div>
            <div>
                <h2 class="hero-title">{{ $statusConfig['label'] }}</h2>
                <p class="hero-meta">
                    Checked at {{ $readiness->checkedAt->format('Y-m-d H:i:s') }} &middot;
                    Total duration: {{ number_format($readiness->totalDurationMs, 1) }}ms
                </p>
            </div>
        </div>

        {{-- Health Checks Table --}}
        <div class="section card">
            <div class="card-header">
                <h3 class="card-title">Health Checks</h3>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Check</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allResults = collect(array_merge($liveness->results, $readiness->results))->unique('name');
                        @endphp
                        @foreach($allResults as $result)
                            @php
                                $badgeClass = match($result->status->value) {
                                    'ok' => 'is-ok',
                                    'warning' => 'is-warning',
                                    'critical' => 'is-critical',
                                    default => 'is-unknown',
                                };
                            @endphp
                            <tr>
                                <td class="cell-strong">
                                    {{ $result->name }}
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $result->status->value }}
                                    </span>
                                </td>
                                <td>
                                    {{ $result->message ?: '-' }}
                                </td>
                                <td>
                                    {{ number_format($result->durationMs, 2) }}ms
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- System Info --}}
        @if(isset($systemMetrics['environment']) && $systemMetrics['environment'])
            <div class="section card card-body">
                <h3 class="card-title">System Info</h3>
                <div class="facts facts--5">
                    <div>
                        <span class="fact-label">OS</span>
                        <p class="fact-value">{{ $systemMetrics['environment']['os'] }}</p>
                    </div>
                    <div>
                        <span class="fact-label">Version</span>
                        <p class="fact-value">{{ $systemMetrics['environment']['os_version'] }}</p>
                    </div>
                    <div>
                        <span class="fact-label">Kernel</span>
                        <p class="fact-value">{{ $systemMetrics['environment']['kernel'] }}</p>
                    </div>
                    <div>
                        <span class="fact-label">Architecture</span>
                        <p class="fact-value">{{ $systemMetrics['environment']['architecture'] }}</p>
                    </div>
                    <div>
                        <span class="fact-label">Environment</span>
                        <p class="fact-value">
                            @if($systemMetrics['environment']['containerized'])
                                <span class="tag is-info">Container</span>
                            @else
                                <span class="tag is-unknown">Host</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- System Metrics --}}
        <div class="section grid grid--2">
            {{-- CPU/Load Card --}}
            @if(isset($systemMetrics['load']) && $systemMetrics['load'])
                <div class="card card-body">
                    <h4 class="card-label">CPU Load</h4>
                    <div class="rows">
                        <div class="row">
                            <span class="row-label">1 min</span>
                            <span class="row-value">{{ number_format($systemMetrics['load']['load_1m'], 2) }}</span>
                        </div>
                        <div class="row">
                            <span class="row-label">5 min</span>
                            <span class="row-value">{{ number_format($systemMetrics['load']['load_5m'], 2) }}</span>
                        </div>
                        <div class="row">
                            <span class="row-label">15 min</span>
                            <span class="row-value">{{ number_format($systemMetrics['load']['load_15m'], 2) }}</span>
                        </div>
                        @if($systemMetrics['load']['core_count'])
                            <div class="rows-footer">
                                <span>{{ $systemMetrics['load']['core_count'] }} cores</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Memory Card --}}
            @if(isset($systemMetrics['memory']) && $systemMetrics['memory'])
                @php
                    $memPercent = $systemMetrics['memory']['used_percent'];
                    $memBarColor = $memPercent > 90 ? 'bar-fill--critical' : ($memPercent > 75 ? 'bar-fill--warning' : 'bar-fill--ok');
                @endphp
                <div class="card card-body">
                    <h4 class="card-label">Memory</h4>
                    <div>
                        <div class="row usage-head">
                            <span class="row-label">Usage</span>
                            <span class="row-value">{{ $memPercent }}%</span>
                        </div>
                        <div class="bar">
                            <div class="bar-fill {{ $memBarColor }}" style="width: {{ min($memPercent, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="details">
                        <div class="row">
                            <span>Used</span>
                            <span>{{ number_format($systemMetrics['memory']['used_bytes'] / 1073741824, 1) }} GB</span>
                        </div>
                        <div class="row">
                            <span>Total</span>
                            <span>{{ number_format($systemMetrics['memory']['total_bytes'] / 1073741824, 1) }} GB</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Disk Usage Table --}}
        @if(isset($systemMetrics['storage']) && $systemMetrics['storage'])
            <div class="section card">
                <div class="card-header">
                    <h3 class="card-title">Disk Usage</h3>
                </div>
                <div class="table-wrap">
                    <table class="table table--compact">
                        <thead>
                            <tr>
                                <th>Mount</th>
                                <th>Device</th>
                                <th class="col-usage">Usage</th>
                                <th class="num">Used</th>
                                <th class="num">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($systemMetrics['storage'] as $mount)
                                @php
                                    $diskPercent = $mount['used_percent'];
                                    $diskBarColor = $diskPercent > 90 ? 'bar-fill--critical' : ($diskPercent > 75 ? 'bar-fill--warning' : 'bar-fill--ok');
                                @endphp
                                <tr>
                                    <td class="cell-strong">
                                        {{ $mount['mountpoint'] }}
                                    </td>
                                    <td>
                                        {{ $mount['device'] }}
                                    </td>
                                    <td>
                                        <div class="meter">
                                            <div class="bar">
                                                <div class="bar-fill {{ $diskBarColor }}" style="width: {{ min($diskPercent, 100) }}%"></div>
                                            </div>
                                            <span class="meter-value row-value">{{ $diskPercent }}%</span>
                                        </div>
                                    </td>
                                    <td class="num">
                                        {{ number_format($mount['used_bytes'] / 1073741824, 1) }} GB
                                    </td>
                                    <td class="num">
                                        {{ number_format($mount['total_bytes'] / 1073741824, 1) }} GB
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Network Table --}}
        @if(isset($systemMetrics['network']) && $systemMetrics['network'])
            <div class="section card">
                <div class="card-header">
                    <h3 class="card-title">Network Interfaces</h3>
                </div>
                <div class="table-wrap">
                    <table class="table table--compact">
                        <thead>
                            <tr>
                                <th>Interface</th>
                                <th>Status</th>
                                <th class="num">Received</th>
                                <th class="num">Sent</th>
                                <th class="num">RX Errors</th>
                                <th class="num">TX Errors</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($systemMetrics['network'] as $iface)
                                <tr>
                                    <td class="cell-strong">
                                        {{ $iface['name'] }}
                                    </td>
                                    <td>
                                        @if($iface['is_up'])
                                            <span class="badge is-ok">up</span>
                                        @else
                                            <span class="badge is-unknown">down</span>
                                        @endif
                                    </td>
                                    <td class="num">
                                        @if($iface['rx_bytes'] > 1073741824)
                                            {{ number_format($iface['rx_bytes'] / 1073741824, 2) }} GB
                                        @elseif($iface['rx_bytes'] > 1048576)
                                            {{ number_format($iface['rx_bytes'] / 1048576, 1) }} MB
                                        @else
                                            {{ number_format($iface['rx_bytes'] / 1024, 1) }} KB
                                        @endif
                                    </td>
                                    <td class="num">
                                        @if($iface['tx_bytes'] > 1073741824)
                                            {{ number_format($iface['tx_bytes'] / 1073741824, 2) }} GB
                                        @elseif($iface['tx_bytes'] > 1048576)
                                            {{ number_format($iface['tx_bytes'] / 1048576, 1) }} MB
                                        @else
                                            {{ number_format($iface['tx_bytes'] / 1024, 1) }} KB
                                        @endif
                                    </td>
                                    <td class="num">
                                        {{ number_format($iface['rx_errors']) }}
                                    </td>
                                    <td class="num">
                                        {{ number_format($iface['tx_errors']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Container Info --}}
        @if(isset($systemMetrics['container']) && $systemMetrics['container'])
            @php $c = $systemMetrics['container']; @endphp
            <div class="section card card-body">
                <h3 class="card-title">Container</h3>
                <div class="facts facts--3 facts--wide">
                    <div>
                        <span class="fact-label">Cgroup</span>
                        <p class="fact-value">{{ $c['cgroup_version'] }}</p>
                    </div>
                    @if($c['cpu_quota'])
                        <div>
                            <span class="fact-label">CPU Limit</span>
                            <p class="fact-value">
                                {{ $c['cpu_quota'] }} cores
                                @if($c['host_cpu_cores'])
                                    <span class="fact-note">/ {{ $c['host_cpu_cores'] }} host</span>
                                @endif
                            </p>
                        </div>
                    @endif
                    @if($c['memory_limit_bytes'])
                        <div>
                            <span class="fact-label">Memory Limit</span>
                            <p class="fact-value">
                                {{ number_format($c['memory_limit_bytes'] / 1048576) }} MB
                                @if($c['host_memory_bytes'])
                                    <span class="fact-note">/ {{ number_format($c['host_memory_bytes'] / 1073741824, 1) }} GB host</span>
                                @endif
                            </p>
                        </div>
                    @endif
                    <div>
                        <span class="fact-label">CPU Throttled</span>
                        <p class="fact-value">{{ number_format($c['cpu_throttled_count'] ?? 0) }}</p>
                    </div>
                    <div>
                        <span class="fact-label">OOM Kills</span>
                        <p class="fact-value">{{ $c['oom_kill_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Uptime --}}
        @if(isset($systemMetrics['uptime']) && $systemMetrics['uptime'])
            <div class="footer">
                Uptime: {{ $systemMetrics['uptime']['human_readable'] }}
            </div>
        @endif
    </div>

    <script>
        (function() {
            const refreshInterval = 10000;

            setTimeout(function() {
                window.location.reload();
            }, refreshInterval);
        })();
    </script>
</body>
</html>
