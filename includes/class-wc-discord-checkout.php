<?php

class WC_Discord_Checkout {

    public static $currentUser = NULL;
    public static $token = NULL;

    public static function init() {

        // Add link discord button on checkout page
        add_action( 'woocommerce_after_order_notes' , array(__CLASS__, 'checkout_discord_fields') );

        add_action('woocommerce_checkout_process', array(__CLASS__, 'validate_fields_before_checkout'));

        // grant access to discord after subscription is created on chekout
        add_action('woocommerce_checkout_subscription_created', array(__CLASS__, 'process_checkout'));
    }

    public static function checkout_discord_fields() {
        if (!self::should_connect_discord_account()) return;

        // verify that we have set all needed settings before checkout
        $guild_id = get_option('guild_id');
        $role_id = get_option('role_id');
        $bot_token = get_option('bot_token');
        $authUri = get_option('auth_uri');
        $redirect_uri = get_option('redirect_uri');
        $client_secret = get_option('client_secret');
        $client_id = get_option('client_id');

        if ($guild_id == false || $role_id == false || $authUri == false || $client_secret == false || $client_id == false
        || $redirect_uri == false || $bot_token == false) {
            wc_add_notice( __( 'Please make sure all your settings are set in the Discord Bot tab on the dashboard panel'), 'error' );
            return;
        }

        ?>

        <script>

            if (!this.checkoutFields) {
                const checkoutFields = localStorage.getItem('_checkout_fields');
                if (checkoutFields) {
                    const jsonCheckoutFields = JSON.parse(checkoutFields);
                    Object.keys(jsonCheckoutFields).map((k, index) => {
                        jQuery(`#${k}`).val(jsonCheckoutFields[k]);
                    })
                    localStorage.removeItem('_checkout_fields');
                }
            }

            function fn_link_discord_button() {
                var authUri = "<?php echo $authUri; ?>";
                const billing_first_name = jQuery("#billing_first_name").val();
                const billing_last_name = jQuery("#billing_last_name").val();
                const billing_company = jQuery("#billing_company").val();
                const billing_address_1 = jQuery("#billing_address_1").val();
                const billing_address_2 = jQuery("#billing_address_2").val();
                const billing_city = jQuery("#billing_city").val();
                const billing_postcode = jQuery("#billing_postcode").val();
                const billing_country = jQuery("#billing_country").val();
                const billing_state = jQuery("#billing_state").val();
                const billing_email = jQuery("#billing_email").val();
                const billing_phone = jQuery("#billing_phone").val();

                localStorage.setItem('_checkout_fields', JSON.stringify({
                    billing_first_name,
                    billing_last_name,
                    billing_company,
                    billing_address_1,
                    billing_address_2,
                    billing_city,
                    billing_postcode,
                    billing_country,
                    billing_state,
                    billing_email,
                    billing_phone
                }));
                location.replace(authUri);
            }
        </script>


        <?php

        // clean up fields if they exist
        $_SESSION['discord_curret_user'] = NULL;
        $_SESSION['discord_user_token'] = NULL;

        echo '<div id="_link_discord_field_wrapper"><h3>'.__('Link Discord account', 'woocommerce-discord').'</h3>';

        echo '<label id="_link_discord_field_description" style="display:block;margin-bottom:5px;text-align:justify;">';
        echo __("In order to give you access to our private group now, 
        we need you to link your Discord account below. 
        If you don't have an account yet, you can create one below as well.", 'woocommerce-discord');
        echo '</label>';

        
        $_POST['billing']['billing_company']['default'] = 'Test';
        
        $discordAuthUser = new WC_Discord_UserAuth();
        $token = $discordAuthUser->try_getting_access_token();
        $currentUser = $discordAuthUser->try_getting_current_user($token);

        if ($token && $currentUser) {
            self::$currentUser = $currentUser;
            self::$token = $token;

            $_SESSION['discord_curret_user'] = $currentUser;
            $_SESSION['discord_user_token'] = $token;


            echo '<a id="_link_discord_button" href="'.$authUri.'"class="button">'.$currentUser->username.'</a>';
        } else {
            // echo '<a id="_link_discord_button" href="'.$authUri.'"class="button">'.__('Link account', 'woocommerce-discord').'</a>';
            echo '<a id="_link_discord_button" onclick="fn_link_discord_button()" class="button">'.__('Link account', 'woocommerce-discord').'</a>';
        }

        echo '</div>';
    }

    public static function validate_fields_before_checkout() {
        if (!self::should_connect_discord_account()) return;

        $currentUser = $_SESSION['discord_curret_user'];
        $token = $_SESSION['discord_user_token'];

        if (!$currentUser && !$token) {
            wc_add_notice( __( 'You need to connect a Discord account in order to continue'), 'error' );
        }
    }

    public static function process_checkout($order) {
        if (!self::should_connect_discord_account()) return;

        $user_id =$order->get_user_id();
        $currentUser = $_SESSION['discord_curret_user'];
        $token = $_SESSION['discord_user_token'];

        // TODO: add member to discord
        self::add_member_to_guild();
        // TODO: save member id on user meta
        $discord_meta_args = array(
            'member_id' => $currentUser->id,
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token
        );
        update_user_meta($user_id, '_discord_credentials', $discord_meta_args);

        // clean up fields
        $_SESSION['discord_curret_user'] = NULL;
        $_SESSION['discord_user_token'] = NULL;
    }

    private static function add_member_to_guild() {
        global $discord;

        $currentUser = $_SESSION['discord_curret_user'];
        $token = $_SESSION['discord_user_token'];

        // these values are saved in settings
        $guild_id = get_option('guild_id');
        $role_id = get_option('role_id');

        $currentMember = NULL;
        try {
            $currentMember = $discord->guild->getGuildMember(array(
                'guild.id' => (int)$guild_id,
                'user.id' => (int)$currentUser->id,
            ));
        } catch (Exception  $e) {
            // do nothing, we handle this next
        }

        try {
            if (!$currentMember) {
                $discord->guild->addGuildMember(array(
                    'guild.id' => (int)$guild_id,
                    'user.id' => (int)$currentUser->id,
                    'access_token' => $token->access_token,
                    'roles' => array((int)$role_id)
                ));
            } else {
                $discord->guild->addGuildMemberRole(array(
                    'guild.id' => (int)$guild_id,
                    'user.id' => (int)$currentUser->id,
                    'role.id' => (int)$role_id,
                ));
            }
        } catch (Exception  $e) {
            wc_add_notice( __( 'Error '. $e->getMessage() ), 'error' );
            // echo $e->getMessage();
        }
    }

    private static function should_connect_discord_account() {
        if (WC_Subscriptions_Cart::cart_contains_subscription()) {
            return true;
        }
        return false;
        // if (sizeof(WC()->cart->get_cart()) > 0) {

        //     foreach(WC()->cart->get_cart() as $cart_item_key => $values) {

        //         // $product = $values['data'];
        //         // $product_id = $product->get_type() == 'subscription_variation' 
        //         // ? $product->get_parent_id() : $product->get_id();
        //         // $use_discord = get_post_meta($product_id, '_use_discord', true);
        //         // if (isset($use_discord) && $use_discord == "yes") {
        //         //     return true;
        //         //     break;
        //         // }
        //         return true;
        //     }
        // }
        // return false;
    }

}


?>