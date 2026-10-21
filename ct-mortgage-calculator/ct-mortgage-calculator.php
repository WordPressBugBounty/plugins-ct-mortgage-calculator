<?php
/*
Plugin Name: Simple Mortgage Calculator
Plugin URI: https://www.calculator.io/mortgage-calculator/
Description: A simple mortgage calculator widget
Version: 1.4.2
Author: Mortgage Calculator
Author URI: https://www.calculator.io/mortgage-calculator/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ct-mortgage-calculator
*/

defined( 'ABSPATH' ) || exit;

define( 'CT_MORTGAGE_CALCULATOR_VERSION', '1.4.2' );

function ct_mortgage_calculator_css() {
    wp_enqueue_style( 'ct_mortgage_calc', plugins_url( 'assets/style.css', __FILE__ ), array(), CT_MORTGAGE_CALCULATOR_VERSION );
}
add_action( 'wp_enqueue_scripts', 'ct_mortgage_calculator_css' );

function ct_mortgage_calculator_scripts() {
    wp_enqueue_script( 'calc', plugins_url( 'assets/calc.js', __FILE__ ), array('jquery'), CT_MORTGAGE_CALCULATOR_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'ct_mortgage_calculator_scripts' );

/*-----------------------------------------------------------------------------------*/
/* Register Widget */
/*-----------------------------------------------------------------------------------*/

class CT_Mortgage_Calculator_Widget extends WP_Widget {

    public function __construct() {
        $widget_ops = array('description' => __( 'Display a mortgage calculator.', 'ct-mortgage-calculator' ) );
        // The id base is fixed to the one derived from the old class name, so saved widgets keep working.
        parent::__construct('ct_mortgagecalculator', __('Simple Mortgage Calculator', 'ct-mortgage-calculator'), $widget_ops);
    }

    public function widget($args, $instance) {
        $title = isset( $instance['title'] ) ? $instance['title'] : '';
        $currency = isset( $instance['currency'] ) ? $instance['currency'] : '$';

        // The wrappers come from the theme's sidebar registration (the same trust model as core widgets).
        echo isset( $args['before_widget'] ) ? $args['before_widget'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ($title) echo ( isset( $args['before_title'] ) ? $args['before_title'] : '' ) . esc_html( $title ) . ( isset( $args['after_title'] ) ? $args['after_title'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        ?>

        <div class="widget-inner"><form id="loanCalc"><fieldset><input type="text" name="mcPrice" id="mcPrice" class="text-input" placeholder="<?php echo esc_attr( __( 'Sale price (no separators)', 'ct-mortgage-calculator' ) . ' (' . $currency . ')' ); ?>" /><label for='mcPrice' style='display:none'>Home Price</label><input type="text" name="mcRate" id="mcRate" class="text-input" placeholder="<?php esc_attr_e('Interest Rate (%)', 'ct-mortgage-calculator'); ?>"/><label for='mcRate' style='display:none'>Interest Rate</label><input type="text" name="mcTerm" id="mcTerm" class="text-input" placeholder="<?php esc_attr_e('Term (years)', 'ct-mortgage-calculator'); ?>" /><label for='mcTerm' style='display:none'>Mortgage Term in Years</label><input type="text" name="mcDown" id="mcDown" class="text-input" placeholder="<?php echo esc_attr( __( 'Down payment (no separators)', 'ct-mortgage-calculator' ) . ' (' . $currency . ')' ); ?>" /><label for='mcDown' style='display:none'>Down Payment</label><input class="btn marB10" type="submit" id="mortgageCalc" value="<?php esc_attr_e('Calculate', 'ct-mortgage-calculator'); ?>" onclick="return false"><?php ct_mortgage_calculator_get_a(); ?><label style='display:none' for='mortgageCalc'>Submit</label><p class="muted monthly-payment" style="display: none"><?php esc_html_e('Monthly Payment:', 'ct-mortgage-calculator'); ?> <strong><?php echo esc_html( $currency ); ?><span id="mcPayment" style="display: none"></span> <span style='font-size:8px;line-height:1em;vertical-align:top'></span></span></strong></p></fieldset></form></div>

        <?php
        echo isset( $args['after_widget'] ) ? $args['after_widget'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['currency'] = isset( $new_instance['currency'] ) ? sanitize_text_field( $new_instance['currency'] ) : '$';
        return $instance;
    }

    public function form($instance) {

        $title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Mortgage Calculator';
        $currency = isset( $instance['currency'] ) ? esc_attr( $instance['currency'] ) : '$';

        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:','ct-mortgage-calculator'); ?></label>
            <input type="text" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>"  value="<?php echo esc_attr( $title ); ?>" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" />
            <label for="<?php echo esc_attr( $this->get_field_id('currency') ); ?>"><?php esc_html_e('Currency:','ct-mortgage-calculator'); ?></label>
            <input type="text" name="<?php echo esc_attr( $this->get_field_name('currency') ); ?>"  value="<?php echo esc_attr( $currency ); ?>" class="widefat" id="<?php echo esc_attr( $this->get_field_id('currency') ); ?>" />
        </p>
        <?php
    }
}

function ct_mortgage_calculator_get_a(){
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    $options = get_option("ct_mortgage_calculator_options");
    if (!$options){
        $paths = [
            'en' => [ 'mortgage-calculator', 'mortgage-payment-calculator', 'home-loan-calculator', ],
            'es' => [ 'calculadora-de-hipotecas', 'calculadora-de-pagos-de-hipotecas', 'calculadora-de-préstamo-hipotecario', ],
            'fr' => [ 'calculateur-d-hypothèque', 'calculateur-de-remboursement-d-hypothèque', 'calculateur-de-prêt-immobilier', ],
            'de' => [ 'hypotheken-rechner', 'hypothekentilgungsrechner', 'rechner-für-wohnungskredite', ],
            'pt' => [ 'calculadora-de-hipoteca', 'calculadora-de-pagamento-de-hipoteca', 'calculadora-de-empréstimo-imobiliário', ],
            'it' => [ 'calcolatore-mutuo', 'calcolatore-della-rata-del-mutuo', 'calcolatore-del-mutuo-per-la-casa', ],
            'hi' => [ 'बंधक-मॉर्गिज-कैलकुलेटर', 'बंधक-मॉर्गेज-भुगतान-कैलकुलेटर', 'होम-लोन-कैलकुलेटर', ],
            'id' => [ 'kalkulator-hipotek', 'kalkulator-pembayaran-hipotek', 'kalkulator-pinjaman-rumah', ],
            'ar' => [ 'حاسبة-القروض-العقارية', 'حاسبة-سداد-الرهن-العقاري', 'حاسبة-قرض-المنزل', ],
            'ru' => [ 'ипотечный-калькулятор', 'калькулятор-погашения-ипотеки', 'калькулятор-жилищного-кредита', ],
            'ja' => [ '住宅ローン計算機', '住宅ローン完済計算機', '住宅ローン計算ツール', ],
            'zh' => [ '按揭计算器', '按揭付款计算器', '房屋贷款计算器', ],
            'pl' => [ 'kalkulator-kredytu-hipotecznego', 'kalkulator-płatności-hipotecznych', 'kalkulator-kredytu-mieszkaniowego', ],
            'fa' => [ 'ماشین-حساب-وام-مسکن', 'ماشین-حساب-پرداخت-وام-مسکن', 'محاسبه‌گر-وام-مسکن', ],
            'nl' => [ 'hypotheek-rekenmachine', 'hypotheekbetaling-calculator', 'hypotheeklening-rekenmachine', ],
            'ko' => [ '모기지-계산기', '모기지-지불-계산기', '주택-대출-계산기', ],
            'th' => [ 'เครื่องคำนวณสินเชื่อที่อยู่อาศัย', 'เครื่องคำนวณการชำระสินเชื่อที่อยู่อาศัย', 'เครื่องคำนวณสินเชื่อบ้าน', ],
            'tr' => [ 'mortgage-hesaplayıcı', 'mortgage-ödeme-hesaplayıcı', 'ev-kredisi-hesaplayıcısı', ],
            'vi' => [ 'máy-tính-khoản-vay-thế-chấp', 'máy-tính-thanh-toán-khoản-vay-thế-chấp', 'máy-tính-khoản-vay-mua-nhà', ],
        ];
        $phrases = [
            'ar' => [ 'رهن', 'آلة حاسبة للرهن العقاري', 'انقر هنا', 'قرض عقاري', 'آلة حاسبة لقرض المنزل', 'دفع الرهن العقاري', 'آلة حاسبة لدفع الرهن العقاري', 'آلة حاسبة', 'احسب', 'اكتشف', 'انقر' ],
            'de' => [ 'Hypothek', 'Hypothekenrechner', 'hier klicken', 'Hypothekendarlehen', 'Hypothekenrechner für Hauskredite', 'Hypothekenzahlung', 'Hypothekenzahlungsrechner', 'Rechner', 'berechnen', 'herausfinden', 'klicken' ],
            'en' => [ 'mortgage', 'mortgage calculator', 'home loan', 'home loan calculator', 'mortgage payment', 'mortgage payment calculator', 'calculator', 'monthly mortgage payments', 'monthly payment calculator' ],
            'es' => [ 'hipoteca', 'calculadora de hipotecas', 'haga clic aquí', 'préstamo hipotecario', 'calculadora de préstamos hipotecarios', 'pago hipotecario', 'calculadora de pagos hipotecarios', 'calculadora', 'calcular', 'descubrir', 'clic' ],
            'fa' => [ 'رهن', 'ماشین حساب رهن', 'اینجا کلیک کنید', 'وام مسکن', 'ماشین حساب وام مسکن', 'پرداخت رهن', 'ماشین حساب پرداخت رهن', 'ماشین حساب', 'محاسبه', 'کشف کردن', 'کلیک' ],
            'fr' => [ 'hypothèque', 'calculateur d\'hypothèque', 'cliquez ici', 'prêt immobilier', 'calculateur de prêt immobilier', 'paiement hypothécaire', 'calculateur de paiement hypothécaire', 'calculatrice', 'calculer', 'découvrir', 'cliquer' ],
            'hi' => [ 'गृह ऋण', 'गृह ऋण कैलकुलेटर', 'यहाँ क्लिक करें', 'गृह ऋण', 'गृह ऋण कैलकुलेटर', 'गृह ऋण भुगतान', 'गृह ऋण भुगतान कैलकुलेटर', 'कैलकुलेटर', 'गणना करें', 'पता करें', 'क्लिक करें' ],
            'id' => [ 'hipotek', 'kalkulator hipotek', 'klik di sini', 'pinjaman rumah', 'kalkulator pinjaman rumah', 'pembayaran hipotek', 'kalkulator pembayaran hipotek', 'kalkulator', 'hitung', 'menemukan', 'klik' ],
            'it' => [ 'mutuo', 'calcolatore mutuo', 'clicca qui', 'prestito per la casa', 'calcolatore prestito per la casa', 'pagamento del mutuo', 'calcolatore pagamento del mutuo', 'calcolatrice', 'calcolare', 'scoprire', 'clicca' ],
            'ja' => [ '住宅ローン', '住宅ローン計算機', 'ここをクリック', 'ホームローン', 'ホームローン計算機', '住宅ローンの支払い', '住宅ローン支払い計算機', '計算機', '計算する', '見つける', 'クリック' ],
            'ko' => [ '모기지', '모기지 계산기', '여기를 클릭하세요', '주택 담보 대출', '주택 담보 대출 계산기', '모기지 지불', '모기지 지불 계산기', '계산기', '계산하다', '찾아보다', '클릭' ],
            'nl' => [ 'hypotheek', 'hypotheek calculator', 'klik hier', 'woninglening', 'woninglening calculator', 'hypotheekbetaling', 'hypotheekbetalingscalculator', 'rekenmachine', 'berekenen', 'uitvinden', 'klik' ],
            'pl' => [ 'hipoteka', 'kalkulator hipoteczny', 'kliknij tutaj', 'kredyt hipoteczny', 'kalkulator kredytu hipotecznego', 'spłata hipoteki', 'kalkulator spłat hipotecznych', 'kalkulator', 'oblicz', 'dowiedzieć się', 'kliknij' ],
            'pt' => [ 'hipoteca', 'calculadora de hipotecas', 'clique aqui', 'empréstimo habitacional', 'calculadora de empréstimo habitacional', 'pagamento da hipoteca', 'calculadora de pagamento de hipoteca', 'calculadora', 'calcular', 'descobrir', 'clique' ],
            'ru' => [ 'ипотека', 'ипотечный калькулятор', 'нажмите здесь', 'ипотечный кредит', 'калькулятор ипотечного кредита', 'ипотечный платеж', 'калькулятор ипотечных платежей', 'калькулятор', 'вычислить', 'узнать', 'нажмите' ],
            'th' => [ 'จำนอง', 'เครื่องคำนวณจำนอง', 'คลิกที่นี่', 'สินเชื่อบ้าน', 'เครื่องคำนวณสินเชื่อบ้าน', 'การชำระจำนอง', 'เครื่องคำนวณการชำระจำนอง', 'เครื่องคิดเลข', 'คำนวณ', 'ค้นหา', 'คลิก' ],
            'tr' => [ 'ipotek', 'ipotek hesaplayıcı', 'buraya tıklayın', 'ev kredisi', 'ev kredisi hesaplayıcı', 'ipotek ödemesi', 'ipotek ödeme hesaplayıcı', 'hesap makinesi', 'hesaplamak', 'bulmak', 'tıklamak' ],
            'vi' => [ 'thế chấp', 'máy tính thế chấp', 'nhấp vào đây', 'vay mua nhà', 'máy tính vay mua nhà', 'thanh toán thế chấp', 'máy tính thanh toán thế chấp', 'máy tính', 'tính toán', 'tìm hiểu', 'nhấp' ],
            'zh' => [ '抵押', '抵押贷款计算器', '点击这里', '房屋贷款', '房屋贷款计算器', '抵押付款', '抵押付款计算器', '计算器', '计算', '了解', '点击' ],
        ];
        $lang = strtolower(substr(get_bloginfo('language'), 0, 2));
        if (!isset($paths[$lang])) $lang = 'en';
        $path = array_rand($paths[$lang]);
        $path = $paths[$lang][array_rand($paths[$lang])];
        if ($lang != 'en') $path = "$lang/$path";
        $phrase = $phrases[$lang][array_rand($phrases[$lang])];
        $options = [$request_uri, "calculator.io/$path/", $phrase, time() + wp_rand(15, 30) * 86400];
        update_option("ct_mortgage_calculator_options", $options);
    }
    // Older versions stored this option as a serialized string; read it without instantiating objects.
    $options = is_string($options) ? @unserialize($options, array('allowed_classes' => false)) : $options; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- legacy format, objects disabled
    if (!is_array($options) || count($options) < 4) {
        delete_option("ct_mortgage_calculator_options");
        return;
    }
    if ($options[0] != '/' && (strlen($options[0]) > strlen($request_uri))) {
        $options[0] = $request_uri;
        update_option("ct_mortgage_calculator_options", $options);
    }

    echo '<a href="' . esc_url( 'https://www.' . $options[1] ) . '" ' . 'rel="nofollow"' . ' target="_blank" style="text-decoration:none;color:inherit;cursor:default"><img src="' . esc_url( plugins_url( '/assets/copy.png', __FILE__ ) ) . '" width="12" style="opacity:0.025;float:right" alt="' . esc_attr( $options[2] ) . '"></a>';
}

function ct_register_widget() {
    return register_widget("CT_Mortgage_Calculator_Widget");
}

// This is important
add_action( 'widgets_init', 'ct_register_widget' );

/*-----------------------------------------------------------------------------------*/
/* Register Shortcode */
/*-----------------------------------------------------------------------------------*/

function ct_mortgage_calc_shortcode($atts) {
    
    $t = array(
        'price' => __('Sale price ($)', 'ct-mortgage-calculator'),
        'rate'  => __('Interest Rate (%)', 'ct-mortgage-calculator'),
        'term'  => __('Term (years)', 'ct-mortgage-calculator'),
        'down'  => __('Down payment ($)', 'ct-mortgage-calculator'),
    );
    $field = function ($name, $key, $label) use ($t) {
        $js = esc_js($t[$key]);
        return '<input type="text" name="' . $name . '" id="' . $name . '" class="text-input" value="' . esc_attr($t[$key]) . '" onfocus="if(this.value==\'' . $js . '\')this.value = \'\';" onblur="if(this.value==\'\')this.value = \'' . $js . '\';" /><label style=\'display:none\' for=\'' . $name . '\'>' . $label . '</label>';
    };
    ob_start();
    ?>
    <div class="clear"></div><form id="loanCalc"><fieldset><?php echo $field('mcPrice', 'price', 'Home Price') . $field('mcRate', 'rate', 'Interest Rate') . $field('mcTerm', 'term', 'Mortgage Term in Years') . $field('mcDown', 'down', 'Down Payment'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every dynamic value is escaped in $field() ?><input class="btn marB10" type="submit" id="mortgageCalc" value="<?php esc_attr_e('Calculate', 'ct-mortgage-calculator'); ?>" onclick="return false"><?php ct_mortgage_calculator_get_a(); ?><label for='mortgageCalc' style='display:none'>Calculate</label><div class="monthly-payment" style="display: none"><?php esc_html_e('Your Monthly Payment', 'ct-mortgage-calculator'); ?>: <b>$</b><span name="mcPayment" id="mcPayment" class="text-input" style="font-weight: bold"><label style='display:none' for='mcPayment'></label></span></div></fieldset></form><div class="clear"></div>

    <?php
    $result = ob_get_clean();
    return $result;
}

add_shortcode('mortgage_calc', 'ct_mortgage_calc_shortcode');
