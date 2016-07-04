<?php
/**
 * Global settings screen. (plugin options)
 */

$this->extend('layout');
?> 

    <form action="" method="post" enctype="application/x-www-form-urlencoded">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Compiling MO files','loco')?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php esc_html_e('Compiling MO files','loco')?></span>
                            </legend>
                            <p>
                                <label for="loco--gen-hash">
                                    <input type="checkbox" name="opts[gen_hash]" value="1" id="loco--gen-hash"<?php echo $opts->gen_hash?' checked':''?> />
                                    <?php esc_html_e('Generate hash tables','loco')?> 
                                </label>
                            </p>
                            <p>
                                <label for="loco--use-fuzzy">
                                    <input type="checkbox" name="opts[use_fuzzy]" value="1" id="loco--use-fuzzy"<?php echo $opts->use_fuzzy?' checked':''?> />
                                    <?php esc_html_e('Include Fuzzy strings','loco')?> 
                                </label>
                            </p>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Backing up PO files','loco')?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php esc_html_e('Backing up PO files','loco')?></span>
                            </legend>
                            <p>
                                <label for="loco--num-backups">
                                    <?php esc_html_e('Number of backups to keep of each file:','loco')?> 
                                </label>
                                <input type="number" min="0" max="99" size="2" name="opts[num_backups]" id="loco--num_backups" value="<?php printf('%u',$opts->num_backups)?>" />
                            </p>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Grant access to roles','loco')?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php esc_html_e('Allow full access to these roles','loco')?></span>
                            </legend><?php 
                            /* @var $cap Loco_mvc_ViewParams */
                            foreach( $caps as $cap ):?> 
                            <p>
                                <label>
                                    <input type="checkbox" name="<?php $cap->e('name')?>" value="<?php $cap->e('label')?>" <?php echo $cap->attrs?> />
                                    <?php $cap->e('label')?> 
                                </label>
                            </p><?php
                            endforeach;?> 
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php esc_html_e('Save settings','loco')?>" />
            <input type="hidden" name="<?php $nonce->e('name')?>" value="<?php $nonce->e('value')?>" />
        </p>
    </form>
