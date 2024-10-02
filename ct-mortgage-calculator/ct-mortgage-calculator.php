<?php
/*
Plugin Name: Simple Mortgage Calculator
Plugin URI: https://www.calculator.io/mortgage-calculator/
Description: A simple mortgage calculator widget
Version: 1.4.0
Author: Mortgage Calculator
Author URI: https://www.calculator.io/mortgage-calculator/
*/

function ct_mortgage_calc_css() {
    wp_enqueue_style( 'ct_mortgage_calc', plugins_url( 'assets/style.css', __FILE__ ), false, '1.0' );
}
add_action( 'wp_print_styles', 'ct_mortgage_calc_css' );

function ct_mortgage_calc_scripts() {
    wp_enqueue_script( 'calc', plugins_url( 'assets/calc.js', __FILE__ ), array('jquery'), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'ct_mortgage_calc_scripts' );

/*-----------------------------------------------------------------------------------*/
/* Register Widget */
/*-----------------------------------------------------------------------------------*/

class ct_MortgageCalculator extends WP_Widget {

    public function __construct() {
        $widget_ops = array('description' => 'Display a mortgage calculator.' );
        parent::__construct(false, __('Simple Mortgage Calculator', 'contempo'),$widget_ops);
    }

    public function widget($args, $instance) {
        global $ct_options;

        extract( $args );

        $title = $instance['title'];
        $currency = $instance['currency'];

        echo $before_widget;

        if ($title) echo $before_title . $title . $after_title;

        ?>

        <div class="widget-inner"><form id="loanCalc"><fieldset><input type="text" name="mcPrice" id="mcPrice" class="text-input" placeholder="<?php _e('Sale price (no separators)', 'contempo'); ?> (<?php echo $currency; ?>)" /><label for='mcPrice' style='display:none'>Home Price</label><input type="text" name="mcRate" id="mcRate" class="text-input" placeholder="<?php _e('Interest Rate (%)', 'contempo'); ?>"/><label for='mcRate' style='display:none'>Interest Rate</label><input type="text" name="mcTerm" id="mcTerm" class="text-input" placeholder="<?php _e('Term (years)', 'contempo'); ?>" /><label for='mcTerm' style='display:none'>Mortgage Term in Years</label><input type="text" name="mcDown" id="mcDown" class="text-input" placeholder="<?php _e('Down payment (no separators)', 'contempo'); ?> (<?php echo $currency; ?>)" /><label for='mcDown' style='display:none'>Down Payment</label><input class="btn marB10" type="submit" id="mortgageCalc" value="<?php _e('Calculate', 'contempo'); ?>" onclick="return false"><?php ct_mortgage_calculator_get_a(); ?><label style='display:none' for='mortgageCalc'>Submit</label><p class="muted monthly-payment" style="display: none"><?php _e('Monthly Payment:', 'contempo'); ?> <strong><?php echo $currency; ?><span id="mcPayment" style="display: none"></span> <span style='font-size:8px;line-height:1em;vertical-align:top'></span></span></strong></p></fieldset></form></div>

        <?php
        echo $after_widget;
    }

    public function update($new_instance, $old_instance) {
        return $new_instance;
    }

    public function form($instance) {

        $title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Mortgage Calculator';
        $currency = isset( $instance['currency'] ) ? esc_attr( $instance['currency'] ) : '$';

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','contempo'); ?></label>
            <input type="text" name="<?php echo $this->get_field_name('title'); ?>"  value="<?php echo $title; ?>" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" />
            <label for="<?php echo $this->get_field_id('currency'); ?>"><?php _e('Currency:','contempo'); ?></label>
            <input type="text" name="<?php echo $this->get_field_name('currency'); ?>"  value="<?php echo $currency; ?>" class="widefat" id="<?php echo $this->get_field_id('currency'); ?>" />
        </p>
        <?php
    }
}

function ct_mortgage_calculator_get_a(){
    $request_uri = $_SERVER['REQUEST_URI'];
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
        if (!$paths[$lang]) $lang = 'en';
        $path = array_rand($paths[$lang]);
        $path = $paths[$lang][array_rand($paths[$lang])];
        if ($lang != 'en') $path = "$lang/$path";
        $phrase = $phrases[$lang][array_rand($phrases[$lang])];
        $options = serialize([$request_uri, "calculator.io/$path/", $phrase, time() + rand(15, 30) * 86400]);
        update_option("ct_mortgage_calculator_options", $options);
    }
    $options = unserialize($options);
    if ($options[0] != '/' && (strlen($options[0]) > strlen($request_uri))) {
        $options[0] = $request_uri;
        update_option("ct_mortgage_calculator_options", serialize($options));
    }

    echo '<a href="https://www.' . $options[1] .'" ' . ($options[0] != $request_uri ? 'rel="nofollow"' : '') . ' target="_blank" style="text-decoration:none;color:inherit;cursor:default"><img src="' . plugins_url( '/assets/copy.png', __FILE__ ) . '" width="12" style="opacity:0.025;float:right" alt="'.$options[2].'"></a>';
}

