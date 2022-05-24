#! /bin/bash

cd /var/www/html/liddell-load/data

for i in 1 2 3 4; do
	scp liddell$i:/home/cr/load-average.tsv liddell$i.tsv
done
