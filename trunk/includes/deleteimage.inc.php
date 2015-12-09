<?php
    if ( !defined ( 'ABSPATH' ) ) exit;

    if ( !is_user_logged_in () ) {
        die( 'Login required!' );
    }

    if ( empty( $_REQUEST[ 'path' ] ) ) {
        die( 'no path given' );
    }

    $uploads = wp_upload_dir();
    $uploadDir = $uploads[ 'basedir' ];
    $uploadUrl = $uploads[ 'baseurl' ];

    if ( !file_exists ( $uploadDir . '/gallery/' . $_REQUEST[ 'path' ] ) ) {
        die( 'file not exists or is folder' );
    }

    // If "path" is dir iterate through this dir and delete all files
    if ( is_dir ( $uploadDir . '/gallery/' . $_REQUEST[ 'path' ] ) ) {
        $dirname = $uploadDir . '/gallery/' . $_REQUEST[ 'path' ];
        $dirHandle = opendir ( $dirname );

        if ( !$dirHandle )
            return false;

        // Iterate through the directory and delete every single file in it.
        // Afterwards delete the directory itself.
        while ( $file = readdir ( $dirHandle ) ) {
            if ( $file != "." && $file != ".." ) {
                if ( !is_dir ( $dirname . "/" . $file ) )
                    $success = unlink ( $dirname . "/" . $file );
                else
                    delete_directory ( $dirname . '/' . $file );
            }
        }

        closedir ( $dirHandle );
        rmdir ( $dirname );
        return true;
    } else {
        // Deletes a single file
        $success = unlink ( $uploadDir . '/gallery/' . $_REQUEST[ 'path' ] );
    }

    echo ( intval ( $success ) );
