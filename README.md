# Simple Server Monitor

Web-based charts of load averages without installing anything on the remote server
<!-- , uptimes, and memory usage -->

## Installation

```sh
# Install PHP (also PHP-enabled webserver)
sudo apt install php7.4-cli # or php8.0-cli

# Copy default hosts.php file
cp hosts.php.example hosts.php
```

Add this line to monitoring server's crontab

```crontab
* * * * * php /path/to/record.php
```

Add remote hosts to `hosts.php` with any text editor

## Troubleshooting

If the problem is a recording problem, check the following:

+ File permissions
  + `data/`
    + recorder should be able to write to it
    + web server should be able to read from it
  + `hosts.php`
    + recorder and web server should be able to read from it
+ SSH
  + Try running `php /path/to/record.php` from terminal yourself, and read the output. It sometimes gives hints
