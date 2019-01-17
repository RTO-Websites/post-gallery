<div class="multi-rename">
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label class="theme_options_label"><?php _e( 'Multi-Rename', 'post-gallery' ); ?></label>
            </th>
            <td>
                <input type="text"
                        value="image-"
                        name="postgallery-multireplace-prefix"
                        class="postgallery-multireplace-prefix"/>
                <input class="button"
                        type="button"
                        onclick="multiRename();"
                        value="<?php _e( 'Rename', 'post-gallery' ); ?>"/>
            </td>
        </tr>
    </table>
</div>