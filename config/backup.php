<?php

return [
    // Where .sql dumps are stored
    'path' => env('BACKUP_PATH', storage_path('app/backups')),

    // How many of the newest backups to keep (older ones are deleted after each backup)
    'keep' => (int) env('BACKUP_KEEP', 14),

    // Dump tools (only used for the matching driver). On Windows give the full path, e.g.
    // C:\Program Files\PostgreSQL\16\bin\pg_dump.exe
    'pg_dump'   => env('PG_DUMP_PATH', 'pg_dump'),
    'mysqldump' => env('MYSQL_DUMP_PATH', 'mysqldump'),

    // Seconds before a dump tool is aborted
    'timeout' => (int) env('BACKUP_TIMEOUT', 300),
];
