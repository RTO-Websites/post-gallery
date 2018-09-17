<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !is_user_logged_in() ) {
    die( 'Login required!' );
}

$path = filter_input( INPUT_GET, 'path' );
if ( empty( $path ) ) {
    $path = filter_input( INPUT_POST, 'path' );
}
if ( empty( $path ) ) {
    die( 'no path given' );
}

$uploads = wp_upload_dir();
$uploadDir = $uploads['basedir'];
$uploadUrl = $uploads['baseurl'];

if ( !file_exists( $uploadDir . '/gallery/' . $path ) ) {
    die( 'file not exists or is folder. ' . $uploadDir . '/gallery/' . $path );
}

$deletedFiles = [];

// If "path" is dir iterate through this dir and delete all files
if ( is_dir( $uploadDir . '/gallery/' . $path ) ) {
    $dirname = $uploadDir . '/gallery/' . $path;
    $dirHandle = opendir( $dirname );

    if ( !$dirHandle )
        return false;

    // Iterate through the directory and delete every single file in it.
    // Afterwards delete the directory itself.
    while ( $file = readdir( $dirHandle ) ) {
        if ( $file != "." && $file != ".." && !is_dir( $dirname . "/" . $file ) ) {
            $success = unlink( $dirname . "/" . $file );

            $attachmentId = \Inc\PostGallery::getAttachmentIdByUrl( $uploadUrl . '/gallery/' . $path . '/' . $file );
            if ( $attachmentId ) {
                wp_delete_attachment( $attachmentId );
            }

            $deletedFiles[] = $file;
        }
    }

    closedir( $dirHandle );
    rmdir( $dirname );
    return true;
} else {
    // Deletes a single file
    $success = unlink( $uploadDir . '/gallery/' . $path );
    $file = explode( '/', $path );
    $file = array_pop( $file );

    // delete attachment and thumbnails
    $attachmentId = \Inc\PostGallery::getAttachmentIdByUrl( $uploadUrl . '/gallery/' . $path );
    if ( $attachmentId ) {
        wp_delete_attachment( $attachmentId );
    }

    $deletedFiles[] = $file;
}


// delete from cache
$cacheDir = scandir( $uploadDir . '/cache/' );
foreach ( $deletedFiles as $file ) {
    $file = explode( '.', $file );
    $fileExtension = array_pop( $file );
    $file = implode( '.', $file );
    $length = strlen( $file );

    foreach ( $cacheDir as $cacheFile ) {
        if ( substr( $cacheFile, 0, $length ) == $file ) {
            unlink( $uploadDir . '/cache/' . $cacheFile );
        }
    }
}

echo( intval( $success ) );
