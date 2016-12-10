#!/bin/bash

for f in /import.d/*.sql; do
    mysql -u"root" ${database.mysql.password.option} "${database.mysql.name}" < "$f"
done
