# Simple Server Monitor

```plain
!! README out of date !!
```

Web-based charts of load averages without installing anything on the remote server
<!-- , uptimes, and memory/disk usage -->

## How it works

It uses SSH to connect to the remote server and run a command to get the average load via `uptime` and saves the output to a file. It can then parse the file with regex, and displays the load averages in a graph.

Hostnames set in `hosts.php` are passed to SSH ass the hostname to connect to. You could define a user there (like `"user@hostname"`), but you should [set up `.ssh/config`](https://linuxize.com/post/using-the-ssh-config-file) instead.

SSH will assume you've already [set up your SSH key](https://linuxize.com/post/how-to-setup-passwordless-ssh-login), and will use that to connect to the remote server.

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
  + `db.sqlite`
    + recorder should be able to write to it
    + web server should be able to read from it
  + `hosts.php`
    + recorder and web server should be able to read from it
+ SSH
  + Try running `php /path/to/record.php` from terminal yourself, and read the output. It sometimes gives hints

## To do (in rough order of priority)

+ PHP send data more than 24 hours into the past (currently breaks at midnight UTC)
+ JS request only what's needed
+ PHP send data more efficiently (fewer data points than 1 per 2 pixels)
+ PHP offer averages wider than 15 minutes: 1h, 6h, 1d, 1w, 1m
+ Add uptime and memory/disk usage
<!--

load averages: cat /proc/loadavg
uptime: uptime -p
disk usage: df | grep -v '^tmpfs ' | grep -v '^/dev/loop' | grep -v '^udev '
memory usage: free

-->
