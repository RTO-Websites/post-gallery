<?php
    if ( !defined ( 'ABSPATH' ) ) exit;

    if ( !is_user_logged_in () ) {
        die( 'Login required!' );
    }

    if ( empty( $_REQUEST[ 'path' ] ) ) {
        die( 'no path given' );
    }

    $uploads = wp_upload_dir();
    $upload_dir = $uploads[ 'basedir' ];
    $upload_url = $uploads[ 'baseurl' ];

    if ( !file_exists ( $upload_dir . '/gallery/' . $_REQUEST[ 'path' ] ) ) {
        die( 'file not exists or is folder' );
    }

    // If "path" is dir iterate through this dir and delete all files
    if ( is_dir ( $upload_dir . '/gallery/' . $_REQUEST[ 'path' ] ) ) {
        $dirname = $upload_dir . '/gallery/' . $_REQUEST[ 'path' ];
        $dir_handle = opendir ( $dirname );

        if ( !$dir_handle )
            return false;

        // Iterate through the directory and delete every single file in it.
        // Afterwards delete the directory itself.
        while ( $file = readdir ( $dir_handle ) ) {
            if ( $file != "." && $file != ".." ) {
                if ( !is_dir ( $dirname . "/" . $file ) )
                    $success = unlink ( $dirname . "/" . $file );
                else
                    delete_directory ( $dirname . '/' . $file );
            }
        }

        closedir ( $dir_handle );
        rmdir ( $dirname );
        return true;
    } else {
        // Deletes a single file
        $success = unlink ( $upload_dir . '/gallery/' . $_REQUEST[ 'path' ] );
    }

    echo ( intval ( $success ) );
