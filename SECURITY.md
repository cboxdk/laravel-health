# Security Policy

## Supported versions

Only the latest major version receives security fixes.

## Reporting a vulnerability

Please **do not** open a public issue. Email [sn@cbox.dk](mailto:sn@cbox.dk) with a
description of the problem and, if you can, a proof of concept. Fixes are handled on a
best-effort basis and credited in the release notes unless you prefer otherwise.

Areas of particular interest for this package:

- Endpoint access control: `EndpointAuth` (token and auth callback) and `AllowIps`
  (exact and CIDR allowlists).
- Information exposure through the status, JSON metrics, Prometheus and dashboard
  endpoints (hostnames, environment, system metrics, check error messages).
- Configuration handling that could turn a misconfiguration into an open endpoint.

See [docs/security](docs/security/_index.md) for how the endpoints are protected and what
each one exposes.