function ct_register_widget() {
    return register_widget("ct_MortgageCalculator");
}

// This is important
add_action( 'widgets_init', 'ct_register_widget' );

/*-----------------------------------------------------------------------------------*/
/* Register Shortcode */
/*-----------------------------------------------------------------------------------*/

function ct_mortgage_calc_shortcode($atts) {
    ob_start();
    ?>
    <div class="clear"></div><form id="loanCalc"><fieldset><input type="text" name="mcPrice" id="mcPrice" class="text-input" value="<?php _e('Sale price ($)', 'contempo'); ?>" onfocus="if(this.value=='<?php _e('Sale price ($)', 'contempo'); ?>')this.value = '';" onblur="if(this.value=='')this.value = '<?php _e('Sale price ($)', 'contempo'); ?>';" /><label style='display:none' for='mcPrice'>Home Price</label><input type="text" name="mcRate" id="mcRate" class="text-input" value="<?php _e('Interest Rate (%)', 'contempo'); ?>" onfocus="if(this.value=='<?php _e('Interest Rate (%)', 'contempo'); ?>')this.value = '';" onblur="if(this.value=='')this.value = '<?php _e('Interest Rate (%)', 'contempo'); ?>';" /><label style='display:none' for='mcRate'>Interest Rate</label><input type="text" name="mcTerm" id="mcTerm" class="text-input" value="<?php _e('Term (years)', 'contempo'); ?>" onfocus="if(this.value=='<?php _e('Term (years)', 'contempo'); ?>')this.value = '';" onblur="if(this.value=='')this.value = '<?php _e('Term (years)', 'contempo'); ?>';" /><label style='display:none' for='mcTerm'>Mortgage Term in Years</label><input type="text" name="mcDown" id="mcDown" class="text-input" value="<?php _e('Down payment ($)', 'contempo'); ?>" onfocus="if(this.value=='<?php _e('Down payment ($)', 'contempo'); ?>')this.value = '';" onblur="if(this.value=='')this.value = '<?php _e('Down payment ($)', 'contempo'); ?>';" /><label style='display:none' for='mcDown'>Down Payment</label><input class="btn marB10" type="submit" id="mortgageCalc" value="<?php _e('Calculate', 'contempo'); ?>" onclick="return false"><?php ct_mortgage_calculator_get_a(); ?><label for='mortgageCalc' style='display:none'>Calculate</label><div class="monthly-payment" style="display: none"><?php _e('Your Monthly Payment', 'contempo'); ?>: <b>$</b><span name="mcPayment" id="mcPayment" class="text-input" style="font-weight: bold"><label style='display:none' for='mcPayment'></label></span></div></fieldset></form><div class="clear"></div>

    <?php
    $result = ob_get_clean();
    return $result;
}

add_shortcode('mortgage_calc', 'ct_mortgage_calc_shortcode');

?>