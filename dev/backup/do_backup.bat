del .\ancoragoh.backup
del .\ancoragoh.tar
del .\ancoragoh.custom
del .\ancoragoh.sql
C:\servidores\PostgreSQL\8.4\bin\pg_dump.exe -v -h localhost -p 5432 -U postgres -F c -f .\ancoragoh.custom ancoragoh
C:\servidores\PostgreSQL\8.4\bin\pg_dump.exe -v -h localhost -p 5432 -U postgres -F t -f .\ancoragoh.tar ancoragoh
C:\servidores\PostgreSQL\8.4\bin\pg_dump.exe -v -h localhost -p 5432 -U postgres -F p -f .\ancoragoh.sql ancoragoh

"C:\Program files\PostgreSQL\8.4\bin\pg_dump.exe" -v -h localhost -p 5432 -U postgres -F c -f .\ancoragoh.custom ancoragoh
"C:\Program files\PostgreSQL\8.4\bin\pg_dump.exe" -v -h localhost -p 5432 -U postgres -F t -f .\ancoragoh.tar ancoragoh
"C:\Program files\PostgreSQL\8.4\bin\pg_dump.exe" -v -h localhost -p 5432 -U postgres -F p -f .\ancoragoh.sql ancoragoh

D:\servidores\postgres\bin\pg_dump.exe -v -h localhost -p 5432 -U postgres -F c -f .\ancoragoh.custom ancoragoh
D:\servidores\postgres\bin\pg_dump.exe -v -h localhost -p 5432 -U postgres -F t -f .\ancoragoh.tar ancoragoh
D:\servidores\postgres\bin\pg_dump.exe -v -h localhost -p 5432 -U postgres -F p -f .\ancoragoh.sql ancoragoh
