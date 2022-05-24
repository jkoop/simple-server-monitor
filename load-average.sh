#! /bin/bash

# hostname
# datetime
# uptime
# users
# avg_1
# avg_5
# avg_15
# ram_total
# ram_used
# swap_total
# swap_used

((
hostname
date +%s
uptime | grep -o 'up .* user' | grep -o '[0-9].*,' | grep -o '[0-9].*[0-9]' | sed 's/  / /g'
uptime | grep -o '[0-9]* user' | grep -o '[0-9]*'
uptime | grep -o 'age: [^,]*,' | grep -o '[0-9.]*,$' | grep -o '[0-9.]*'
uptime | grep -o 'age: [^,]*, [^,]*,' | grep -o '[0-9.]*,$' | grep -o '[0-9.]*'
uptime | grep -o '[0-9.]*$' | grep -o '[0-9.]*'
free -b | grep -i 'mem' | grep -o '[0-9]*' | head -n 1
free -b | grep -i 'mem' | grep -o '[0-9]*' | head -n 2 | tail -n 1
free -b | grep -i 'swap' | grep -o '[0-9]*' | head -n 1
free -b | grep -i 'swap' | grep -o '[0-9]*' | head -n 2 | tail -n 1
) | sed -z 's/\n/\t/g' | sed 's/\t$//g'; echo) >> ~/load-average.tsv
