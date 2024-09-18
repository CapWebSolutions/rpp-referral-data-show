<?php
/**
 * BuddyBoss Custom Notification Class.
 */
 
defined( 'ABSPATH' ) || exit;
 
if ( ! class_exists( 'BP_Core_Notification_Abstract' ) ) {
    return;
}
 
/**
 * Set up the Custom notification class.
 */
class BP_Custom_Notification extends BP_Core_Notification_Abstract {
 
    /**
     * Instance of this class.
     *
     * @var object
     */
    private static $instance = null;
 
    /**
     * Get the instance of this class.
     *
     * @return null|BP_Custom_Notification|Controller|object
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }
 
    /**
     * Constructor method.
     */
    public function __construct() {
        $this->start();
    }
 
    /**
     * Initialize all methods inside it.
     *
     * @return mixed|void
     */
    public function load() {
 
        /**
         * Register Notification Group.
         *
         * @param string $group_key         Group key.
         * @param string $group_label       Group label.
         * @param string $group_admin_label Group admin label.
         * @param int    $priority          Priority of the group.
         */
        $this->register_notification_group(
            'custom',
            esc_html__( 'RPP Custom Notification Frontend', 'buddyboss' ), // For the frontend.
            esc_html__( 'RPP Custom Notifications Admin', 'buddyboss' ) // For the backend.
        );
 
        $this->register_custom_notification();
    }
 
    /**
     * Register notification for user mention.
     */
    public function register_custom_notification() {
        /**
         * Register Notification Type.
         *
         * @param string $notification_type        Notification Type key.
         * @param string $notification_label       Notification label.
         * @param string $notification_admin_label Notification admin label.
         * @param string $notification_group       Notification group.
         * @param bool   $default                  Default status for enabled/disabled.
         */
        $this->register_notification_type(
            'notification_custom',
            esc_html__( 'RPP Custom Notification title', 'buddyboss' ),
            esc_html__( 'RPP Custom Notification admin title', 'buddyboss' ),
            'custom'
        );
 
        /**
         * Add email schema.
         *
         * @param string $email_type        Type of email being sent.
         * @param array  $args              Email arguments.
         * @param string $notification_type Notification Type key.
         */
        $this->register_email_type(
            'custom-at-message',
            array(
                'email_title'         => __( 'email title', 'buddyboss' ),
                'email_content'       => __( 'email content', 'buddyboss' ),
                'email_plain_content' => __( 'email plain text content', 'buddyboss' ),
                'situation_label'     => __( 'Email situation title', 'buddyboss' ),
                'unsubscribe_text'    => __( 'You will no longer receive emails when custom notification performed.', 'buddyboss' ),
            ),
            'notification_custom'
        );
 
        /**
         * Register notification.
         *
         * @param string $component         Component name.
         * @param string $component_action  Component action.
         * @param string $notification_type Notification Type key.
         * @param string $icon_class        Notification Small Icon.
         */
        $this->register_notification(
            'custom',
            'custom_action',
            'rpp_notification_custom'
        );
 
        /**
         * Register Notification Filter.
         *
         * @param string $notification_label    Notification label.
         * @param array  $notification_types    Notification types.
         * @param int    $notification_position Notification position.
         */
        $this->register_notification_filter(
            __( 'RPP Custom Notification Filter', 'buddyboss' ),
            array( 'rpp_notification_custom' ),
            5
        );
    }
 
    /**
     * Format the notifications.
     *
     * @param string $content               Notification content.
     * @param int    $item_id               Notification item ID.
     * @param int    $secondary_item_id     Notification secondary item ID.
     * @param int    $action_item_count     Number of notifications with the same action.
     * @param string $component_action_name Canonical notification action.
     * @param string $component_name        Notification component ID.
     * @param int    $notification_id       Notification ID.
     * @param string $screen                Notification Screen type.
     *
     * @return array
     */
    public function format_notification( $content, $item_id, $secondary_item_id, $action_item_count, $component_action_name, $component_name, $notification_id, $screen ) {
 
        if ( 'custom' === $component_name && 'custom_action' === $component_action_name ) {
 
            $text = esc_html__( 'Custom Notification text.', 'buddyboss' );
            $link = get_permalink( $item_id );
     
            /**
             * Change the text for Push Notifications  
             */
            if($screen == "app_push" || $screen == "web_push") {
                $text = esc_html__( 'Custom Push Notification Text Only.', 'buddyboss' );
            }
     
            return array(
                'title' => "", // (optional) only for push notification & if not provided no title will be used.
                'text' => $text,
                'link' => $link,
            );
        }
 
        return $content;
    }
}