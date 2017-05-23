<?php

/* Folders that will be written to later */
define( 'IPS_FOLDER_PERMISSION', 0777 );

/* Folders that will be created and not written to later */
define( 'FOLDER_PERMISSION_NO_WRITE', 0755 );

/* Files that will be written, and then later deleted or overwritten */
define( 'IPS_FILE_PERMISSION', 0666 );

/* Files that will be written once, and would not later be updated or deleted */
define( 'FILE_PERMISSION_NO_WRITE', 0644 );