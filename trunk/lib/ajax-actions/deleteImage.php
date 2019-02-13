<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !is_user_logged_in() ) {
    die( 'Login required!' );
}

$success = false;
$uploads = wp_upload_dir();
$uploadDir = $uploads['basedir'];
$uploadUrl = $uploads['baseurl'];

$postid = filter_input( INPUT_GET, 'postid' );
if ( empty( $postid ) ) {
    $postid = filter_input( INPUT_POST, 'postid' );
}

$attachmentId = filter_input( INPUT_GET, 'attachmentid' );
if ( empty( $attachmentId ) ) {
    $attachmentId = filter_input( INPUT_POST, 'attachmentid' );
}

$deletedFiles = [];

if ( empty( $attachmentId ) && !empty( $postid ) ) {
    // Delete all images from a post
    $images = \Lib\PostGallery::getImages( $postid );
    foreach ( $images as $image ) {
        $deletedFiles[] = \Admin\PostGalleryAdmin::deleteAttachment( $image['attachmentId'] );
    }
    $success = true;
} else if ( !empty( $attachmentId ) ) {
    // Deletes a single file
    $deletedFiles[] = \Admin\PostGalleryAdmin::deleteAttachment( $attachmentId );
    $success = true;
} else {
    die( 'No postid or attachmentid' );
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
