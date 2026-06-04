<?php
// جلوگیری از دسترسی مستقیم
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// اضافه کردن درگاه کیف پول به ووکامرس
add_filter( 'woocommerce_payment_gateways', 'neo_add_wallet_gateway_class' );
function neo_add_wallet_gateway_class( $gateways ) {
    $gateways[] = 'WC_Gateway_Neo_Wallet';
    return $gateways;
}

// کلاس اختصاصی درگاه پرداخت
add_action( 'plugins_loaded', 'neo_init_wallet_gateway_class' );
function neo_init_wallet_gateway_class() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

    class WC_Gateway_Neo_Wallet extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = 'neo_wallet';
            $this->has_fields         = false;
            $this->method_title       = 'کیف پول نئو';
            $this->method_description = 'پرداخت سریع با موجودی کیف پول سایت.';

            $this->init_form_fields();
            $this->init_settings();

            $this->title       = $this->get_option( 'title', 'پرداخت با کیف پول' );
            $this->description = $this->get_option( 'description', 'هزینه این سفارش از کیف پول شما کسر خواهد شد.' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function is_available() {
            if ( ! is_user_logged_in() ) return false;
            return parent::is_available();
        }

        public function get_title() {
            $balance = neo_get_wallet_balance( get_current_user_id() );
            // ظاهر ساده و شیک برای نمایش موجودی در برگه تسویه حساب
            $badge = '<span style="display:inline-block; font-size:12px; font-weight:normal; color:#fff; background:#4f46e5; padding:2px 8px; border-radius:4px; margin-right:8px;">موجودی: ' . wc_price( $balance ) . '</span>';
            return $this->title . ' ' . $badge;
        }

        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            $user_id = $order->get_user_id();
            $total = $order->get_total();
            $balance = neo_get_wallet_balance( $user_id );

            if ( $balance < $total ) {
                wc_add_notice( 'موجودی کیف پول شما کافی نیست. لطفا حساب خود را شارژ کنید.', 'error' );
                return;
            }

            // کسر مبلغ با فراخوانی تابعی که در wallet-core.php نوشتیم
            neo_wallet_transaction( $user_id, $total, 'debit', 'پرداخت سفارش #' . $order_id );

            $order->payment_complete();
            $order->add_order_note( 'سفارش با موفقیت از طریق کیف پول پرداخت شد.' );

            WC()->cart->empty_cart();
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order )
            );
        }
    }
}