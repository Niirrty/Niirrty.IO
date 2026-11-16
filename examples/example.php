<?php

include dirname( __DIR__ ) . '/vendor/autoload.php';

use Niirrty\IO\File;

try
{
    echo 'First 5 bytes are: '. File::ReadFirstBytes( __FILE__, 5 );
}
catch ( \Throwable $ex ) { echo $ex; }