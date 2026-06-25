# Security Policy

## Supported versions

Only the latest release receives security fixes.

| Version | Supported |
|---------|-----------|
| 1.x     | ✅ Yes    |
| < 1.0   | ❌ No     |

## Reporting a vulnerability

**Do not open a public GitHub issue for security vulnerabilities.**

Email **contact@renderbit.com** with the subject line `[SECURITY] Indos-Checker-Laravel — <brief description>`. Please include:

- A description of the vulnerability and its potential impact
- The affected version(s)
- Steps to reproduce or a minimal proof-of-concept
- Any suggested remediation if you have one

You will receive an acknowledgement within **48 hours** and a resolution timeline within **7 days**. We will coordinate a disclosure date with you before publishing any fix or advisory.

## Scope

Areas most relevant to this package:

- **Response parsing** — logic in `DgShippingVerifier::parseResponse()` that determines validity from portal HTML; false positives or false negatives caused by crafted responses
- **HTTP handling** — improper handling of redirects, untrusted TLS certificates, or response data from the DG Shipping portal
- **Cache poisoning** — ability to inject malicious data into the verification cache
- **Exception leakage** — sensitive information exposed through exception messages

## Out of scope

- Vulnerabilities in the DG Shipping government portal itself (report those to DG Shipping directly)
- Issues in upstream dependencies (report to the relevant maintainer)
- Theoretical attacks with no practical exploit path
