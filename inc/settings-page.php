<?php
class Qsoocp_Settings_Page {

    private $options;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    public function add_plugin_page() {
        add_options_page(
            esc_attr__( 'Settings Admin', 'qsoocp' ),
            esc_attr__( 'QSOOCP Setting', 'qsoocp' ), 
            'manage_options', 
            'qsoocp-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    public function create_admin_page() {
        $this->options = get_option( 'qsoocp_option_data' );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Quick Search And Only One Category Posting Settings', 'qsoocp' ); ?></h1>
            <form method="post" action="options.php">
            <?php
                settings_fields( 'qsoocp_option_group' );
                do_settings_sections( 'qsoocp-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {        
        register_setting(
            'qsoocp_option_group',
            'qsoocp_option_data',
            array( $this, 'sanitize' )
        );

        add_settings_section(
            'qsoocp_setting_section_id',
            '',
            '',
            'qsoocp-setting-admin'
        );  

        add_settings_field(
            'qsoocp_setting_value',
            esc_attr__( 'Chose Post Type', 'qsoocp' ),
            array( $this, 'qsoocp_settings_callback' ),
            'qsoocp-setting-admin',
            'qsoocp_setting_section_id'       
        );     
    }

    public function sanitize( $input ) {
        foreach ( $input as $key => $value ) {
            if ( !isset( $value['posttype'] ) || !isset( $value['taxonomy'] ) || !isset( $value['radio'] ) ) {
                unset( $input[ $key ] );
            }
        }
        return $input;
    }

    public function qsoocp_settings_callback() {
        $defaults = array(
            array(
                'posttype' => 'post',
                'taxonomy' => 'category',
                'radio'    => 'disable'
            )
        );

        $posttypes  = qsoocp_get_data_setting();
        $taxonomies = qsoocp_get_taxonomies();
        
        if ( $this->options ) {
            $args = $this->options;
        } else {
            $args = $defaults;
        }

        ?>
        <div class="repeater">
            <div data-repeater-list="qsoocp_option_data">
                <?php
                    foreach ( $args as $data ) : 
                ?>
                    <div class="qsccp-item" data-repeater-item>

                        <label><?php esc_html_e( 'Select Post Type', 'qsoocp' ); ?></label>
                        <select name="qsoocp_option_data[0][posttype]">
                            <?php foreach ( $posttypes as $value ) : ?>
                                <option value="<?php echo esc_html( $value->name ); ?>" <?php selected( $value->name, $data['posttype'] ); ?>><?php echo esc_html( $value->label ); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label><?php esc_html_e( 'Select Taxonomy', 'qsoocp' ); ?></label>
                        <select name="qsoocp_option_data[0][taxonomy]">
                            <?php foreach ( $taxonomies as $value ) : ?>
                                <option value="<?php echo esc_html( $value ); ?>" <?php selected( $value, $data['taxonomy'] ); ?>><?php echo qsoocp_get_taxonomy_name( $value ); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label><?php esc_html_e( 'Change Checkbox to Radio Button', 'qsoocp' ); ?></label>
                        <select name="qsoocp_option_data[0][radio]">
                            <option value="enable" <?php selected( 'enable', $data['radio'] ); ?>><?php esc_html_e( 'Enable', 'qsoocp' ); ?></option>
                            <option value="disable" <?php selected( 'disable', $data['radio'] ); ?>><?php esc_html_e( 'Disable', 'qsoocp' ); ?></option>
                        </select>

                        <input data-repeater-delete type="button" value="<?php esc_attr_e( 'Delete', 'qsoocp' ); ?>" class="button qsccp-button-del"/>
                    </div>
                <?php endforeach; ?>
            </div>
            <input data-repeater-create type="button" value="<?php esc_attr_e( 'Add', 'qsoocp' ); ?>" class="button button-primary"/>
        </div>

        <?php
    }

}

if ( is_admin() )
    $my_settings_page = new Qsoocp_Settings_Page();