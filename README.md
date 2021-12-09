# Simple Server Monitor

Web-based charts of load averages without installing anything on the remote server
<!-- , uptimes, and memory usage -->

## Installation

Install PHP

```sh
sudo apt install php7.4-cli  # or php8.0-...
```
<!-- php7.4-sqlite -->
Add this line to monitoring server's crontab

```crontab
* * * * * /path/to/record.php
```

Add remote hosts to `hosts.php`
