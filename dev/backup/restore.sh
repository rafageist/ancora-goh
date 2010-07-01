#!/bin/sh
pg_restore -v -h localhost -p 5432 -U ancoragoh -d ancoragoh ./ancoragoh.custom
