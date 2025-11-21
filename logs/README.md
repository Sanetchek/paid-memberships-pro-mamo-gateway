# MAMO Logs Directory

This directory contains MAMO payment gateway logs.

## Log Files

Logs are created daily with the format: `mamo-YYYY-MM-DD.log`

Example:
- `mamo-2025-10-21.log`
- `mamo-2025-10-22.log`

## How to Enable

1. Go to **WordPress Admin → Memberships → Settings → Payment Gateway**
2. Select **MAMO** gateway
3. Set **Enable debug log** to **Yes**
4. Click **Save Settings**

## Log Location

Logs are written to:
1. **Primary:** `logs/mamo-YYYY-MM-DD.log` (this directory)
2. **Backup:** WordPress `wp-content/debug.log` (if `WP_DEBUG_LOG` is enabled)

## Security

- Log files automatically redact sensitive data (passwords, tokens, API keys, card numbers)
- This directory should NOT be publicly accessible
- Add appropriate `.htaccess` rules if needed

## Monitoring

Use the provided PowerShell script to watch logs in real-time:
```powershell
.\watch-logs.ps1
```

## Rotation

Logs are automatically rotated daily. Old logs can be safely deleted manually.

Consider setting up a cron job to clean old logs:
```bash
# Delete logs older than 30 days
find /path/to/logs/ -name "mamo-*.log" -mtime +30 -delete
```

