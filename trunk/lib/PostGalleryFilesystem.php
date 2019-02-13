<?php
/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */

namespace Lib;

class PostGalleryFilesystem {
    static $cachedFolders = [];

    /**
     * Returns the foldername for the gallery
     *
     * @param object $wpost
     * @return string
     */
    public static function getImageDir( $wpost ) {
        $postName = empty( $wpost->post_title ) ? 'undefined' : $wpost->post_title;
        $postId = $wpost->ID;

        $blockedPostTypes = [
            'revision',
            'attachment',
            'mgmlp_media_folder',
        ];

        if ( in_array( $wpost->post_type, $blockedPostTypes, true ) ) {
            return;
        }

        if ( isset( self::$cachedFolders[$postId] ) ) {
            return self::$cachedFolders[$postId];
        }

        $search = [ 'ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', '°', '+', '&amp;', '&', '€', 'ß', '–' ];
        $replace = [ 'ae', 'ue', 'oe', 'ae', 'ue', 'oe', '', '-', '-', '-', 'E', 'ss', '-' ];

        $postName = str_replace( $search, $replace, $postName );

        $uploads = wp_upload_dir();
        $oldImageDir = strtolower( str_replace( 'http://', '', esc_url( $postName ) ) );
        $newImageDir = strtolower(
            sanitize_file_name( str_replace( '&amp;', '-', $postName )
            )
        );

        $baseDir = $uploads['basedir'] . '/gallery/';

        if ( empty( $newImageDir ) ) {
            return false;
        }

        // for very old postgallery who used wrong dir
        self::renameDir( $baseDir . $oldImageDir, $baseDir . $newImageDir );

        // for old postgallery who dont uses post-id in folder
        $oldImageDir = $newImageDir;
        $newImageDir = $newImageDir . '_' . $postId;
        self::renameDir( $baseDir . $oldImageDir, $baseDir . $newImageDir );

        self::$cachedFolders[$postId] = $newImageDir;

        return $newImageDir;
    }

    /**
     * Rename a folder
     *
     * @param $oldDir
     * @param $newDir
     */
    public static function renameDir( $oldDir, $newDir ) {
        if ( $newDir == $oldDir ) {
            return;
        }
        if ( is_dir( $oldDir ) && !is_dir( $newDir ) ) {
            //rename($old_dir, $new_dir);
            if ( file_exists( $oldDir ) ) {
                $files = scandir( $oldDir );
                @mkdir( $newDir );
                @chmod( $newDir, octdec( '0777' ) );

                foreach ( $files as $file ) {
                    if ( !is_dir( $oldDir . '/' . $file ) ) {
                        copy( $oldDir . '/' . $file, $newDir . '/' . $file );
                        unlink( $oldDir . '/' . $file );
                    }
                }
                @rmdir( $oldDir );

                return $newDir;
            }
        }

        // fail
        return $oldDir;
    }
}