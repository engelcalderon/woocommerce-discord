<?php

class WC_Discord {

    public static function init() {
        // add_action('woocommerce_subscription_payment_failed', array(__CLASS__, 'on_subscription_payment_failed'));

        // add_action('woocommerce_subscription_status_pending-cancel_to_cancelled', array(__CLASS__, 'on_subscription_payment_failed'));

        // we need to listen to all updates, even if the admin decides to cancel a subscription manually
        // for now we only support to quick members from here, the only way to get in is from the checkout
        add_action('woocommerce_subscription_status_updated', array(__CLASS__, 'on_subscription_status_changed'));
    }

    public static function on_subscription_status_changed($subscription) {

        $is_cancelled = $subscription->has_status('cancelled');
        $is_expired = $subscription->has_status('expired');

        if ($is_cancelled || $is_expired) {
            $user_id = $subscription->get_user_id();
            $discord_credentials = get_user_meta($user_id, '_discord_credentials', true);
            $member_id = $discord_credentials['member_id'];
            self::quick_discord_member($member_id);
        }
    }

    public static function quick_discord_member($memberId) {
        global $discord;

        // these values are saved in settings
        $guild_id = get_option('guild_id');
        
        try {
            $args = array(
                'guild.id' => (int)$guild_id,
                'user.id' => (int)$memberId
            );
            $discord->guild->removeGuildMember($args);
        } catch (Exception  $e) {
            // echo $e->getMessage();
        }
    }
}

?>