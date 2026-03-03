---
title: JSON Metrics
description: JSON system metrics endpoint.
weight: 33
---

# JSON Metrics

The `/health/metrics/json` endpoint returns system metrics as structured JSON.

## Response Structure

Responses include a `hostname` field identifying which host served the request. In Kubernetes this is the pod name; on single-host platforms (Forge, Ploi) it's the server hostname.

```json
{
  "hostname": "my-app-7d9f8b6c4-xk2mn",
  "environment": {
    "os": "Linux",
    "os_version": "6.1.0",
    "kernel": "6.1.0-generic",
    "architecture": "x86_64",
    "containerized": true
  },
  "load": {
    "load_1m": 0.45,
    "load_5m": 0.38,
    "load_15m": 0.32,
    "core_count": 4
  },
  "memory": {
    "total_bytes": 8589934592,
    "used_bytes": 4294967296,
    "available_bytes": 4294967296,
    "used_percent": 50.0,
    "swap_total_bytes": 2147483648,
    "swap_used_bytes": 0,
    "source": "cgroup_v2"
  },
  "storage": [
    {
      "mountpoint": "/",
      "device": "/dev/sda1",
      "total_bytes": 107374182400,
      "used_bytes": 53687091200,
      "available_bytes": 53687091200,
      "used_percent": 50.0
    }
  ],
  "network": [
    {
      "name": "eth0",
      "is_up": true,
      "rx_bytes": 1073741824,
      "tx_bytes": 536870912,
      "rx_errors": 0,
      "tx_errors": 0
    }
  ],
  "uptime": {
    "total_seconds": 86400,
    "human_readable": "1 day"
  },
  "container": {
    "cgroup_version": "v2",
    "cpu_quota": 2.0,
    "memory_limit_bytes": 4294967296,
    "cpu_usage_cores": 0.5,
    "memory_usage_bytes": 2147483648,
    "cpu_throttled_count": 0,
    "oom_kill_count": 0,
    "host_cpu_cores": 8,
    "host_memory_bytes": 17179869184
  }
}
```

The `hostname` field identifies the responding host — useful in horizontally scaled deployments where a load balancer routes to any instance. The `memory.source` field indicates whether metrics come from `host`, `cgroup_v1`, or `cgroup_v2`. The `container` section only appears when running inside a container.

## Disabling Metric Groups

Toggle groups in `config/health.php`:

```php
'metrics' => [
    'system' => [
        'cpu'     => true,
        'memory'  => true,
        'load'    => false,
        'storage' => false,
        'network' => false,
    ],
],
```

## Related Documentation

- [Endpoints Overview](_index.md)
- [Prometheus Metrics](prometheus-metrics.md)
- [System Metrics Integration](../advanced/system-metrics-integration.md)
