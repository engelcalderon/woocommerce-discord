<?php

class WC_Discord_Admin {

    public static function init() {
        add_action ( 'admin_menu', array( __CLASS__, 'admin_menu' ));
        add_action( 'admin_init',  array( __CLASS__, 'register_admin_settings' ));
    }

    public static function admin_menu() {
        add_menu_page ( __ ( 'Discord Bot', 'woocommerce-discord' ), __ ( 'Discord Bot', 'woocommerce-discord' ), 'administrator', 'wc_discord',
        array(__CLASS__, 'settings_page'), 'dashicons-admin-tools' );
    }

    public static function register_admin_settings() {
        register_setting( 'woocommerce-discord-settings', 'client_secret' );
        register_setting( 'woocommerce-discord-settings', 'client_id' );
        register_setting( 'woocommerce-discord-settings', 'bot_token' );
        register_setting( 'woocommerce-discord-settings', 'redirect_uri' );
        register_setting( 'woocommerce-discord-settings', 'auth_uri' );
        register_setting( 'woocommerce-discord-settings', 'guild_id' );
        register_setting( 'woocommerce-discord-settings', 'role_id' );
    }

    public static function settings_page() { ?>
        <div class="wrap">
            <h1>Discord Bot Settings</h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'woocommerce-discord-settings' ); ?>
                <?php do_settings_sections( 'woocommerce-discord-settings' ); ?>

                <table class="form-table">
                    <tr valign="top">
                    <th scope="row">Client Secret</th>
                    <td><input type="password" name="client_secret" value="<?php echo esc_attr( get_option('client_secret') ); ?>" /></td>
                    </tr>
                    
                    <tr valign="top">
                    <th scope="row">Client Id</th>
                    <td><input type="text" name="client_id" value="<?php echo esc_attr( get_option('client_id') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">Bot token</th>
                    <td><input type="password" name="bot_token" value="<?php echo esc_attr( get_option('bot_token') ); ?>" /></td>
                    </tr>
                    
                    <tr valign="top">
                    <th scope="row">Redirect URL</th>
                    <td><input type="text" name="redirect_uri" value="<?php echo esc_attr( get_option('redirect_uri') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">Discord Auth URL</th>
                    <td><input type="text" name="auth_uri" value="<?php echo esc_attr( get_option('auth_uri') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">Guild Id</th>
                    <td><input type="text" name="guild_id" value="<?php echo esc_attr( get_option('guild_id') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row">Role Id</th>
                    <td><input type="text" name="role_id" value="<?php echo esc_attr( get_option('role_id') ); ?>" /></td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        
        </div>
    <?php 
    }
}

?>