#!/bin/sh
rm ./ancoragoh.backup
rm ./ancoragoh.tar
rm ./ancoragoh.custom
rm ./ancoragoh.sql
pg_dump -v -h localhost -p 5432 -U ancoragoh -F c -f ./ancoragoh.custom ancoragoh
pg_dump -v -h localhost -p 5432 -U ancoragoh -F t -f ./ancoragoh.tar ancoragoh
pg_dump -v -h localhost -p 5432 -U ancoragoh -F p -f ./ancoragoh.sql ancoragoh

