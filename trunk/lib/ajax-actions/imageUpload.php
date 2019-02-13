<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !is_user_logged_in() ) {
    die( 'Login required!' );
}

if ( empty( $_FILES ) || empty( $_FILES['file'] ) ) {
    die( 'No uploaded files' );
}

$uploader = new \Admin\PostGalleryUploader();
$result = $uploader->handleUpload();

echo json_encode( $result );
die();