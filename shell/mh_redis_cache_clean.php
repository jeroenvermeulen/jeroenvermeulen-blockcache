#!/usr/bin/env php
<?php
$mageRoot = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
/** @noinspection PhpIncludeInspection */
require $mageRoot . '/lib/Credis/Client.php';

$client = Redis_Connect( $mageRoot . '/app/etc/local.xml' );
$client->flushdb();
exit;

function Redis_Connect( $m1xmlFile )
{
    if ( file_exists($m1xmlFile) ) {
        if ( !is_readable( $m1xmlFile ) )
        {
            throw new Exception( sprintf('File "%s" does not exits or is not readable.', $m1xmlFile ) );
        }

        $xml  = simplexml_load_file( $m1xmlFile, 'SimpleXMLElement', LIBXML_NOCDATA );
        /** @noinspection PhpUndefinedFieldInspection */
        $host = strval( $xml->global->cache->backend_options->server );
        /** @noinspection PhpUndefinedFieldInspection */
        $port = strval( $xml->global->cache->backend_options->port );
        /** @noinspection PhpUndefinedFieldInspection */
        $db   = strval( $xml->global->cache->backend_options->database );
        if ( empty( $host ) )
        {
            throw new Exception( sprintf('Redis server hostname is not found in "%s".', $m1xmlFile ) );
        }
        if ( is_null( $port ) || ""===$port ) // port can be 0 when using socket
        {
            throw new Exception( sprintf('Redis server port is not found in "%s".', $m1xmlFile ) );
        }
        if ( !strlen( $db ) )
        {
            throw new Exception( sprintf('Redis database number is not found in "%s".', $m1xmlFile ) );
        }
    }

    if ( '/' == substr($host,0,1) || 0 === $port ) {
        // Socket
        $server = $host;
    }
    else {
        // TCP
        $server = sprintf( 'tcp://%s:%d', $host, $port );
    }

    if ( !isset($db) || is_null($db) || "" === $db || empty($server) ) {
        throw new Exception( 'Could not find Redis configuration' );
    }

    $client = new \Credis_Client( $server );
    $client->select( $db );

    return $client;
}
