<?php 

function qsoocp_get_data_setting() {
    $posttypes = get_post_types('', 'object');
    
    unset( $posttypes['page'] );
    unset( $posttypes['attachment'] );
    unset( $posttypes['revision'] );
    unset( $posttypes['nav_menu_item'] );
    unset( $posttypes['custom_css'] );
    unset( $posttypes['customize_changeset'] );

    return $posttypes; 
}

function qsoocp_get_taxonomies() {
    $taxonomies = get_taxonomies();

    unset( $taxonomies['post_tag'] );
    unset( $taxonomies['nav_menu'] );
    unset( $taxonomies['link_category'] );
    unset( $taxonomies['post_format'] );

    return $taxonomies;
}

function qsoocp_get_taxonomy_name( $taxonomy ) {
    $object = get_taxonomy( $taxonomy );
    return $object->label;
} 

function qsoocp_update_term_box() {
    $defaults = array(
        array(
            'posttype' => 'post',
            'taxonomy' => 'category',
            'radio'    => 'disable'
        )
    );

    $args = get_option( 'qsoocp_option_data' );

    if ( $args ) {
        $args = $args;
    } else {
        $args = $defaults;
    }

	foreach ( $args as $value ) {
		$term = get_taxonomy( $value['taxonomy'] );
        remove_meta_box( $value['taxonomy'].'div', $value['posttype'], 'side');
		add_meta_box( 'hql'.$value['taxonomy'].'div', $term->label, 'qsoocp_update_taxonomy_meta_box', $value['posttype'], 'side', 'core', array( 'taxonomy' => $value['taxonomy'], 'radio' => $value['radio'] ));
	}
}

