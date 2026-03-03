<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" id="dashboard">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Health Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($hostname)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">{{ $hostname }}</span>
                    &middot;
                @endif
                Last updated: <span id="last-updated">{{ now()->format('H:i:s') }}</span>
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200" id="auto-refresh-badge">
                    Auto-refresh: 10s
                </span>
            </p>
        </div>

        {{-- Overall Status Hero --}}
        @php
            $overallStatus = $readiness->status->value;
            $statusConfig = match($overallStatus) {
                'ok' => ['bg' => 'bg-green-500', 'text' => 'text-green-500', 'bgLight' => 'bg-green-50 dark:bg-green-900/20', 'icon' => '&#10003;', 'label' => 'All Systems Operational'],
                'warning' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-500', 'bgLight' => 'bg-yellow-50 dark:bg-yellow-900/20', 'icon' => '&#9888;', 'label' => 'Degraded Performance'],
                'critical' => ['bg' => 'bg-red-500', 'text' => 'text-red-500', 'bgLight' => 'bg-red-50 dark:bg-red-900/20', 'icon' => '&#10007;', 'label' => 'System Outage'],
                default => ['bg' => 'bg-gray-500', 'text' => 'text-gray-500', 'bgLight' => 'bg-gray-50 dark:bg-gray-800', 'icon' => '?', 'label' => 'Unknown'],
            };
        @endphp

        <div class="rounded-lg {{ $statusConfig['bgLight'] }} border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-16 h-16 {{ $statusConfig['bg'] }} rounded-full flex items-center justify-center">
                    <span class="text-3xl text-white">{!! $statusConfig['icon'] !!}</span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Checked at {{ $readiness->checkedAt->format('Y-m-d H:i:s') }} &middot;
                        Total duration: {{ number_format($readiness->totalDurationMs, 1) }}ms
                    </p>
                </div>
            </div>
        </div>

        {{-- Health Checks Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Health Checks</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Message</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $allResults = collect(array_merge($liveness->results, $readiness->results))->unique('name');
                        @endphp
                        @foreach($allResults as $result)
                            @php
                                $badgeClass = match($result->status->value) {
                                    'ok' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    'critical' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                };
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $result->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        {{ $result->status->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $result->message ?: '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Info</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">OS</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $systemMetrics['environment']['os'] }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Version</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $systemMetrics['environment']['os_version'] }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Kernel</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $systemMetrics['environment']['kernel'] }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Architecture</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $systemMetrics['environment']['architecture'] }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Environment</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            @if($systemMetrics['environment']['containerized'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Container</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Host</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- System Metrics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            {{-- CPU/Load Card --}}
            @if(isset($systemMetrics['load']) && $systemMetrics['load'])
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">CPU Load</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-300">1 min</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($systemMetrics['load']['load_1m'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-300">5 min</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($systemMetrics['load']['load_5m'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-300">15 min</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($systemMetrics['load']['load_15m'], 2) }}</span>
                        </div>
                        @if($systemMetrics['load']['core_count'])
                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $systemMetrics['load']['core_count'] }} cores</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Memory Card --}}
            @if(isset($systemMetrics['memory']) && $systemMetrics['memory'])
                @php
                    $memPercent = $systemMetrics['memory']['used_percent'];
                    $memBarColor = $memPercent > 90 ? 'bg-red-500' : ($memPercent > 75 ? 'bg-yellow-500' : 'bg-green-500');
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Memory</h4>
                    <div class="mb-2">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-300">Usage</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $memPercent }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="{{ $memBarColor }} h-2 rounded-full" style="width: {{ min($memPercent, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <div class="flex justify-between">
                            <span>Used</span>
                            <span>{{ number_format($systemMetrics['memory']['used_bytes'] / 1073741824, 1) }} GB</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total</span>
                            <span>{{ number_format($systemMetrics['memory']['total_bytes'] / 1073741824, 1) }} GB</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Disk Usage Table --}}
        @if(isset($systemMetrics['storage']) && $systemMetrics['storage'])
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Disk Usage</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Device</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4">Usage</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Used</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($systemMetrics['storage'] as $mount)
                                @php
                                    $diskPercent = $mount['used_percent'];
                                    $diskBarColor = $diskPercent > 90 ? 'bg-red-500' : ($diskPercent > 75 ? 'bg-yellow-500' : 'bg-green-500');
                                @endphp
                                <tr>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $mount['mountpoint'] }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $mount['device'] }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div class="{{ $diskBarColor }} h-2 rounded-full" style="width: {{ min($diskPercent, 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white w-12 text-right">{{ $diskPercent }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                        {{ number_format($mount['used_bytes'] / 1073741824, 1) }} GB
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Network Interfaces</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Interface</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Received</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sent</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">RX Errors</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">TX Errors</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($systemMetrics['network'] as $iface)
                                <tr>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $iface['name'] }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        @if($iface['is_up'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">up</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">down</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                        @if($iface['rx_bytes'] > 1073741824)
                                            {{ number_format($iface['rx_bytes'] / 1073741824, 2) }} GB
                                        @elseif($iface['rx_bytes'] > 1048576)
                                            {{ number_format($iface['rx_bytes'] / 1048576, 1) }} MB
                                        @else
                                            {{ number_format($iface['rx_bytes'] / 1024, 1) }} KB
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                        @if($iface['tx_bytes'] > 1073741824)
                                            {{ number_format($iface['tx_bytes'] / 1073741824, 2) }} GB
                                        @elseif($iface['tx_bytes'] > 1048576)
                                            {{ number_format($iface['tx_bytes'] / 1048576, 1) }} MB
                                        @else
                                            {{ number_format($iface['tx_bytes'] / 1024, 1) }} KB
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                        {{ number_format($iface['rx_errors']) }}
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Container</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-x-8 gap-y-4">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Cgroup</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $c['cgroup_version'] }}</p>
                    </div>
                    @if($c['cpu_quota'])
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">CPU Limit</span>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $c['cpu_quota'] }} cores
                                @if($c['host_cpu_cores'])
                                    <span class="text-xs font-normal text-gray-400 dark:text-gray-500">/ {{ $c['host_cpu_cores'] }} host</span>
                                @endif
                            </p>
                        </div>
                    @endif
                    @if($c['memory_limit_bytes'])
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Memory Limit</span>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($c['memory_limit_bytes'] / 1048576) }} MB
                                @if($c['host_memory_bytes'])
                                    <span class="text-xs font-normal text-gray-400 dark:text-gray-500">/ {{ number_format($c['host_memory_bytes'] / 1073741824, 1) }} GB host</span>
                                @endif
                            </p>
                        </div>
                    @endif
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">CPU Throttled</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($c['cpu_throttled_count'] ?? 0) }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">OOM Kills</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $c['oom_kill_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Uptime --}}
        @if(isset($systemMetrics['uptime']) && $systemMetrics['uptime'])
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                Uptime: {{ $systemMetrics['uptime']['human_readable'] }}
            </div>
        @endif
    </div>

    <script>
        (function() {
            const prefix = @json($prefix);
            const refreshInterval = 10000;

            async function refresh() {
                try {
                    const tokenMatch = window.location.search.match(/[?&]token=([^&]+)/);
                    const tokenParam = tokenMatch ? '?token=' + tokenMatch[1] : '';

                    const response = await fetch('/' + prefix + '/status' + tokenParam);
                    if (!response.ok) return;

                    const data = await response.json();
                    document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();

                    // Could enhance with full DOM update here
                } catch (e) {
                    // Silently fail, will retry on next interval
                }
            }

            setInterval(refresh, refreshInterval);
        })();
    </script>
</body>
</html>