function qsoocp_update_taxonomy_meta_box( $post, $box ) {
    $defaults = array( 'taxonomy' => 'category' );
    if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
        $args = array();
    } else {
        $args = $box['args'];
    }
    $r = wp_parse_args( $args, $defaults );
    $tax_name = esc_attr( $r['taxonomy'] );
    $taxonomy = get_taxonomy( $r['taxonomy'] );
    ?>
    <input id="tax_keyword" data-taxonomy="<?php echo esc_attr( $r['taxonomy'] ); ?>" type="text" value="" class="hql-tax-keyword" placeholder="<?php esc_attr_e( 'Quick Seach', 'qsoocp'); ?>"/>
    <div id="taxonomy-<?php echo esc_attr( $tax_name ); ?>" class="categorydiv">
        <ul id="<?php echo esc_attr( $tax_name ); ?>-tabs" class="category-tabs">
            <li class="tabs"><a href="#<?php echo esc_attr( $tax_name ); ?>-all"><?php echo esc_html( $taxonomy->labels->all_items ); ?></a></li>
            <li class="hide-if-no-js"><a href="#<?php echo esc_attr( $tax_name ); ?>-pop"><?php _e( 'Most Used' ); ?></a></li>
        </ul>

        <div id="<?php echo esc_attr( $tax_name ); ?>-pop" class="tabs-panel" style="display: none;">
            <ul id="<?php echo esc_attr( $tax_name ); ?>checklist-pop" class="categorychecklist form-no-clear" >
                <?php 
                    if ( $r['radio'] == 'enable' ) {
                        $popular_ids = qsoocp_popular_terms_checklist( $tax_name ); 
                    } else {
                        $popular_ids = wp_popular_terms_checklist( $tax_name ); 
                    }
                ?>
            </ul>
        </div>

        <div id="<?php echo esc_attr( $tax_name ); ?>-all" class="tabs-panel">
            <?php
            $name = ( $tax_name == 'category' ) ? 'post_category' : 'tax_input[' . $tax_name . ']';
            echo "<input type='hidden' name='{$name}[]' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
            ?>
            <ul id="<?php echo esc_attr( $tax_name ); ?>checklist" data-wp-lists="list:<?php echo esc_attr( $tax_name ); ?>" class="categorychecklist form-no-clear">
                <?php 
                    if ( $r['radio'] == 'enable' ) {
                        wp_terms_checklist( $post->ID, array( 'taxonomy' => $tax_name, 'walker' => new Qsccp_Walker_Category_Checklist, 'popular_cats' => $popular_ids ) );
                    } else {
                        wp_terms_checklist( $post->ID, array( 'taxonomy' => $tax_name, 'popular_cats' => $popular_ids ) );
                    } 
                ?>
            </ul>
        </div>
        <?php if ( current_user_can( $taxonomy->cap->edit_terms ) && $r['radio'] !== 'enable') : ?>
            <div id="<?php echo esc_attr( $tax_name ); ?>-adder" class="wp-hidden-children">
                <a id="<?php echo esc_attr( $tax_name ); ?>-add-toggle" href="#<?php echo esc_attr( $tax_name ); ?>-add" class="hide-if-no-js taxonomy-add-new">
                    <?php
                        /* translators: %s: add new taxonomy label */
                        printf( __( '+ %s' ), esc_html( $taxonomy->labels->add_new_item ) );
                    ?>
                </a>
                <p id="<?php echo esc_attr( $tax_name ); ?>-add" class="category-add wp-hidden-child">
                    <label class="screen-reader-text" for="new<?php echo esc_attr( $tax_name ); ?>"><?php echo esc_html( $taxonomy->labels->add_new_item ); ?></label>
                    <input type="text" name="new<?php echo esc_attr( $tax_name ); ?>" id="new<?php echo esc_attr( $tax_name ); ?>" class="form-required form-input-tip" value="<?php echo esc_attr( $taxonomy->labels->new_item_name ); ?>" aria-required="true"/>
                    <label class="screen-reader-text" for="new<?php echo esc_attr( $tax_name ); ?>_parent">
                        <?php echo esc_html( $taxonomy->labels->parent_item_colon ); ?>
                    </label>
                    <?php
                    $parent_dropdown_args = array(
                        'taxonomy'         => $tax_name,
                        'hide_empty'       => 0,
                        'name'             => 'new' . $tax_name . '_parent',
                        'orderby'          => 'name',
                        'hierarchical'     => 1,
                        'show_option_none' => '&mdash; ' . $taxonomy->labels->parent_item . ' &mdash;',
                    );

                    /**
                     * Filters the arguments for the taxonomy parent dropdown on the Post Edit page.
                     *
                     * @since 4.4.0
                     *
                     * @param array $parent_dropdown_args {
                     *     Optional. Array of arguments to generate parent dropdown.
                     *
                     *     @type string   $taxonomy         Name of the taxonomy to retrieve.
                     *     @type bool     $hide_if_empty    True to skip generating markup if no
                     *                                      categories are found. Default 0.
                     *     @type string   $name             Value for the 'name' attribute
                     *                                      of the select element.
                     *                                      Default "new{$tax_name}_parent".
                     *     @type string   $orderby          Which column to use for ordering
                     *                                      terms. Default 'name'.
                     *     @type bool|int $hierarchical     Whether to traverse the taxonomy
                     *                                      hierarchy. Default 1.
                     *     @type string   $show_option_none Text to display for the "none" option.
                     *                                      Default "&mdash; {$parent} &mdash;",
                     *                                      where `$parent` is 'parent_item'
                     *                                      taxonomy label.
                     * }
                     */
                    $parent_dropdown_args = apply_filters( 'post_edit_category_parent_dropdown_args', $parent_dropdown_args );

                    wp_dropdown_categories( $parent_dropdown_args );
                    ?>
                    <input type="button" id="<?php echo esc_attr( $tax_name ); ?>-add-submit" data-wp-lists="add:<?php echo esc_attr( $tax_name ); ?>checklist:<?php echo esc_attr( $tax_name ); ?>-add" class="button category-add-submit" value="<?php echo esc_attr( $taxonomy->labels->add_new_item ); ?>" />
                    <?php wp_nonce_field( 'add-' . $tax_name, '_ajax_nonce-add-' . $tax_name, false ); ?>
                    <span id="<?php echo esc_attr( $tax_name ); ?>-ajax-response"></span>
                </p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function qsoocp_popular_terms_checklist( $taxonomy, $default = 0, $number = 10, $echo = true ) {
    $post = get_post();
 
    if ( $post && $post->ID )
        $checked_terms = wp_get_object_terms($post->ID, $taxonomy, array('fields'=>'ids'));
    else
        $checked_terms = array();
 
    $terms = get_terms( $taxonomy, array( 'orderby' => 'count', 'order' => 'DESC', 'number' => $number, 'hierarchical' => false ) );
 
    $tax = get_taxonomy($taxonomy);
 
    $popular_ids = array();
    foreach ( (array) $terms as $term ) {
        $popular_ids[] = $term->term_id;
        if ( !$echo ) // Hack for Ajax use.
            continue;
        $id = "popular-$taxonomy-$term->term_id";
        $checked = in_array( $term->term_id, $checked_terms ) ? 'checked="checked"' : '';
        ?>
 
        <li id="<?php echo esc_attr( $id ); ?>" class="popular-category">
            <label class="selectit">
                <input id="in-<?php echo esc_attr( $id ); ?>" type="radio" <?php echo esc_attr( $checked ); ?> value="<?php echo (int) $term->term_id; ?>" <?php disabled( ! current_user_can( $tax->cap->assign_terms ) ); ?> />
                <?php
                /** This filter is documented in wp-includes/category-template.php */
                echo esc_html( apply_filters( 'the_category', $term->name ) );
                ?>
            </label>
        </li>
 
        <?php
    }
    return $popular_ids;
}