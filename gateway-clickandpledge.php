<?php
/*
Plugin Name: WooCommerce Click & Pledge Gateway
Plugin URI: http://manual.clickandpledge.com/
Description: With Click & Pledge, Accept all major credit cards directly on your WooCommerce website with a seamless and secure checkout experience.<a href="http://manual.clickandpledge.com/" target="_blank">Click Here</a> to get a Click & Pledge account.
Version: 2.24070000-WP6.6.1-WC9.1.2
Author: Click & Pledge
Author URI: http://www.clickandpledge.com
*/
//@ini_set('display_errors', 0);
//error_reporting(E_ALL & ~E_NOTICE);
//define('WP_DEBUG', false);
//define('WP_DEBUG_DISPLAY', false);
ini_set("default_socket_timeout", 120);
add_action('plugins_loaded', 'woocommerce_clickandpledge_init', 0);
$cnpadditionalfe = get_option('woocommerce_clickandpledge_additionalfee');
if(isset($cnpadditionalfe['feeenabled']) && ($cnpadditionalfe['feeenabled']=='optin'))
{ 	
add_action( 'woocommerce_form_field_radio', 'custom_form_field_radio', 20, 4 );
function custom_form_field_radio( $field, $key, $args, $value ) {
    if ( ! empty( $args['options'] ) && is_checkout() ) {
        $field = str_replace( '</label><input ', '</label><br><input ', $field );
        $field = str_replace( '<label ', '<label style="display:inline;margin-left:8px;" ', $field );
    }
    return $field;
}
add_action( 'woocommerce_review_order_after_cart_contents', 'checkout_shipping_form_packing_addition', 20 );
	
function checkout_shipping_form_packing_addition( )
{
   
	$domain       = 'wocommerce';
	$cnpadditionalfee = get_option('woocommerce_clickandpledge_additionalfee');
	$cnpfeee=$cnpadditionalfee['feetitle'];
	$cnpfeeedflt=$cnpadditionalfee['feedfltoptn'];
	$addfeeinstructions = $cnpadditionalfee['feeinstructions'];//<br>[ '.$addfeeinstructions.']
    echo '<tr class="packing-select" id="cnpaddfee"><th>' . __($cnpfeee, $domain) . '</th><td>';

    $chosen   = WC()->session->get('chosen_packing');
    $chosen   = empty($chosen) ? WC()->checkout->get_value('radio_packing') : $chosen;
    $chosen   = empty($chosen) ? $cnpfeeedflt : $chosen;
	global $woocommerce;
	$cartshippingprice="";
			$cnpsubtotal= (preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_total() ));
			$taxes = WC()->cart->get_taxes(); 
       		foreach($taxes as $tax) $totaltax += $tax;
			foreach( WC()->session->get('shipping_for_package_0')['rates'] as $method_id => $rate ){
				if( WC()->session->get('chosen_shipping_methods')[0] == $method_id ){
					$rate_label = $rate->label; 
					$rate_cost_excl_tax = floatval($rate->cost); // The cost excluding tax
					// The taxes cost
					$rate_taxes = 0;
					foreach ($rate->taxes as $rate_tax)
						$rate_taxes += floatval($rate_tax);
					// The cost including tax
					$rate_cost_incl_tax = $rate_cost_excl_tax + $rate_taxes;

					$cartshippingprice = WC()->cart->get_cart_shipping_total();
					break;
				}
			} 
			$fees = 0; 
			$amount = WC()->cart->cart_contents_total + $totaltax + $rate_cost_excl_tax;
			$cnpcarttotal = $totaltax + $cnpsubtotal;
			if($cnpadditionalfee['feeper'] !=""){

				   $fees +=  ($amount * $cnpadditionalfee['feeper'])/100; 
			  
			}
			if($cnpadditionalfee['feeamt'] !=""){
			   $fees += $cnpadditionalfee['feeamt']; 
		    }
	$fees = round($fees,2);
	$fees = number_format((float)$fees, 2, '.', ''); 
	WC()->session->set('cnpfee', $fees );
	WC()->session->set('cnpfeetitle', $cnpfeee );
	$cnpoptout = str_replace("{AdditionalFee}",  get_woocommerce_currency_symbol()."".$fees ,$cnpadditionalfee['feeoptoutlbl']);
	$cnpoptin  = str_replace("{AdditionalFee}",  get_woocommerce_currency_symbol()."".$fees ,$cnpadditionalfee['feeoptinlbl']);
	$cnpoptaddnllbl = str_replace("{AdditionalFee}",  get_woocommerce_currency_symbol()."".$fees ,$cnpadditionalfee['feeinstructions']);
    // Add a custom checkbox field // 
if($fees == "0.00")
{
?>
<script>

jQuery('#cnpaddfee').hide();
jQuery('.fee').hide();
</script>

<?php
}
	 echo '<div class="my_custom_checkout_field">' . __($cnpoptaddnllbl) .'';
    woocommerce_form_field( 'radio_packing', array(
        'type' => 'radio',
        'class' => array( 'form-row-wide packing' ),
        'options' => array(
            'out' => __($cnpoptout, $domain),
            'in' => __($cnpoptin, $domain),
        ),
        'default' => $chosen,
    ), $chosen );

    echo '</td></tr>';
}

// Add a custom fee
add_action( 'woocommerce_cart_calculate_fees', 'add_packaging_fee', 20, 1 );
function add_packaging_fee( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    $packing_fee = WC()->session->get( 'chosen_packing' ); // Dynamic fee
	$fees = 0;
	$cnpadditionalfee = get_option('woocommerce_clickandpledge_additionalfee');
	global $woocommerce;
	$cartshippingprice = "";
			$cnpsubtotal = (preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_total() ));
			$taxes = WC()->cart->get_taxes(); 
       		foreach($taxes as $tax) $totaltax += $tax;
			foreach( WC()->session->get('shipping_for_package_0')['rates'] as $method_id => $rate ){
				if( WC()->session->get('chosen_shipping_methods')[0] == $method_id ){
					$rate_label = $rate->label; 
					$rate_cost_excl_tax = floatval($rate->cost); // The cost excluding tax
					// The taxes cost
					$rate_taxes = 0;
					foreach ($rate->taxes as $rate_tax)
						$rate_taxes += floatval($rate_tax);
					// The cost including tax
					$rate_cost_incl_tax = $rate_cost_excl_tax + $rate_taxes;

					$cartshippingprice = WC()->cart->get_cart_shipping_total();
					break;
				}
			} 
			  $fees = 0; 
			$amount = WC()->cart->cart_contents_total + $totaltax + $rate_cost_excl_tax;


		
			$cnpcarttotal = $totaltax + $cnpsubtotal;
			
			if($cnpadditionalfee['feeper'] !=""){

				   $fees +=  ($amount * $cnpadditionalfee['feeper'])/100; 
			  
			}
			if($cnpadditionalfee['feeamt'] !=""){

			   $fees += $cnpadditionalfee['feeamt']; 

		}
	$cnppacking_fee = $fees;
	$cnppacking_feetitle = $cnpadditionalfee['feetitle'];
	if( $cnpadditionalfee['feedfltoptn'] == "in"){$fee = $cnppacking_fee;}elseif( $cnpadditionalfee['feedfltoptn'] == "out") {$fee = "";}
	if($packing_fee == "in" ){$fee = $cnppacking_fee;}elseif($packing_fee == "out" ) {$fee = "";}
if (  is_checkout() ){

    $cart->add_fee( __( $cnppacking_feetitle, 'woocommerce' ), $fee );

}
}
add_action( 'wp_footer', 'checkout_shipping_packing_script' );
function checkout_shipping_packing_script() {
    if ( ! is_checkout() )
        return; // Only checkout page
    ?>
    <script type="text/javascript">
    jQuery( function($){
        $('form.checkout').on('change', 'input[name=radio_packing]', function(e){
            e.preventDefault();
            var p = $(this).val();
            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.ajax_url,
                data: {
                    'action': 'woo_get_ajax_data',
                    'packing': p,
                },
                success: function (result) {
                    $('body').trigger('update_checkout');
                    console.log('response: '+result); // just for testing | TO BE REMOVED
                },
                error: function(error){
                    console.log(error); // just for testing | TO BE REMOVED
                }
            });
        });
    });
    </script>
    <?php

}


// Php Ajax (Receiving request and saving to WC session)
add_action( 'wp_ajax_woo_get_ajax_data', 'woo_get_ajax_data' );
add_action( 'wp_ajax_nopriv_woo_get_ajax_data', 'woo_get_ajax_data' );
function woo_get_ajax_data() {
    if ( isset($_POST['packing']) ){
        $packing = sanitize_key( $_POST['packing'] );
        WC()->session->set('chosen_packing', $packing );
        echo json_encode( $packing );
    }
    die(); // Alway at the end (to avoid server error 500)
}


}
if(isset($cnpadditionalfe['feeenabled']) && ($cnpadditionalfe['feeenabled']=='yes'))
{ 
		add_action( 'woocommerce_cart_calculate_fees','endo_handling_fee' );
		function endo_handling_fee() {
			$cnpadditionalfe = get_option('woocommerce_clickandpledge_additionalfee');
		    global $woocommerce;$cartshippingprice="";
			$cnpsubtotal= (preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_total() ));
			$taxes = WC()->cart->get_taxes(); 
       		foreach($taxes as $tax) $totaltax += $tax;
			foreach( WC()->session->get('shipping_for_package_0')['rates'] as $method_id => $rate ){
				if( WC()->session->get('chosen_shipping_methods')[0] == $method_id ){
					$rate_label = $rate->label; 
					$rate_cost_excl_tax = floatval($rate->cost); // The cost excluding tax
					// The taxes cost
					$rate_taxes = 0;
					foreach ($rate->taxes as $rate_tax)
						$rate_taxes += floatval($rate_tax);
					// The cost including tax
					$rate_cost_incl_tax = $rate_cost_excl_tax + $rate_taxes;

					$cartshippingprice = WC()->cart->get_cart_shipping_total();
					break;
				}
			} 
			  $fees = 0; 
			$amount = WC()->cart->cart_contents_total + $totaltax + $rate_cost_excl_tax;


		
			$cnpcarttotal = $totaltax + $cnpsubtotal;
			
			if($cnpadditionalfe['feeper'] !=""){

				   $fees +=  ($amount * $cnpadditionalfe['feeper'])/100; 
			  
			}
			if($cnpadditionalfe['feeamt'] !=""){

			   $fees += $cnpadditionalfe['feeamt']; 

		}
		$cnpfee=$cnpadditionalfe['feetitle'];
			if( $fees != 0 ){
				$woocommerce->cart->add_fee( __($cnpfee, 'woocommerce'), $fees);
		}
	}
}

add_filter( 'default_checkout_billing_country', 'change_default_checkout_country' );
add_filter( 'default_checkout_billing_state', 'change_default_checkout_state' );

$zerocccnp = get_option('woocommerce_clickandpledge_zeropaymentsettings');
if(($zerocccnp['zerocreditcard'] != "" || $zerocccnp['zerocustom'] != "")) 
{
add_filter( 'woocommerce_cart_needs_payment', '__return_true' );
add_filter( 'woocommerce_order_needs_payment', '__return_true' );
}
function change_default_checkout_country( $country ) {
	$cnpuserid = get_current_user_id();
	$cnpusrmeta= get_user_meta($cnpuserid);
	
    if (! WC()->customer->get_is_paying_customer() ) {
        return $country;
	}
	else
	{
		return $cnpusrmeta['billing_country'][0];
	}

    /*return 'UK';*/ // override to default country
}

function change_default_checkout_state($state) {
	$cnpuserid = get_current_user_id();
	$cnpusrmeta= get_user_meta($cnpuserid);
	if (! WC()->customer->get_is_paying_customer() ) {
        return $state;
	}
	else
	{
		return $cnpusrmeta['billing_state'][0];
	}
}
function woocommerce_clickandpledge_init() {
  if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'admin_notices', 'wc_cnp_notice' );
  }
function wc_cnp_notice() {
		echo '<div class="error"><p><strong> <i> WooCommerce Click & Pledge Gateway </i> </strong> Requires <a href="'.admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce').'"> <strong> <u>Woocommerce</u></strong>  </a> To Be Installed And Activated </p></div>';
}
	
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }
	require_once( WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__)) . '/classes/clickandpledge-request.php' );
	add_action( 'wp_ajax_cnp_getAccounts', 'cnp_wcgetcnpaccounts' );
	add_action( 'wp_ajax_nopriv_cnp_getAccounts','cnp_wcgetcnpaccounts');
	add_action( 'wp_ajax_cnp_getcode', 'cnp_wcgetconnectcode');
	add_action( 'wp_ajax_nopriv_cnp_getcode', 'cnp_wcgetconnectcode');
	add_action( 'wp_ajax_getWCCnPAccountList', 'cnp_getWCCnPAccountList');
	add_action( 'wp_ajax_nopriv_getWCCnPAccountList', 'cnp_getWCCnPAccountList');
	add_action( 'wp_ajax_getCnPUserEmailAccountList', 'cnp_getCnPUserEmailAccountList');
	add_action( 'wp_ajax_nopriv_getCnPUserEmailAccountList', 'cnp_getCnPUserEmailAccountList');
	
	
	function cnp_getCnPUserEmailAccountList() {
		$cnpwcaccountid = $_REQUEST['cnpacid'];
		$paymntgtcls = new WC_Gateway_ClickandPledge();
    $cnpcurd = $paymntgtcls->getwcCnPCurrency($cnpwcaccountid);
if($cnpcurd == 840) {$cnpcurrval = "USD";}elseif($cnpcurd == 978) {$cnpcurrval = "EURO";}elseif($cnpcurd == 826) {$cnpcurrval = "POUND";}else {$cnpcurrval = "CAD";}
		$cnprtrntxt = $paymntgtcls->getwcCnPConnectCampaigns($cnpwcaccountid);
        $cnprtrnpaymentstxt = $paymntgtcls->getWCCnPactivePaymentList($cnpwcaccountid);
		echo $cnprtrntxt."||".$cnprtrnpaymentstxt."||".$cnpcurrval;
	  die();
	}
	 function getwcCnPrefreshtoken() {
		 
		global $wpdb;
		
        $table_name = $wpdb->prefix . 'cnp_wp_wccnptokeninfo';
        $sql = "SELECT cnptokeninfo_refreshtoken  FROM ". $table_name;
        $cnprefreshtkn = $wpdb->get_var( $sql );
		
		$cnpsettingsquery = "SELECT *  FROM ".$wpdb->prefix . 'cnp_wp_wccnpsettingsinfo';
		 $results = $wpdb->get_results($cnpsettingsquery, ARRAY_A);

        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
			 $password  = "password";
			 $cnpsecret = openssl_decrypt($results[$i]['cnpsettingsinfo_clentsecret'],"AES-128-ECB",$password);
			
			 $rtncnpdata = "client_id=".$results[$i]['cnpsettingsinfo_clientid']."&client_secret=". $cnpsecret."&grant_type=refresh_token&scope=".$results[$i]['cnpsettingsinfo_scope']."&refresh_token=".$cnprefreshtkn;
        }
		
			return $rtncnpdata;
			exit;
		
	 }
	function cnp_getWCCnPAccountList()
	{
		
		$rtnrefreshtokencnpdata = getwcCnPrefreshtoken();
		$cnpwcaccountid = $_REQUEST['rcnpwcaccountid'];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => "https://aaas.cloud.clickandpledge.com/IdServer/connect/token",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $rtnrefreshtokencnpdata,
		CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/x-www-form-urlencoded"

		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
			$cnptokendata = json_decode($response);
			
			$cnptoken = $cnptokendata->access_token;
			$cnprtokentyp = $cnptokendata->token_type;
			if($cnptoken != "")
			{
				$curl = curl_init();

			  curl_setopt_array($curl, array(
  			  CURLOPT_URL => "https://api.cloud.clickandpledge.com/users/accountlist",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: ".$cnprtokentyp." ".$cnptoken,
				"content-type: application/json"),
			  	));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);

				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				  
					$cnpAccountsdata = json_decode($response);

					$camrtrnval = "";
					$rtncnpdata = delete_wccnpaccountslist();
					
					
					  $confaccno 	 =  $cnpwcaccountid;	
				
					foreach($cnpAccountsdata as $cnpkey =>$cnpvalue)
					{
					 $selectacnt ="";
					 $cnporgid = $cnpvalue->OrganizationId;
					 $cnporgname = addslashes($cnpvalue->OrganizationName);
					 $cnpaccountid = $cnpvalue->AccountGUID;
					 $cnpufname = addslashes($cnpvalue->UserFirstName);
					 $cnplname = addslashes($cnpvalue->UserLastName);
                     $cnpcurr = addslashes($cnpvalue->CurrencyCode);
                     $cnpgateway = addslashes($cnpvalue->GatewayName);
				     $cnpuid = $cnpvalue->UserId;
					 $rtncnpdata = insert_cnpwcaccountsinfo($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid,$cnpcurr,$cnpgateway);
					 if($confaccno == $cnporgid){$selectacnt ="selected='selected'";}
					 	 $camrtrnval .= "<option value='".$cnporgid."' ".$selectacnt.">".$cnporgid." [".$cnpvalue->OrganizationName."]</option>";
		
		

	 }
					echo $camrtrnval;
					wp_die();
					}
				   
				}
			}
	}
		
	function get_cnpwctransactions($cnpemailid,$cnpcode)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'cnp_wp_wccnpsettingsinfo';
        $sql = "SELECT * FROM ". $table_name;
        $results = $wpdb->get_results($sql, ARRAY_A);

        $count = sizeof($results);
        for($i=0; $i<$count; $i++){
			 $password="password";
			 $cnpsecret = openssl_decrypt($results[$i]['cnpsettingsinfo_clentsecret'],"AES-128-ECB",$password);
			 $rtncnpdata = "client_id=".$results[$i]['cnpsettingsinfo_clientid']."&client_secret=". $cnpsecret."&grant_type=".$results[$i]['cnpsettingsinfo_granttype']."&scope=".$results[$i]['cnpsettingsinfo_scope']."&username=".$cnpemailid."&password=".$cnpcode;
        }

        return $rtncnpdata;
	}
	function delete_cnpwctransactions()
	{
		global $wpdb;
        $table_name = $wpdb->prefix .'cnp_wp_wccnptokeninfo';
        $wpdb->query("DELETE FROM ". $table_name);
	}
	function  insrt_cnpwctokeninfo($cnpemailid, $cnpcode, $cnptoken, $cnprtoken)
	{
		  global $wpdb;
        $table_name = $wpdb->prefix .'cnp_wp_wccnptokeninfo';
         $wpdb->insert($table_name, array('cnptokeninfo_username' => $cnpemailid, 
					'cnptokeninfo_code' => $cnpcode, 
					'cnptokeninfo_accesstoken' => $cnptoken,
					'cnptokeninfo_refreshtoken' => $cnprtoken));
		
            $id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
			
        return $id;
	}
	function delete_wccnpaccountslist()
	{
		  global $wpdb;
        $table_name = $wpdb->prefix . 'cnp_wp_wccnpaccountsinfo';
        $wpdb->query("DELETE FROM ". $table_name);
	}
	function insert_cnpwcaccountsinfo($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid,$cnpcur,$cnpgtway){
        global $wpdb;
        $table_name = $wpdb->prefix . 'cnp_wp_wccnpaccountsinfo';
      
            $wpdb->insert($table_name, array('cnpaccountsinfo_orgid' => $cnporgid, 
					'cnpaccountsinfo_orgname' => $cnporgname, 
					'cnpaccountsinfo_accountguid' => $cnpaccountid,
					'cnpaccountsinfo_userfirstname' => $cnpufname,
					'cnpaccountsinfo_userlastname'=> $cnplname,
					'cnpaccountsinfo_userid'=> $cnpuid,
                    'cnpaccountsinfo_cnpcurrency'=> $cnpcur,
					'cnpaccountsinfo_gatewayname'=> $cnpgtway));
            $id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
			
        return $id;
    }
	 function cnp_wcgetcnpaccounts(){
		
		$cnpemailid = $_REQUEST['wccnpemailid'];
		$cnpcode    = $_REQUEST['wccnpcode'];
		$cnptransactios = get_cnpwctransactions($cnpemailid,$cnpcode);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => "https://aaas.cloud.clickandpledge.com/idserver/connect/token",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $cnptransactios,
		CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/x-www-form-urlencoded"

		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		$cnptokendata = json_decode($response);
		
		 if(!isset($cnptokendata->error)){
			$cnptoken = $cnptokendata->access_token;
			$cnprtoken = $cnptokendata->refresh_token;
			$cnptransactios = delete_cnpwctransactions();
			$rtncnpdata =	insrt_cnpwctokeninfo($cnpemailid,$cnpcode,$cnptoken,$cnprtoken);	
			
			if($rtncnpdata != "")
			{
				$curl = curl_init();

			  curl_setopt_array($curl, array(
  			  CURLOPT_URL => "https://api.cloud.clickandpledge.com/users/accountlist",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Bearer ".$cnptoken,
				"content-type: application/json"),
			  	));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);

				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				 
					$cnpAccountsdata = json_decode($response);
				
					$cnptransactios = delete_wccnpaccountslist();
					
					foreach($cnpAccountsdata as $cnpkey =>$cnpvalue)
					{
					 $cnporgid = $cnpvalue->OrganizationId;
					 $cnporgname = addslashes($cnpvalue->OrganizationName);
					 $cnpaccountid = $cnpvalue->AccountGUID;
					 $cnpufname = addslashes($cnpvalue->UserFirstName);
					 $cnplname = addslashes($cnpvalue->UserLastName);
				     $cnpuid = $cnpvalue->UserId;
                    $cnpgtway = $cnpvalue->GatewayName;
				     $cnpcur = $cnpvalue->CurrencyCode;
					$cnptransactios = insert_cnpwcaccountsinfo($cnporgid,$cnporgname,$cnpaccountid,$cnpufname,$cnplname,$cnpuid,$cnpcur,$cnpgtway);	
						
					}
					//print_r($cnpAccountsdata);
				   echo "success";
				}
			}
			}else{
				echo "error";
			}
			
	    }
		die();
	}
	function cnp_wcgetconnectcode(){
  		$curl = curl_init();
		$cnpemailaddress = $_REQUEST['cnpemailid'];
		curl_setopt_array($curl, array(
  		CURLOPT_URL => "https://api.cloud.clickandpledge.com/users/requestcode",
  		CURLOPT_RETURNTRANSFER => true,
  	    CURLOPT_ENCODING => "",
  		CURLOPT_MAXREDIRS => 10,
  	    CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: application/x-www-form-urlencoded",
        "email: ".$cnpemailaddress
	  ),
	));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
	
 
  wp_die(); // ajax call must die to avoid trailing 0 in your response
}
			global $wpdb;
			$table_name         = $wpdb->prefix . 'cnp_wp_wccnpsettingsinfo';
			$tokentable_name    = $wpdb->prefix . 'cnp_wp_wccnptokeninfo';
			$accountstable_name = $wpdb->prefix . 'cnp_wp_wccnpaccountsinfo';

			$charset_collate = $wpdb->get_charset_collate();

			$prevaccounttable =  $wpdb->prefix . 'cnpaccountsinfo';
			$prevsettingtable =  $wpdb->prefix . 'cnpsettingsinfo';
			$prevtokentable   =  $wpdb->prefix . 'cnptokeninfo';
	
			$cnpselcttable = "show tables like '".$accountstable_name."'";
			$cnpselrowcount = $wpdb->get_var( $cnpselcttable );
			if($cnpselrowcount == ""){

				$cnpselcttable = "show tables like '".$prevaccounttable."'";
				$cnpselrowcount = $wpdb->get_var( $cnpselcttable );
				if($cnpselrowcount != ""){
					$cnpselrowcount = $wpdb->get_var("RENAME TABLE ".$prevaccounttable." TO ".$accountstable_name.",".$prevsettingtable." TO ".$table_name.",".$prevtokentable." TO ".$tokentable_name);
				}

			}
	
			$settingssql = "CREATE TABLE $table_name (
			  `cnpsettingsinfo_id` int(11) NOT NULL AUTO_INCREMENT,
			  `cnpsettingsinfo_clientid` varchar(255) NOT NULL,
			  `cnpsettingsinfo_clentsecret` varchar(255) NOT NULL,
			  `cnpsettingsinfo_granttype` varchar(255) NOT NULL,
			  `cnpsettingsinfo_scope` varchar(255) NOT NULL,
			   PRIMARY KEY (`cnpsettingsinfo_id`)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $settingssql );
	$cnpsql= "SELECT count(*) FROM ". $table_name;
			$rowcount = $wpdb->get_var( $cnpsql );
			if($rowcount == 0)
			{
					$cnpfldname = 'connectwordpressplugin';
					$cnpfldtext = 'zh6zoyYXzsyK9fjVQGd8m+ap4o1qP2rs5w/CO2fZngqYjidqZ0Fhbhi1zc/SJ5zl';
					$cnpfldpwd = 'password';
					$cnpfldaccsid = 'openid profile offline_access';


					$wpdb->insert( 
						$table_name, 
						array( 
							'cnpsettingsinfo_clientid' => $cnpfldname, 
							'cnpsettingsinfo_clentsecret' => $cnpfldtext, 
							'cnpsettingsinfo_granttype' => $cnpfldpwd,
							'cnpsettingsinfo_scope' => $cnpfldaccsid,
						) 
					);			
            }
			$tokensql = "CREATE TABLE $tokentable_name (
 			`cnptokeninfo_id` int(11) NOT NULL AUTO_INCREMENT,
			`cnptokeninfo_username` varchar(255) NOT NULL,
			`cnptokeninfo_code` varchar(255) NOT NULL,
			`cnptokeninfo_accesstoken` text NOT NULL,
			`cnptokeninfo_refreshtoken` text NOT NULL,
			`cnptokeninfo_date_added` datetime NOT NULL,
			`cnptokeninfo_date_modified` datetime NOT NULL,
			 PRIMARY KEY (`cnptokeninfo_id`)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $tokensql );
			
			$accountssql = "CREATE TABLE $accountstable_name (
 			  `cnpaccountsinfo_id` int(11) NOT NULL AUTO_INCREMENT,
			  `cnpaccountsinfo_orgid` varchar(100) NOT NULL,
  			  `cnpaccountsinfo_orgname` varchar(250) NOT NULL,
  	          `cnpaccountsinfo_accountguid` varchar(250) NOT NULL,
			  `cnpaccountsinfo_userfirstname` varchar(250) NOT NULL,
			  `cnpaccountsinfo_userlastname` varchar(250) NOT NULL,
			  `cnpaccountsinfo_userid` varchar(250) NOT NULL,
			  `cnpaccountsinfo_crtdon` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `cnpaccountsinfo_crtdby` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`cnpaccountsinfo_id`)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $accountssql );
	$check_column = (array) $wpdb->get_results("SELECT count(COLUMN_NAME) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME = '$accountstable_name' AND COLUMN_NAME = 'cnpaccountsinfo_cnpcurrency'")[0];

		$table_name = $accountstable_name;
		 $check_column = (int) array_shift($check_column);
		 if($check_column == 0) {
		 $wpdb->query("ALTER TABLE $table_name   ADD COLUMN `cnpaccountsinfo_cnpcurrency` varchar(250) NOT NULL,ADD COLUMN `cnpaccountsinfo_gatewayname` varchar(250) NOT NULL");
		  }
 
		

	/**
 	* Gateway class
 	**/
	class WC_Gateway_ClickandPledge extends WC_Payment_Gateway {
		var $AccountID;
		var $AccountGuid;
		var $maxrecurrings_Installment;
		var $maxrecurrings_Subscription;
		var $pselectedval;
		var $liveurl = 'http://manual.clickandpledge.com/';
		var $testurl = 'http://manual.clickandpledge.com/';
		var $testmode;
	

		function __construct() { 
			
			$this->id				= 'clickandpledge';
			$this->method_title 	= __('Click & Pledge', 'woothemes');
			$this->method_description = __( 'Click & Pledge works by adding credit card fields on the checkout and then sending the details to Click & Pledge for verification.

			.', 'woocommerce' );
			$this->icon 			= WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/images/CP_Secured.jpg';
			
			// Load the form fields
			$this->init_form_fields();
			// Load the settings.
			$this->init_settings();
			// Get setting values
			if(isset($this->settings['title']))	{$this->title 			= stripslashes_deep($this->settings['title']);	}
			if(isset($this->settings['description'])){ $this->description 		= stripslashes_deep($this->settings['description']);}
			if(isset($this->settings['enabled'])){	$this->enabled 		= $this->settings['enabled'];}
			if(isset($this->settings['AccountID']))	{  $this->AccountID 		= $this->settings['AccountID'];	}
			if(isset($this->settings['AccountGuid'])){	$this->AccountGuid 		= $this->settings['AccountGuid'];}
			if(isset($this->settings['ConnectCampaignAlias'])){	$this->ConnectCampaignAlias 		= $this->settings['ConnectCampaignAlias'];}
			if(isset($this->settings['testmode']))	{ $this->testmode 		= $this->settings['testmode'];	}
			if(isset($this->settings['DefaultpaymentMethod']))	{ $this->defaultpayment   = $this->settings['DefaultpaymentMethod'];	}
			if(isset($this->settings['Preauthorization']))	{ $this->Preauthorization   = $this->settings['Preauthorization'];	}
			if(isset($this->settings['ReferenceNumber_Label']))	{ $this->ReferenceNumber_Label   = $this->settings['ReferenceNumber_Label'];	}

			$this->Periodicity      = array();
			$this->RecurringMethod  = array();
			$this->available_cards  = array();
			$this->CustomPayments   = array();
			$this->recurring_details   = array();
       
			if($this->description == "") {$this->description = "Pay with Click & Pledge Payment Gateway.";}
			
			if(isset($this->settings['CreditCard']) && $this->settings['CreditCard'] == 'yes')
			$this->Paymentmethods['CreditCard'] = 'Credit Card';
			if(isset($this->settings['eCheck']) && $this->settings['eCheck'] == 'yes')
			$this->Paymentmethods['eCheck'] = 'eCheck';
		
			if(isset($this->settings['CustomPayment']) && $this->settings['CustomPayment'] == 'yes') {
				$CustomPayments = explode(';', html_entity_decode($this->settings['CustomPayment_Titles']));
				if(count($CustomPayments) > 0) {
					foreach($CustomPayments as $key => $val) {
						if(trim($val) != '') {
							$this->Paymentmethods[trim($val)] = trim($val);
							$this->CustomPayments[] = trim($val);
						}
					}
				}				
			}			
			
			
			//Available Credit Cards
			$this->acceptedcreditcards_details = get_option( 'woocommerce_clickandpledge_acceptedcreditcards',
		
				array(
					'Visa'                 => $this->get_option( 'Visa' ),
					'American Express'     => $this->get_option( 'American Express' ),
					'Discover'             => $this->get_option( 'Discover' ),
					'MasterCard'           => $this->get_option( 'MasterCard' ),
					'JCB'                  => $this->get_option( 'JCB' )
					
				
			)
		);
			if((isset($this->acceptedcreditcards_details['Visa']) && ($this->acceptedcreditcards_details['Visa'] == 'Visa') )){
				$this->available_cards['Visa']		= 'Visa';
			}
			if((isset($this->acceptedcreditcards_details['American_Express']) && ($this->acceptedcreditcards_details['American_Express'] == 'American Express') )){
				$this->available_cards['American Express']		= 'American Express';
			}
			if((isset($this->acceptedcreditcards_details['Discover']) && ($this->acceptedcreditcards_details['Discover'] == 'Discover') )){
				$this->available_cards['Discover']		= 'Discover';
                $this->available_cards['JCB']		= 'JCB';
			}
			if((isset($this->acceptedcreditcards_details['MasterCard']) && ($this->acceptedcreditcards_details['MasterCard'] == 'MasterCard') )){
				$this->available_cards['MasterCard']		= 'MasterCard';
			}
			/*if((isset($this->acceptedcreditcards_details['JCB']) && ($this->acceptedcreditcards_details['JCB'] == 'JCB') )){
				$this->available_cards['JCB']		= 'JCB';
			}*/
			
		
			$this->isRecurring 		= (isset($this->settings['isRecurring']) && ($this->settings['isRecurring'] == '1')) ? true : false;
		
			$this->indefinite 		= (isset($this->settings['indefinite']) && $this->settings['indefinite'] == 'yes') ? true : false;
			
			$this->additionalfee_details = get_option( 'woocommerce_clickandpledge_additionalfee', array(
					'feeenabled'             => $this->get_option( 'feeenabled' ),
					'feetitle'               => $this->get_option( 'feetitle' ),
					'feeamt'                 => $this->get_option( 'feeamt' ),
					'feeper'                 => $this->get_option( 'feeper' ),
					'feesku'                 => $this->get_option( 'feesku' ),
					'feetax'                 => $this->get_option( 'feetax' ),
					'feeinstructions'        => $this->get_option( 'feeinstructions' ),
					'feeoptoutlbl'           => $this->get_option( 'feeoptoutlbl' ),
					'feeoptinlbl'            => $this->get_option( 'feeoptinlbl' ),
					'feedfltoptn'            => $this->get_option( 'feedfltoptn' )
					)
				);
        		$this->zeropaymentsettings_details = get_option( 'woocommerce_clickandpledge_zeropaymentsettings', array(
					'zerocreditcard'             => $this->get_option( 'zerocreditcard' ),
					'zerocustom'                 => $this->get_option( 'zerocustom' ),
					'zerocustompaynt'            => $this->get_option( 'zerocustompaynt' )
					)
				);
			$this->paymentsettings_details = get_option( 'woocommerce_clickandpledge_paymentsettings', array(
					'enabled'             => $this->get_option( 'enabled' ),
					'title'               => $this->get_option( 'title' ),
					'description'         => $this->get_option( 'description' ),
					'AccountID'           => $this->get_option( 'AccountID' ),
					'testmode'            => $this->get_option( 'testmode' ),
					'ConnectCampaignAlias'=> $this->get_option( 'ConnectCampaignAlias' ),
					'CreditCard'          => $this->get_option( 'CreditCard' ),
					'eCheck'              => $this->get_option( 'eCheck' ),
					'American_Express'    => $this->get_option( 'American_Express' ),
					'JCB'                 => $this->get_option( 'JCB' ),
					'MasterCard'          => $this->get_option( 'MasterCard' ),
					'Visa'                => $this->get_option( 'Visa' ),	
					'dfltpayoptn'         => $this->get_option( 'Discover' ),	
				//	'Preauthorization'    => $this->get_option( 'Preauthorization'),
					'CustomPayment'       => $this->get_option( 'CustomPayment'),
            		'ReferenceNumber_Label'       => $this->get_option( 'ReferenceNumber_Label'),
            		'CustomPayment_Titles'       => $this->get_option( 'CustomPayment_Titles')
					)
				);
			if((isset($this->paymentsettings_details['Visa']) && 
				     ($this->paymentsettings_details['Visa'] == 'Visa') )){
				$this->available_cards['Visa']		= 'Visa';
			}
			if((isset($this->paymentsettings_details['American_Express']) && 
				     ($this->paymentsettings_details['American_Express'] == 'amex') )){
				$this->available_cards['American Express']		= 'American Express';
			}
			if((isset($this->paymentsettings_details['MasterCard']) && 
				     ($this->paymentsettings_details['MasterCard'] == 'Master') )){
				$this->available_cards['MasterCard']		= 'MasterCard';
			}
			/*if((isset($this->paymentsettings_details['JCB']) && 
				     ($this->paymentsettings_details['JCB'] == 'jcb') )){
				$this->available_cards['JCB']		= 'JCB';
			}*/
			if((isset($this->paymentsettings_details['dfltpayoptn']) && 
				     ($this->paymentsettings_details['dfltpayoptn'] == 'Discover') )){
				$this->available_cards['Discover']		= 'Discover';
            $this->available_cards['JCB']		= 'JCB';
			}
			if(isset($this->paymentsettings_details['AccountID']) && 
				         $this->paymentsettings_details['AccountID'] != '') {
					$this->AccountID  = $this->paymentsettings_details['AccountID'];
			}
			if(isset($this->paymentsettings_details['AccountID']) && 
				         $this->paymentsettings_details['AccountID'] != '') {
			    $AccountGuidnew = $this->getwcCnPAccountGUID($this->paymentsettings_details['AccountID']);
			   $this->AccountGuidnw  = $AccountGuidnew;
				if($AccountGuidnew == "")
				{$this->AccountGuid = $this->AccountGuid;}
				else{$this->AccountGuid =$this->AccountGuidnw;}
				
			}
				if(isset($this->paymentsettings_details['AccountID']) && 
				         $this->paymentsettings_details['AccountID'] != '') {
					$this->enabled  = $this->paymentsettings_details['enabled'];
				}
			if(isset($this->paymentsettings_details['AccountID']) && 
				         $this->paymentsettings_details['AccountID'] != '') {
					$this->testmode  = $this->paymentsettings_details['testmode'];
				}
			
			if(isset($this->paymentsettings_details['title']) && 
				         $this->paymentsettings_details['title'] != '') {
					$this->title  = stripslashes_deep($this->paymentsettings_details['title']);
				}
			if(isset($this->paymentsettings_details['ReferenceNumber_Label']) && 
				         $this->paymentsettings_details['ReferenceNumber_Label'] != '') {
					$this->ReferenceNumber_Label  = $this->paymentsettings_details['ReferenceNumber_Label'];
				}
		//print_r($this->Paymentmethods); print_r($this->paymentsettings_details['CreditCard']);
			if(isset($this->paymentsettings_details['CreditCard']) && $this->paymentsettings_details['CreditCard'] == 'CreditCard')$this->Paymentmethods =array();
			$this->Paymentmethods['CreditCard'] = 'Credit Card';
			if(isset($this->paymentsettings_details['eCheck']) && $this->paymentsettings_details['eCheck'] == 'eCheck')
			$this->Paymentmethods['eCheck'] = 'eCheck';
		if(isset($this->paymentsettings_details['ConnectCampaignAlias']) && 
				         $this->paymentsettings_details['ConnectCampaignAlias'] != '') {
					$this->ConnectCampaignAlias  = $this->paymentsettings_details['ConnectCampaignAlias'];
				}
			if(isset($this->paymentsettings_details['CustomPayment']) && $this->paymentsettings_details['CustomPayment'] == 'Purchase Order') {
				$CustomPayments = explode(';', html_entity_decode($this->paymentsettings_details['CustomPayment_Titles']));
				if(count($CustomPayments) > 0) {
					foreach($CustomPayments as $key => $val) {
						if(trim($val) != '') {
							$this->Paymentmethods[trim($val)] = trim($val);
							$this->CustomPayments[] = trim($val);
						}
					}
				}				
			}	
		
			$this->recurring_details = get_option( 'woocommerce_clickandpledge_recurring',
			array(
					'Installment'            => $this->get_option( 'Installment' ),
					'Subscription'           => $this->get_option( 'Subscription' ),
					'week'                   => $this->get_option( 'week' ),
					'tweeks'                 => $this->get_option( 'tweeks' ),
					'month'                  => $this->get_option( 'month' ),
					'2months'                => $this->get_option( '2months' ),
					'quarter'                => $this->get_option( 'quarter' ),
					'smonths'                => $this->get_option( 'smonths' ),
					'year'                   => $this->get_option( 'year' ),
					'indefinite'             => $this->get_option( 'indefinite' ),
					'isRecurring_oto'        => $this->get_option( 'isRecurring_oto' ),
					'isRecurring_recurring'  => $this->get_option( 'isRecurring_recurring' ),
					'dfltpayoptn'            => $this->get_option( 'dfltpayoptn' ),
					'dfltrectypoptn'         => $this->get_option( 'dfltrectypoptn' ),
					'dfltnoofpaymnts'        => $this->get_option( 'dfltnoofpaymnts' ),
					'payoptn'                => $this->get_option( 'payoptn' ),
					'rectype'                => $this->get_option( 'rectype' ),
					'periodicity'            => $this->get_option( 'periodicity' ),
					'noofpayments'           => $this->get_option( 'noofpayments' ),
					'dfltnoofpaymentslbl'    => $this->get_option( 'dfltnoofpaymentslbl' ),
					'maxnoofinstallments'    => $this->get_option( 'maxnoofinstallments' ),
					'maxrecurrings_Subscription'  => $this->get_option( 'maxrecurrings_Subscription' )
				
			)
		);
		   if((isset($this->recurring_details['installment']) && ($this->recurring_details['installment'] == 'Installment') )){
				$this->RecurringMethod['Installment']		= 'Installment';
			}
			if((isset($this->recurring_details['subscription']) && ($this->recurring_details['subscription'] == 'Subscription') )){
				$this->RecurringMethod['Subscription']		= 'Subscription';
			}
			
			if((isset($this->recurring_details['week']) && ($this->recurring_details['week'] == 'Week') )) {
				$this->Periodicity['Week']		= 'Week';
			}
			if((isset($this->recurring_details['2_weeks']) && ($this->recurring_details['2_weeks'] == '2 Weeks'))) {
				$this->Periodicity['2 Weeks']		= '2 Weeks';
			}
			if((isset($this->recurring_details['month']) && ($this->recurring_details['month'] == 'Month'))) {
				$this->Periodicity['Month']		= 'Month';
			}
			if((isset($this->recurring_details['2_months']) && ($this->recurring_details['2_months'] == '2 Months'))) {
				$this->Periodicity['2 Months']		= '2 Months';
			}
			if((isset($this->recurring_details['quarter']) && ($this->recurring_details['quarter'] == 'Quarter') )) {
				$this->Periodicity['Quarter']		= 'Quarter';
			}
			if((isset($this->recurring_details['6_months']) && ($this->recurring_details['6_months'] == '6 Months') )){
				$this->Periodicity['6 Months']		= '6 Months';
			}
			if((isset($this->recurring_details['year']) && ($this->recurring_details['year'] == 'Year') )){
				$this->Periodicity['Year']		= 'Year';
			}
			
			
			// Hooks
			add_action( 'admin_notices', array( &$this, 'ssl_check') );			
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_recurring_details' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_acceptedcreditcards_details' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_paymentsettings_details' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_additionalfee_details' ) );
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_zeropaymentsettings_details' ) );
		}
		
	
		/**
	 	* Check if SSL is enabled and notify the user if SSL is not enabled
	 	**/
		function ssl_check() {
	      if ($this->recurring_details['isRecurring_oto'] == "" &&  $this->recurring_details['isRecurring_recurring'] == "")
		 {	
				echo '<div class="error"><p>'.sprintf(__('Click & Pledge is enabled, but you have not added any <strong>Recurring Settings</strong>.<br>Customers will not be able to purchase products from your store until you set <strong>Recurring Settings</strong>.', 'woothemes'), admin_url('admin.php?page=woocommerce')).'</p></div>';
		}

		if (get_option('woocommerce_force_ssl_checkout')=='no' && ($this->enabled=='yes' || $this->paymentsettings_details['enabled']=='yes')) {
		
			echo '<div class="error"><p>'.sprintf(__('Click & Pledge is enabled, but the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate - Click & Pledge will only work in test mode.', 'woothemes'), admin_url('admin.php?page=woocommerce')).'</p></div>';
		
		}
		
		}
		
		/**
	     * Initialize Gateway Settings Form Fields
	     */
	    function init_form_fields() {	
			$paddingleft = 80;	
			$paddingheight ="height:2px;";	
			$cnppadding ="padding:2px !important;";	
	    	$this->form_fields = array(
			'paymentsettings_details' => array(
							'type'        => 'paymentsettings_details'),
			/*'CustomPayment_Titles' => array(
								'title' => __( '<span style="padding-left:130px;">Title(s) <span style="color: #ff0000">*</span></span>', 'woothemes' ), 
								'type' => 'textarea', 
								'description' => __( 'Separate with semicolon (;)', 'woothemes' ), 
								'default' => '',
								'maxlength' => 1500,),
           
			'ReferenceNumber_Label' => array(
								'title' => __( '<span style="padding-left:130px;">Reference Number Label</span>', 'woothemes' ), 
								'type' => 'text', 
								'description' => __( '', 'woothemes' ), 
								'default' => '',
								'maxlength'=>10,),		*/	
							
			'DefaultpaymentMethod' => array(
								'title' => __( '<span style="font-weight: bold;">Default Payment Method</span>', 'woothemes' ), 
								'type' => 'select',
								'class' => 'cnpselect',
								'options'     => array('' => 'Please select'),),
		
			 'zeropayment_details' => array(
							'type'        => 'zeropayment_details'),
            
			'ReceiptSettings' => array(
								'title' => __( '<span style="font-weight: bold;">Receipt Settings</span>', 'woothemes' ), 
								'type' => 'title',
								'class' => 'ReceiptSettingsSection',
							),
			'cnp_email_customer' => array(
								'title' => __( 'Send Receipt to Patron', 'woothemes' ), 
								'type' => 'checkbox', 
								'description' => __( '', 'woothemes' ), 
								'default' => 'yes',
								'label'       => __( ' ', 'woocommerce' ),),
							
		    'OrganizationInformation' => array(
								'title' => __( 'Receipt Header', 'woothemes' ), 
								'type' => 'textarea', 
								'description' => __( 'Maximum: 1500 characters, the following HTML tags are allowed:&lt;P>&lt;/P&gt;&lt;OL&gt;&lt;/OL&gt;&lt;LI&gt;&lt;/LI&gt;&lt;UL&gt;&lt;/UL&gt;&lt;br&gt;You have <span id="OrganizationInformation_countdown">1500</span> characters left.', 'woothemes' ), 
								'default' => '',
								'maxlength' => 1500,),				
			'TermsCondition' => array(
								'title' => __( 'Terms & Conditions', 'woothemes' ), 
								'type' => 'textarea', 
								'description' => __( 'To be added at the bottom of the receipt. Typically the text provides proof that the patron has read & agreed to the terms & conditions. The following HTML tags are allowed:
&lt;P>&lt;/P&gt;&lt;OL&gt;&lt;/OL&gt;&lt;LI&gt;&lt;/LI&gt;&lt;UL&gt;&lt;/UL&gt;&lt;br&gt;Maximum: 1500 characters, You have <span id="TermsCondition_countdown">1500</span> characters left.', 'woothemes' ), 
								'default' => '',
								'maxlength' => 1500),
		   'AdditionalfeeSection' => array(
								'title' => __( '<span style="font-weight: bold;">Additional Fee Settings</span>', 'woothemes' ), 
								'type' => 'title',
								'class' => 'AdditionalfeeSection'),
		   'additionalfee_details' => array(
							'type'        => 'additionalfee_details'),
							
		   'RecurringSection' => array(
								'title' => __( '<span style="font-weight: bold;">Recurring Settings</span>', 'woothemes' ), 
								'type' => 'title',
								'class' => 'RecurringSection'),
				
		   'RecurringLabel' => array(
								'title' => __( 'Label', 'woothemes' ), 
								'type' => 'text',
								'disabled' => false,
								'description' => __( '', 'woothemes' ), 
								'default' => 'Set this as a recurring payment',
								'css' => 'maxlength:200;',
							),
				
		  'recurring_details' => array(
							'type'        => 'recurring_details'),
          
           
            );
	    }
		
		public function generate_additionalfee_details_html() {
		ob_start();
		?>
		<tr valign="top">
		<th scope="row" class="titledesc">
			
		<label for="woocommerce_clickandpledge_additionalfeeenabled">Add Additional Fee<span class="woocommerce-help-tip" data-tip="A generic way to provide your Patrons an option to be charged an additional amount to cover expenses, based on a percent or a fixed amount. Be aware you may need to add a donation amount to see the 'Additional Fee' section , even in the Form Builder."></span></label></th>
		<td>
				<label for="woocommerce_clickandpledge_additionalfeeenabled">
				<select id="woocommerce_clickandpledge_additionalfeeenabled" name="woocommerce_clickandpledge_additionalfeeenabled" >
					<option value="no" <?php  
				if((isset($this->additionalfee_details['feeenabled']) && $this->additionalfee_details['feeenabled']=='no')){
						echo "selected='selected'";
				}
				
				?>>Disabled</option>
					<option value="yes" <?php  
				if((isset($this->additionalfee_details['feeenabled']) && $this->additionalfee_details['feeenabled']=='yes')){
						echo "selected='selected'";
				}
				
				?>>Enabled - Automatic</option>
					<option value="optin" <?php  
				if((isset($this->additionalfee_details['feeenabled']) && $this->additionalfee_details['feeenabled']=='optin')){
						echo "selected='selected'";
				}
				
				?>>Enabled - Opt In</option>
				</select>
				</label><br>
				</td>
				</tr>
			
			<tr valign="top" class="traddfeeinstruction">
			<th scope="row" class="titledesc">
			

				<label for="woocommerce_clickandpledge_addfeeinstructions">Instructions<span style="color: #ff0000"><span class="woocommerce-help-tip" data-tip="Add any instructions or description of this fee you would like to provide your patron."></span></span></label>
			</th>
			<td class="forminp">
			<?php   $cnpaddfeeinstructions="";
			
						
			if(isset($this->additionalfee_details['feeinstructions']) && $this->additionalfee_details['feeinstructions']!=''){
					$cnpaddfeeinstructions = $this->additionalfee_details['feeinstructions'];
			}
			?>
			<textarea class="input-text regular-input "  name="woocommerce_clickandpledge_addfeeinstructions" id="woocommerce_clickandpledge_addfeeinstructions" rows="3" cols="20" maxlength="500"><?php if($cnpaddfeeinstructions == ""){echo "I would like to pay an additional {AdditionalFee} to cover expenses.";}else {echo $cnpaddfeeinstructions;}?></textarea><p class="description">Use {AdditionalFee} to display additional fee</p>
				
			</td>
		</tr>	
		
		<tr valign="top" class="traddfeeoptoutlbl">
			<th scope="row" class="titledesc">
			

				<label for="woocommerce_clickandpledge_addfeeoptoutlbl">Opt-Out Label<span class="woocommerce-help-tip" data-tip="Text displayed to patrons next to the checkbox allowing them to opt-out to (not to paying) an additional fee."></span><span style="color: #ff0000"></span></label>
			</th>
			<td class="forminp">
			<?php   $cnpaddfeeoptoutlbl="";
			
						
			if(isset($this->additionalfee_details['feeoptoutlbl']) && $this->additionalfee_details['feeoptoutlbl']!=''){
					$cnpaddfeeoptoutlbl = $this->additionalfee_details['feeoptoutlbl'];
			}
			?>
			<textarea class="input-text regular-input "  name="woocommerce_clickandpledge_addfeeoptoutlbl" id="woocommerce_clickandpledge_addfeeoptoutlbl" rows="3" cols="20"><?php if($cnpaddfeeoptoutlbl == ""){echo "I prefer not to pay the {AdditionalFee} at this time.";}else {echo $cnpaddfeeoptoutlbl;}?></textarea>
				<p class="description">Use {AdditionalFee} to display additional fee</p>
			</td>
		</tr>	
		<tr valign="top" class="traddfeeoptinlbl">
			<th scope="row" class="titledesc">
			

				<label for="woocommerce_clickandpledge_addfeeoptinlbl">Opt-In Label<span class="woocommerce-help-tip" data-tip="Text displayed to patrons next to the checkbox allowing them to opt-in to paying an additional fee. Only available when Additional Fee is set to Enabled Opt-In."></span><span style="color: #ff0000"></span></label>
			</th>
			<td class="forminp">
			<?php   $cnpaddfeeoptinlbl="";
			
						
			if(isset($this->additionalfee_details['feeoptinlbl']) && $this->additionalfee_details['feeoptinlbl']!=''){
					$cnpaddfeeoptinlbl = $this->additionalfee_details['feeoptinlbl'];
			}
			?>
			<textarea class="input-text regular-input "  name="woocommerce_clickandpledge_addfeeoptinlbl" id="woocommerce_clickandpledge_addfeeoptinlbl" rows="3" cols="20"><?php if($cnpaddfeeoptinlbl == ""){echo "I accept the addition of {AdditionalFee} to my total payment.";}else {echo $cnpaddfeeoptinlbl;}?></textarea>
			<p class="description">Use {AdditionalFee} to display additional fee</p>	
			</td>
		</tr>	
		
		
		<tr valign="top" class="traddfeedfltoptn">
		<th scope="row" class="titledesc">
			
		<label for="woocommerce_clickandpledge_additionalfeedfltoptn">Default Option<span class="woocommerce-help-tip" data-tip="Set the default choice for the Checkout form."></span></label></th>
		<td>
				<label for="woocommerce_clickandpledge_dfltoptn">
				<select id="woocommerce_clickandpledge_dfltoptn" name="woocommerce_clickandpledge_dfltoptn" >
				<option value="in" <?php  
				if((isset($this->additionalfee_details['feedfltoptn']) && $this->additionalfee_details['feedfltoptn']=='in')){
						echo "selected='selected'";
				}
				
				?>>Opt-In</option>
					<option value="out" <?php  
				if((isset($this->additionalfee_details['feedfltoptn']) && $this->additionalfee_details['feedfltoptn']=='out')){
						echo "selected='selected'";
				}
				
				?>>Opt-Out</option>
				
					
					
				</select>
				</label><br>
				</td>
				</tr>
		
		
			<tr valign="top" class="traddfeelbl">
			<th scope="row" class="titledesc">
			

				<label for="woocommerce_clickandpledge_addfeetitle">Item Name <span class="woocommerce-help-tip" data-tip="Text included on the receipt to explain this additional fee."></span><span style="color: #ff0000"></span></label>
			</th>
			<td class="forminp">
			<?php   $cnpaddfeetitle="";
			
						
			if(isset($this->additionalfee_details['feetitle']) && $this->additionalfee_details['feetitle']!=''){
					$cnpaddfeetitle = $this->additionalfee_details['feetitle'];
			}
			?>
			<input class="input-text regular-input " type="text" name="woocommerce_clickandpledge_addfeetitle" id="woocommerce_clickandpledge_addfeetitle" style="" value="<?php if($cnpaddfeetitle == ""){echo "Handling Fee";}else {echo $cnpaddfeetitle;}?>" placeholder="Handling Fee" maxlength="100">
				
			</td>
		</tr>
		<tr valign="top" class="trdaddfeesku">
			<th scope="row" class="titledesc">
				<label for="woocommerce_clickandpledge_addfeesku">SKU
				<span class="woocommerce-help-tip" data-tip="Separate SKU to assign to the Additional Fee."></span><span style="color: #ff0000"></span></label>
			</th>
			<td class="forminp">
			<?php   $cnpaddfeesku="";
			
						
			if(isset($this->additionalfee_details['feesku']) && $this->additionalfee_details['feesku']!=''){
					$cnpaddfeesku = $this->additionalfee_details['feesku'];
			}
			?>
			<input class="input-text regular-input " type="text" name="woocommerce_clickandpledge_addfeesku" id="woocommerce_clickandpledge_addfeesku" style="" value="<?php echo $cnpaddfeesku;?>" placeholder="" maxlength="100">
				
			</td>
		</tr>
		<tr valign="top" class="trdaddfee">
			<th scope="row" class="titledesc">
				
				<label for="woocommerce_clickandpledge_title">Additional Fee<span class="woocommerce-help-tip" data-tip="Fixed Amount, Percent Amount, or combination of both that you would like added to the total amount."></span><span style="color: #ff0000"></span></label>
			</th>
			<td class="forminp">
			<?php   $cnpfeeper=""; $cnpfeeamt="";
			
						
			if(isset($this->additionalfee_details['feeamt']) && $this->additionalfee_details['feeamt']!=''){
					$cnpfeeamt = $this->additionalfee_details['feeamt'];
			}
			if(isset($this->additionalfee_details['feeper']) && $this->additionalfee_details['feeper']!=''){
					$cnpfeeper = $this->additionalfee_details['feeper'];
			}
			?>
			<input class="input-text regular-input " type="text" name="woocommerce_clickandpledge_addfeeper" id="woocommerce_clickandpledge_addfeeper" style="" value="<?php echo $cnpfeeper;?>" size="10" maxlength="8"><span style="line-height: 2;">% +  
			<?php echo get_woocommerce_currency_symbol();?>   </span>
			<input class="input-text regular-input " type="text" name="woocommerce_clickandpledge_addfeeamt" id="woocommerce_clickandpledge_addfeeamt" style="" value="<?php echo $cnpfeeamt;?>" size="10" maxlength="8">
				
			</td>
		</tr>
		<tr valign="top" class="traddfeetax">
			<th scope="row" class="titledesc">
				
				<label for="woocommerce_clickandpledge_addfeetax">Tax Deductible <span class="woocommerce-help-tip" data-tip="The percentage of the Additional Fee that is tax deductible."></span><span style="color: #ff0000"></span></label>
			</th>
			<td class="forminp">
			<?php   $cnpaddfeetax="";
			
						
			if(isset($this->additionalfee_details['feetax']) && $this->additionalfee_details['feetax']!=''){
					$cnpaddfeetax = $this->additionalfee_details['feetax'];
			}
			?>
			<input class="input-text regular-input " type="text" name="woocommerce_clickandpledge_addfeetax" id="woocommerce_clickandpledge_addfeetax" style="" value="<?php echo $cnpaddfeetax;?>" size="10" maxlength="8"><span style="line-height: 2;"> %</span>
				
			</td>
		</tr>
		<?php
		return ob_get_clean();

	}
	
		public function generate_testmode_details_html() {
		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'API Mode', 'woocommerce' ); ?>:</th>
			<td class="forminp" id="cnp_apimode">
				<table  cellspacing="0">
					
					<tbody class="accounts">
						<?php
							echo '<tr class="account">									
									<td style="padding:2px;"><input type="radio" value="yes" name="woocommerce_clickandpledge_testmode" id="woocommerce_clickandpledge_testmode" '.checked($this->testmode, 'yes',false ).'/>Test Mode</td>
									<td><input type="radio" value="no" name="woocommerce_clickandpledge_testmode" id="woocommerce_clickandpledge_testmode"  '.checked( $this->testmode, 'no',false ).'/>Live Mode</td>
								  </tr>';
							
						?>
					</tbody>
					
				</table>
				
			</td>
		</tr>
		<?php
		return ob_get_clean();

	}
	 public function generate_zeropayment_details_html()
		{
			 ob_start();
    ?>
            <tr valign="top" class="clsacrds">
			<th scope="row" class="zerotitledesc"><?php  _e( '<span style="font-weight: bold;">Allow for $0 Transaction</span>', 'woocommerce' ); ?></th>
			<td class="forminps" id="cnp_zerodtl" style="padding: 15px 0px !important;">
				<table  cellspacing="0">
					<tbody>
					<tr>
					<td valign="top">
					<div><input type="checkbox" id="woocommerce_clickandpledge_zerocreditcard" name="woocommerce_clickandpledge_zerocreditcard" value="zerocc" <?php echo checked($this->zeropaymentsettings_details['zerocreditcard'],'zerocc',false )?> class="rectyp"> Use Credit Card for pre-authorization</div>	
					<div style="text-align: center;vertical-align: middle;line-height: 50px;"><input type="checkbox" id="woocommerce_clickandpledge_zerocustom" name="woocommerce_clickandpledge_zerocustom" value="zerocp"  <?php echo checked($this->zeropaymentsettings_details['zerocustom'],'zerocp',false );?> class="rectyp"> Use Custom Payment with label: <input type="text" name="woocommerce_clickandpledge_zerocustompaynt" id="woocommerce_clickandpledge_zerocustompaynt"  placeholder='Free'  value="<?php if(esc_attr( $this->zeropaymentsettings_details['zerocustompaynt'] ) == ""){ echo "Free";}else{ echo esc_attr( $this->zeropaymentsettings_details['zerocustompaynt']);}  ?>" size="20" style="text-align: left;
vertical-align: middle;
line-height: 9px;"/> </div>
                   
					</td></tr>
                    </tbody></table></td></tr>
			
  <?php return ob_get_clean(); }
	
	 public function generate_acceptedcreditcards_details_html() {
		ob_start();
		?>
		<tr valign="top" class="clsacptcrds">
			<th scope="row" class="titledesc"><?php // _e( 'Accepted Credit Cards', 'woocommerce' ); ?></th>
			<td  id="cnp_cards">
				<table  cellspacing="0">
					
					<tbody class="accounts">
						<?php
							echo '<tr class="account" >								
									<td style="padding:2px;"><strong>Accepted Credit Cards</strong></td></tr>
							<tr class="account" >								
									<td style="padding:2px;"><br><input type="Checkbox"  name="woocommerce_clickandpledge_Visa" id="woocommerce_clickandpledge_Visa" '. checked($this->available_cards['Visa'], 'Visa',false ).' value="Visa"/>Visa</td></tr><tr>
									<td style="padding:2px;"><input type="Checkbox"  name="woocommerce_clickandpledge_American_Express" id="woocommerce_clickandpledge_American_Express"  '.checked( $this->available_cards['American Express'], 'American Express',false ).' value="American Express"/>American Express</td>
								  </tr>
								  <tr>
									<td style="padding:2px;"><input type="Checkbox"  name="woocommerce_clickandpledge_Discover" id="woocommerce_clickandpledge_Discover"  '.checked( $this->available_cards['Discover'], 'Discover',false ).' value="Discover"/>Discover</td>
								  </tr>
								  <tr>
									<td style="padding:2px;"><input type="Checkbox"  name="woocommerce_clickandpledge_MasterCard" id="woocommerce_clickandpledge_MasterCard"  '.checked( $this->available_cards['MasterCard'], 'MasterCard',false ).' value="MasterCard"/>MasterCard</td>
								  </tr>
								  <tr>
									<td style="padding:2px;"><input type="Checkbox"  name="woocommerce_clickandpledge_JCB" id="woocommerce_clickandpledge_JCB"  '.checked($this->available_cards['JCB'], 'JCB',false ).' value="JCB"/>JCB</td>
								  </tr>';
							
						?>
						
					</tbody>
					
				</table>
				
			</td>
		</tr>

		<?php
		return ob_get_clean();
   }
	public function get_CnPaccountslist()
	{
			global $wpdb;
			$data['cnpaccounts'] = array();
			$query = "SELECT * FROM " . $wpdb->prefix . "cnp_wp_wccnpaccountsinfo";
			$results = $wpdb->get_results($query, ARRAY_A);
			$count = sizeof($results);
			for($i=0; $i<$count; $i++){
				$data['cnpaccounts'][] = array(
				'AccountId'      => $results[$i]['cnpaccountsinfo_orgid'],
				'GUID'           => $results[$i]['cnpaccountsinfo_accountguid'],
				'Organization'   => $results[$i]['cnpaccountsinfo_orgname']    
			);

			}
		
		return $data['cnpaccounts'];
	}
		public function getwcCnPAccountGUID($accid)
		{
			global $wpdb;
			$cnpAccountGUId ="";
			 $query = "SELECT * FROM " . $wpdb->prefix . "cnp_wp_wccnpaccountsinfo where cnpaccountsinfo_orgid ='".$accid."'";
		    $result = $wpdb->get_results($query, ARRAY_A);
			$count = sizeof($result);
				for($i=0; $i<$count; $i++){
				$cnpAccountGUId      = $result[$i]['cnpaccountsinfo_accountguid'];
				}
			 
			return $cnpAccountGUId;
		
		}
    public function getwcCnPCurrency($accid)
		{
			global $wpdb;
			$cnpAccountGUId ="";
			 $query = "SELECT * FROM " . $wpdb->prefix . "cnp_wp_wccnpaccountsinfo where cnpaccountsinfo_orgid ='".$accid."'";
		    $result = $wpdb->get_results($query, ARRAY_A);
			$count = sizeof($result);
				for($i=0; $i<$count; $i++){
				$cnpAccountGUId      = $result[$i]['cnpaccountsinfo_cnpcurrency'];
				}
			 
			return $cnpAccountGUId;
		
		}
		public function getwcCnPAccountName($accid)
		{
			global $wpdb;
			$cnpAccountGUId ="";
			 $query = "SELECT * FROM " . $wpdb->prefix . "cnp_wp_wccnpaccountsinfo where cnpaccountsinfo_orgid ='".$accid."'";
		    $result = $wpdb->get_results($query, ARRAY_A);
			$count = sizeof($result);
				for($i=0; $i<$count; $i++){
				$cnpAccountGUId      = $result[$i]['cnpaccountsinfo_userid'];
				}
			 
			return $cnpAccountGUId;
		
		}
	public function getwcCnPConnectCampaigns($cnpaccid)
	{

		$cnpacountid = $cnpaccid;
	    $cnpaccountGUID = $this->getwcCnPAccountGUID($cnpacountid);
		$cnpUID = "14059359-D8E8-41C3-B628-E7E030537905";
		$cnpKey = "5DC1B75A-7EFA-4C01-BDCD-E02C536313A3";
		$connect  = array('soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 0);
	    $client   = new SoapClient('https://resources.connect.clickandpledge.com/wordpress/Auth2.wsdl', $connect);
		if( isset($cnpacountid) && $cnpacountid !="" && isset($cnpaccountGUID) &&  $cnpaccountGUID !="")
		{ 
			$xmlr  = new SimpleXMLElement("<GetActiveCampaignList2></GetActiveCampaignList2>");
			$cnpsel ="";
			$xmlr->addChild('accountId', $cnpacountid);
			$xmlr->addChild('AccountGUID', $cnpaccountGUID);
			$xmlr->addChild('username', $cnpUID);
			$xmlr->addChild('password', $cnpKey);
			$response = $client->GetActiveCampaignList2($xmlr); 
			$responsearr =  $response->GetActiveCampaignList2Result->connectCampaign;
         $cnpforderRes = [];
 if( !is_array($responsearr)){
      $cnpforderRes[$responsearr->alias] = $responsearr->name;
    }
    else {
      foreach ($responsearr as $obj) {
        $cnpforderRes[$obj->alias] = $obj->name;
      }
    }
    ksort($cnpforderRes);
        natcasesort($cnpforderRes);
			if(isset($this->settings['ConnectCampaignAlias']) && $this->settings['ConnectCampaignAlias'] != '')
			{
				 $cnpcampaignalias 	 =  $this->settings['ConnectCampaignAlias'];
			}
			if(isset($this->paymentsettings_details['ConnectCampaignAlias']) && $this->paymentsettings_details['ConnectCampaignAlias'] != '')
			{
				   $cnpcampaignalias 	 =  $this->paymentsettings_details['ConnectCampaignAlias'];	
			}
	         $cnparrcnt = is_array($responsearr) ? count($responsearr) : 0 ;

			 $camrtrnval = "<option value=''>Select Campaign Name</option>";
			/* if(isset($responsearr->alias) && $cnparrcnt == 0)
				{
					if($responsearr->alias == $cnpcampaignalias){ $cnpsel ="selected='selected'";}
				 $camrtrnval.= "<option value='".$responsearr->alias."' ".$cnpsel." >".$responsearr->name." (".$responsearr->alias.")</option>";
				}else{
					for($inc = 0 ; $inc < count($responsearr);$inc++)
					{ if($responsearr[$inc]->alias == $cnpcampaignalias){ $cnpsel ="selected='selected'";}else{$cnpsel ="";}
					 $camrtrnval .= "<option value='".$responsearr[$inc]->alias."' ".$cnpsel.">".$responsearr[$inc]->name." (".$responsearr[$inc]->alias.")</option>";
					}

				}	*/
      
        foreach ($cnpforderRes as $cnpkey => $cnpvalue)
          {$cnpsel="";
       			 if($cnpkey == $cnpcampaignalias){ $cnpsel ="selected='selected'"; }
           $camrtrnval .= "<option value='" . $cnpkey . "'".$cnpsel.">" .$cnpvalue." (".$cnpkey.")</option>";
      }
				}
		
		return $camrtrnval;
			
		}
	public function getWCCnPactivePaymentList($cnpaccid)
	{

		global $wpdb;
		$cmpacntacptdcards = "";
		$cnpacountid = $cnpaccid;
		$cnpaccountGUID = $this->getwcCnPAccountGUID($cnpacountid);
		$cnpUID = "14059359-D8E8-41C3-B628-E7E030537905";
		$cnpKey = "5DC1B75A-7EFA-4C01-BDCD-E02C536313A3";
		$connect1  = array('soap_version' => SOAP_1_1, 'trace' => 1, 'exceptions' => 0);
	    $client1   = new SoapClient('https://resources.connect.clickandpledge.com/wordpress/Auth2.wsdl', $connect1);
		if( isset($cnpacountid) && $cnpacountid !="" && isset($cnpaccountGUID) &&  $cnpaccountGUID !="")
		{ 
			$xmlr1  = new SimpleXMLElement("<GetAccountDetail></GetAccountDetail>");
			$xmlr1->addChild('accountId',$cnpacountid);
			$xmlr1->addChild('accountGUID',$cnpaccountGUID);
			$xmlr1->addChild('username',$cnpUID);
			$xmlr1->addChild('password',$cnpKey);
			$response1                    =  $client1->GetAccountDetail($xmlr1);
			$responsearramex              =  $response1->GetAccountDetailResult->Amex;
			$responsearrJcb               =  $response1->GetAccountDetailResult->Jcb;
			$responsearrMaster            =  $response1->GetAccountDetailResult->Master;
			$responsearrVisa              =  $response1->GetAccountDetailResult->Visa;
			$responsearrDiscover          =  $response1->GetAccountDetailResult->Discover;
			$responsearrecheck            =  $response1->GetAccountDetailResult->Ach;
			$responsearrCustomPaymentType =  $response1->GetAccountDetailResult->CustomPaymentType;
			
			if(isset($this->settings['American_Express']) && $this->settings['American_Express'] != '')
			    {
				 $cnpamex 	 =  $this->settings['American_Express'];
				}
				if(isset($this->paymentsettings_details['American_Express']) && $this->paymentsettings_details['American_Express'] != '')
			    {
				   $cnpamex 	 =  $this->paymentsettings_details['American_Express'];	
				}
				if(isset($this->settings['CustomPayment']) && $this->settings['CustomPayment'] != '')
				{
				  $cnpcp 	 =  $this->settings['CustomPayment'];
				}
			
				if(isset($this->paymentsettings_details['AccountID']) && 
				   $this->paymentsettings_details['AccountID'] != '')
				{ 
					
			        $cnpcp 	 =  $this->paymentsettings_details['CustomPayment'];	
				}
				
				if(isset($this->settings['JCB']) && $this->settings['JCB'] != '')
				{
				  $cnpjcb 	 =  $this->settings['JCB'];
				}
				if(isset($this->paymentsettings_details['JCB']) && $this->paymentsettings_details['JCB'] != '')
				{
			      $cnpjcb 	 =  $this->paymentsettings_details['JCB'];	
				}
				if(isset($this->settings['MasterCard']) && $this->settings['MasterCard'] != '')
				{
				  $cnpMaster 	 =  $this->settings['MasterCard'];
				}
				if(isset($this->paymentsettings_details['MasterCard']) && $this->paymentsettings_details['MasterCard'] != '')
				{
			      $cnpMaster 	 =  $this->paymentsettings_details['MasterCard'];	
				}
			
				if(isset($this->settings['Visa']) && $this->settings['Visa'] != '')
				{
				  $cnpVisa 	 =  $this->settings['Visa'];
				}
				if(isset($this->paymentsettings_details['Visa']) && $this->paymentsettings_details['Visa'] != '')
				{
			      $cnpVisa 	 =  $this->paymentsettings_details['Visa'];	
				}
				if(isset($this->settings['Discover']) && $this->settings['Discover'] != '')
				{
				  $cnpDiscover 	 =  $this->settings['Discover'];
				}
				if(isset($this->paymentsettings_details['Discover']) && $this->paymentsettings_details['Discover'] != '')
				{
			      $cnpDiscover 	 =  $this->paymentsettings_details['Discover'];	
				}
				if(isset($this->settings['eCheck']) && $this->settings['eCheck'] != '')
				{
				  $cnpecheck 	 =  $this->settings['eCheck'];
				}
				if(isset($this->paymentsettings_details['eCheck']) && $this->paymentsettings_details['eCheck'] != '')
				{
			      $cnpecheck 	 =  $this->paymentsettings_details['eCheck'];	
				}
				if(isset($this->settings['CreditCard']) && $this->settings['CreditCard'] != '')
				{
				  $cnpcc 	 =  $this->settings['CreditCard'];
				}
				if(isset($this->paymentsettings_details['CreditCard']) && $this->paymentsettings_details['CreditCard'] != '')
				{
			      $cnpcc 	 =  $this->paymentsettings_details['CreditCard'];	
				}
				
			$cmpacntacptdcards .= '<input type="hidden" name="woocommerce_clickandpledge_CreditCard" id="woocommerce_clickandpledge_CreditCard"';
			if($responsearramex == true || $responsearrJcb == true || $responsearrMaster== true || $responsearrVisa ==true || $responsearrDiscover == true ){ 
				$cmpacntacptdcards .= ' value="CreditCard">';
			}else{ $cmpacntacptdcards .= ' value="">'; }
				$cmpacntacptdcards .= '<input type="hidden" name="woocommerce_clickandpledge_eCheck" id="woocommerce_clickandpledge_eCheck"';
			if($responsearrecheck == true){
				$cmpacntacptdcards .= ' value="eCheck">';
			}else{ $cmpacntacptdcards .= ' value="">'; }
			if($responsearramex == true){
			$cmpacntacptdcards .= '<input type="hidden" name="woocommerce_clickandpledge_American_Express" id="woocommerce_clickandpledge_American_Express" value="amex">';
			}
			if($responsearrJcb == true){
			$cmpacntacptdcards .= '<input type="hidden" name="woocommerce_clickandpledge_JCB" id="woocommerce_clickandpledge_JCB" value="jcb">';
			}
			if($responsearrMaster == true){
			$cmpacntacptdcards .= '<input type="hidden" name="woocommerce_clickandpledge_MasterCard" id="woocommerce_clickandpledge_MasterCard" value="Master">';
			}
			if($responsearrVisa == true){
			$cmpacntacptdcards .= '<input type="hidden" name="woocommerce_clickandpledge_Visa" id="woocommerce_clickandpledge_Visa" value="Visa">';
			}
			if($responsearrDiscover == true){
			$cmpacntacptdcards .= '<input type="hidden" name="woocommerce_clickandpledge_Discover" id="woocommerce_clickandpledge_Discover" value="Discover">';
			}
			$cmpacntacptdcards .= '<table cellpadding="5" cellspacing="3" style="font-weight:bold;padding:2px;" id="tblacceptedcards">
                    <tbody><tr>
                    <td width="200"><input type="checkbox" id="woocommerce_clickandpledge_CreditCard" class="checkbox_active" value="CreditCard" name="woocommerce_clickandpledge_CreditCard"  onclick="block_creditcard(this.checked);" ';
			if(($responsearramex == true || $responsearrJcb == true || $responsearrMaster== true || $responsearrVisa ==true || $responsearrDiscover == true) )
			{$cmpacntacptdcards .= 'checked="checked"';}
		     $cmpacntacptdcards .= 'checked="checked" disabled="disabled"> Credit Card</td></tr>
			 <tr class="tracceptedcards"><td></td><td>
			 <table cellspacing="0">
					
					<tbody class="accounts">
						<tr class="account">								
									<td style="padding:2px;"><strong>Accepted Credit Cards</strong></td></tr>';
								if($responsearrVisa == true){
									
							      $cmpacntacptdcards .= '<tr class="account">								
									<td style="padding:2px;"><br><input type="Checkbox" name="payment_cnp_Visa" id="payment_cnp_Visa"';
									if(isset($cnpVisa)){ $cmpacntacptdcards .='checked="checked "'; }
									 $cmpacntacptdcards .= 'value="Visa" checked="checked" disabled="disabled">Visa</td></tr>';
								  }
								if($responsearramex == true){
									$cmpacntacptdcards .= '<tr>
									<td style="padding:2px;"><input type="Checkbox" name="payment_cnp_American_Express" id="payment_cnp_American_Express"';
									if(isset($cnpamex)){ $cmpacntacptdcards .='checked="checked"'; }
									$cmpacntacptdcards .= 'value="American Express" checked="checked" disabled="disabled">American Express</td>
								  </tr>';
								}if($responsearrDiscover == true){
								 $cmpacntacptdcards .= ' <tr>
									<td style="padding:2px;"><input type="Checkbox" name="payment_cnp_Discover" id="payment_cnp_Discover"'; 
									if(isset($cnpDiscover)){ $cmpacntacptdcards .='checked="checked"'; }
										$cmpacntacptdcards .= ' value="Discover" checked="checked" disabled="disabled">Discover</td>
								  </tr>';
								}if($responsearrMaster == true){
								  $cmpacntacptdcards .= '<tr>
									<td style="padding:2px;"><input type="Checkbox" name="payment_cnp_MasterCard" id="payment_cnp_MasterCard"';
									if(isset($cnpMaster)){ $cmpacntacptdcards .='checked="checked"'; }
									$cmpacntacptdcards .= ' value="MasterCard"  checked="checked" disabled="disabled">MasterCard</td>
								  </tr>';
								}if($responsearrJcb == true){
								  $cmpacntacptdcards .= '<tr>
									<td style="padding:2px;"><input type="Checkbox" name="payment_cnp_JCB" id="payment_cnp_JCB"';
									if(isset($cnpjcb)){ $cmpacntacptdcards .='checked="checked"'; }
									$cmpacntacptdcards .= ' value="JCB" checked="checked" disabled="disabled">JCB</td>
								  </tr>';
								}
			$cmpacntacptdcards .= '</tbody></table></td></tr>';
			
			if($responsearrecheck == true){
			$cmpacntacptdcards .='<tr><td><input type="checkbox" value="eCheck" id="woocommerce_clickandpledge_eCheck" class="checkbox_active" name="woocommerce_clickandpledge_eCheck" onclick="block_echek(this.checked);"';
				if(isset($cnpecheck)){ $cmpacntacptdcards .='checked="checked"'; }
				 $cmpacntacptdcards .= ' checked="checked" disabled="disabled"> eCheck</td></tr>';
			}
			if($responsearrCustomPaymentType == true){
			$cmpacntacptdcards .='<tr><td><input type="checkbox" value="Purchase Order" id="woocommerce_clickandpledge_CustomPayment" class="checkbox_active" name="woocommerce_clickandpledge_CustomPayment"';
				if(isset($cnpcp) ){ $cmpacntacptdcards .='checked="checked"'; }
				/*if($responsearrCustomPaymentType == true && (!isset($cnpcp) ))
				{$cmpacntacptdcards .='checked="checked"';}	*/
				 $cmpacntacptdcards .= '> Custom Payment</td></tr>';
			}
        
					$cmpacntacptdcards .= '</tbody></table><script>jQuery("#woocommerce_clickandpledge_CustomPayment").click(function() {
				admdefaultpayment();
			});</script>';

		}	
		
		return $cmpacntacptdcards;
		wp_exit();
	}
   
		public function generate_paymentsettings_details_html()
		{
			 ob_start();
			 global $wpdb;
			 $accountstable_name =$wpdb->prefix . 'cnp_wp_wccnpaccountsinfo';
			 $cnpsqlst= "SELECT count(*)  FROM ". $accountstable_name;
			 $rowcount = $wpdb->get_var( $cnpsqlst );
    ?>
	<div><ul class="subsubsub"><li><a href="#" class="cnpregister">Register</a>
	
	<?php if ($rowcount !=0) {
	
		$cnpacountid = $this->paymentsettings_details['AccountID'];
		$cnpaccountwcname = $this->getwcCnPAccountName($cnpacountid);
		?>| </li><li><a href="#" class="cnpsettings">Settings</a> <strong>[ Logged as: <?php echo $cnpaccountwcname;?> ] </strong></li>
	<style>.div-table {
  display: table;         
  width: auto;         
  background-color: #eee;         
  border: 0px solid #666666;         
  border-spacing: 5px; /* cellspacing:poor IE support for  this */
}
.div-table-row {
  display: table-row;
  width: auto;
  clear: both;
}
.div-table-col {
  float: left; /* fix for  buggy browsers */
  display: table-column;         
  width: 200px;         
  background-color: #ccc;  
}</style>
	<?php }?></ul></div>
		<div id="cnpfrmwcregister">
		<div class="tab-content" id="cnpfrmwcregister">
		<br><hr/>
		<h2><p>Login</p><hr/></h2>
		<div id="content" class="col-sm-12 div-table">
     
      <p>1. Enter the email address associated with your Click & Pledge account, and click on <strong>Get the Code</strong>.</p>
	  <p>2. Please check your email inbox for the Login Verification Code email.</p>
	  <p>3. Enter the provided code and click <strong>Login</strong>.</p>
	  
				<div class="form-group required div-table-row">
							
							<div class="col-sm-10">
								<input type="textbox" id="cnp_emailid" placeholder="CONNECT User Name" name="cnp_emailid" maxlength="50" min="6" size="30" >
							</div>
						
						</div>
						<div class="form-group required cnploaderimage div-table-row" style="display:none">
							
							<div class="col-sm-10">
							
				<img src='<?php echo WP_PLUGIN_URL; ?>/<?php echo plugin_basename( dirname(__FILE__)) ?>/images/ajax-loader_trans.gif' title='loader' alt='loader'/>
							</div>
						</div>
						<div class="form-group required cnpcode div-table-row" style="display:none">
							
							<div class="col-sm-10">
							<input type="textbox" id="cnp_code" placeholder="Code" name="cnp_code"  size="30">
							</div>
						</div>
						<div class="form-group required div-table-row">
						
							<div class="col-sm-10">
							<input type="button" id="cnp_btncode" value="Get the code" name="cnp_btncode" >
							</div>
						</div>
						<div class="form-group required cnperror div-table-row" style="display:none">
						
							<div class="col-sm-10">
							<span class="text-danger" style="color:#841a09">Sorry but we cannot find the email in our system. Please try again.</span>
							<span class="text-success" style="color:#008000"></span>
							</div>
						</div>
			 </div></div>
		</div>
		<div id="cnpfrmwcsettings">
		<div class="tab-content" id="cnpfrmwcsettings">
		<br><hr/>
		<div id="content" class="col-sm-12">
		
		<tr valign="top">
			<th scope="row" class="titledesc">
			<label for="woocommerce_clickandpledge_enabled">Status</label>
</th>
			<?php   $cnpenabled="";
				if((isset($this->enabled) && $this->enabled == 'yes')){
						$cnpenabled="checked='checked'";
				}
			
				if((isset($this->paymentsettings_details['enabled']) && $this->paymentsettings_details['enabled']=='yes')){
						$cnpenabled="checked='checked'";
				}
				
				?><td>
				<label for="woocommerce_clickandpledge_enabled">
				<input class="" type="checkbox" name="woocommerce_clickandpledge_enabled" id="woocommerce_clickandpledge_enabled" <?php echo $cnpenabled;?> value="yes"> Enable Click & Pledge</label><br>
				</td>
								</tr>
		
		<tr valign="top">
			<th scope="row" class="titledesc">

				<label for="woocommerce_clickandpledge_title">Title <span style="color: #ff0000">*</span></label>
			</th>
			<td class="forminp">
			<?php   $cnptitle="";
			
			if(isset($this->title) && $this->title != '')
			{
				$cnptitle = stripslashes_deep( $this->title);
			}				
			if(isset($this->paymentsettings_details['title']) && $this->paymentsettings_details['title']!=''){
					$cnptitle = stripslashes_deep($this->paymentsettings_details['title']);
			}
			?>
			<input class="input-text regular-input " type="text" name="woocommerce_clickandpledge_title" id="woocommerce_clickandpledge_title" style="" value="<?php echo $cnptitle;?>" placeholder="" maxlength="500">
				
			</td>
		</tr>
			<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="woocommerce_clickandpledge_description">Description</label>
			</th>
			<td class="forminp">
			<?php   $cnpdesc="";
			
			if(isset($this->description) && $this->description != '')
			{
				$cnpdesc = stripslashes_deep($this->description);
			}				
			if(isset($this->paymentsettings_details['description']) && $this->paymentsettings_details['description']!=''){
					$cnpdesc = stripslashes_deep($this->paymentsettings_details['description']);
			}
			?>
			<input class="input-text regular-input " type="text" name="woocommerce_clickandpledge_description" id="woocommerce_clickandpledge_description" style="" value="<?php echo $cnpdesc;?>" placeholder="" maxlength="500">
			</td>
		</tr>
			<tr valign="top">
			<th scope="row" class="titledesc">
			<label for="woocommerce_clickandpledge_AccountID">C&P Account ID <span style="color: #ff0000">*</span></label>
			</th>
			<td class="forminp"><?php 
            $cnptransactios=$this->get_CnPaccountslist();  
        $keys = array_column($cnptransactios, 'AccountId');
		array_multisort(array_map(function($element) {
      return $element['AccountId'];
  }, $cnptransactios), SORT_ASC, $cnptransactios);
       
        ?>
		<select name="woocommerce_clickandpledge_AccountID" id="woocommerce_clickandpledge_AccountID" class="input-text regular-input required" >
		<?php 
           
            
        natcasesort($cnptransactios);
        foreach($cnptransactios as $cnpacnts){
			if($cnpacnts['AccountId'] == $this->AccountID){
				 $found = true;
				 $cnpactiveuser = $cnpacnts['AccountId'];
			}?>
		<option value=<?php echo $cnpacnts['AccountId'];?> <?php if($cnpacnts['AccountId'] == $this->AccountID){echo "selected";} ?>><?php echo $cnpacnts['AccountId'];?> [<?php echo stripslashes($cnpacnts['Organization']);?>] </option>
		<?php }
			?>
		</select> <a href="#" id="rfrshtokens">Refresh Accounts </a>
		</td>
		    </tr>
            <tr valign="top">
			<th scope="row" class="titledesc">
			<label for="woocommerce_clickandpledge_AccountID">C&P Account Currency<span style="color: #ff0000"></span></label>
			</th>
            <td class="cnpcurr"> <?php    $cnpcurs = $this->getwcCnPCurrency($this->AccountID);
        if($cnpcurs == 840) {$cnpcurrvals = "USD";}elseif($cnpcurs == 978) {$cnpcurrvals = "EURO";}elseif($cnpcurs == 826) {$cnpcurrvals = "POUND";}else {$cnpcurrvals = "CAD";}
 echo $cnpcurrvals;?></td></tr>
		    <?php  if(!isset($found)) {$cnpactiveuser = $cnptransactios[0]['AccountId'];}
       ?>
			<tr valign="top">
			<th scope="row" class="titledesc">Mode:</th>
			<td class="forminp" id="cnp_apimode">
			<table cellspacing="0">
			<tbody class="accounts">
			<tr class="account">
			<?php 
			$cnptestmode="";
			$cnplivemode="";
				if(isset($this->testmode) && $this->testmode != '')
			    {
				 $cnptestmode = checked($this->testmode, 'yes',false )	;
				 $cnplivemode = checked($this->testmode, 'no',false )	;	
				}
				if(isset($this->paymentsettings_details['AccountId']) && $this->paymentsettings_details['AccountId'] != '')
			    {
				 $cnptestmode = checked($this->paymentsettings_details['testmode'], 'yes',false )	;
				 $cnplivemode = checked($this->paymentsettings_details['testmode'], 'no',false )	;	
				}
			?>									
			<td style="padding:2px;"><input type="radio" value="yes" name="woocommerce_clickandpledge_testmode" id="woocommerce_clickandpledge_testmode" <?php echo $cnptestmode; ?>>Test Mode</td>
			<td><input type="radio" value="no" name="woocommerce_clickandpledge_testmode" id="woocommerce_clickandpledge_testmode" <?php echo $cnplivemode; ?>>Live Mode</td>
			</tr></tbody>
					
				</table>
			</td>
		</tr>
			
			
			<tr valign="top">
			<th scope="row" class="titledesc">
							
			<label for="woocommerce_clickandpledge_ConnectCampaignAlias">CONNECT Campaign URL Alias <span class="woocommerce-help-tip" data-tip="Transaction will post to this Connect campaign.  Receipts, Stats are sent and updated based on the set campaign."></span></label>
			</th>
			<td class="forminp">
			<?php 
			 $cnpaccntid = $this->settings['AccountID'];
			$cnpconnectcampaign=$this->getwcCnPConnectCampaigns($cnpactiveuser);?>
			<select name="woocommerce_clickandpledge_ConnectCampaignAlias" id="woocommerce_clickandpledge_ConnectCampaignAlias" class="input-text regular-input required" >
			<?php echo $cnpconnectcampaign; ?>
		</select>
		</td>
		</tr>
		<tr><td class="wc-settings-sub-title" style="vertical-align: top;" ><label><span style="font-weight: bold;">Payment Methods</span></label></td><td id="cnpacceptedcards">	<?php $cnpactivepaymnts=$this->getWCCnPactivePaymentList($cnpactiveuser);
			echo $cnpactivepaymnts;?></td></tr>
           
            <tr><td colspan=2><table style="padding-left:124px;" width="100%" cellspacing="0">
	<tr valign="top"><?php //print_r($this->paymentsettings_details);?>
			<th scope="row" class="titledesc">
				<label for="woocommerce_clickandpledge_CustomPayment_Titles"><span style="padding-left:139px">Title(s) <span style="color: #ff0000">*</span></span> </label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><span style="padding-left:139px">Title(s) <span style="color: #ff0000">*</span></span></span></legend>
					<textarea rows="3" cols="20" class="input-text wide-input " type="textarea" name="woocommerce_clickandpledge_CustomPayment_Titles" id="woocommerce_clickandpledge_CustomPayment_Titles" style="" placeholder=""><?php echo $this->paymentsettings_details['CustomPayment_Titles'];?></textarea>
					<p class="description">Separate with semicolon (;)</p>
				</fieldset>
			</td>
		</tr>
                <tr valign="top">
			<th scope="row" class="titledesc">
				<label for="woocommerce_clickandpledge_ReferenceNumber_Label"><span style="padding-left:41px">Reference Number Label</span> </label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><span style="padding-left:41px">Reference Number Label</span></span></legend>
					<input class="input-text regular-input " type="text" name="woocommerce_clickandpledge_ReferenceNumber_Label" id="woocommerce_clickandpledge_ReferenceNumber_Label" style="" value="<?php echo $this->paymentsettings_details['ReferenceNumber_Label'];?>" placeholder="">
									</fieldset>
			</td>
		</tr></td></tr></table>
			</div></div>
	</div>
		<script>
			jQuery(document).ready(function(){
			<?php if ($rowcount > 0){
            ?>
			jQuery("#cnpfrmwcregister").hide();
			jQuery("#cnpfrmwcsettings").show();
			jQuery('.form-table').show();
			jQuery('.ReceiptSettingsSection').show();
			jQuery('.AdditionalfeeSection').show();
            jQuery('.ZeroSettingsSection').show();
          	jQuery('.RecurringSection').show();
			jQuery('.woocommerce-save-button').show();
			jQuery('.cnpsettings').removeClass('active');
			
			<?php } else {?>
				
			jQuery("#cnpfrmwcregister").show();
			jQuery('#cnpfrmwcsettings').hide();
			jQuery('.form-table').hide();
			jQuery('.ReceiptSettingsSection').hide();
			jQuery('.AdditionalfeeSection').hide();
            jQuery('.ZeroSettingsSection').hide();
			jQuery('.RecurringSection').hide();
			jQuery('.woocommerce-save-button').hide();
			jQuery('.cnpregister').removeClass('active');
			<?php }?>
				
			jQuery('.cnpregister').click(function(){
			jQuery("#cnpfrmwcregister").show();
			jQuery('#cnpfrmwcsettings').hide();
			jQuery('.form-table').hide();
			jQuery('.ReceiptSettingsSection').hide();
            jQuery('.ZeroSettingsSection').hide();
			jQuery('.AdditionalfeeSection').hide();
			jQuery('.RecurringSection').hide();
			jQuery('.woocommerce-save-button').hide();
			jQuery('.cnpregister').removeClass('active');
			});
				
			jQuery('.cnpsettings').click(function(){ 
          	jQuery("#cnpfrmwcregister").hide();
			jQuery("#cnpfrmwcsettings").show();
			jQuery('.form-table').show();
			jQuery('.ReceiptSettingsSection').show();
            jQuery('.ZeroSettingsSection').show();
			jQuery('.AdditionalfeeSection').show();
			jQuery('.RecurringSection').show();
			jQuery('.woocommerce-save-button').show();
			jQuery('.cnpsettings').removeClass('active');
			});
    });
        
        
       jQuery('#woocommerce_clickandpledge_zerocustom').click(function() {
        if(jQuery('#woocommerce_clickandpledge_zerocustom').is(':checked')) {
        alert("in");
						jQuery('#cnpcpn').show();
					
						
					
					} else {
						jQuery('#cnpcpn').hide();
					
					}
        
        });	
			jQuery('#woocommerce_clickandpledge_CustomPayment').on('change', function() {
				admdefaultpayment();
				});		
					function admdefaultpayment() { 
					var paymethods = [];
					var paymethods_titles = [];
					var str = '';
					var defaultval = jQuery('#woocommerce_clickandpledge_DefaultpaymentMethod').val();
					if(jQuery('#woocommerce_clickandpledge_CreditCard').val()!=""){						        paymethods.push('CreditCard');
						paymethods_titles.push('Credit Card');
					}
					if(jQuery('#woocommerce_clickandpledge_eCheck').val()!="") {
						paymethods.push('eCheck');
						paymethods_titles.push('eCheck');
					}
					
					if(jQuery('#woocommerce_clickandpledge_CustomPayment').is(':checked')) {
						jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').closest('tr').show();
						jQuery('#woocommerce_clickandpledge_ReferenceNumber_Label').closest('tr').show();
						
						var titles = jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').val();
						var titlesarr = titles.split(";");
						for(var j=0;j < titlesarr.length; j++)
						{
							if(titlesarr[j] !=""){
								paymethods.push(titlesarr[j]);
								paymethods_titles.push(titlesarr[j]);
							}
						}
					} else {
						jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').closest('tr').hide();
						jQuery('#woocommerce_clickandpledge_ReferenceNumber_Label').closest('tr').hide();
					}
					
					if(paymethods.length > 0) {
						for(var i = 0; i < paymethods.length; i++) {
							if(paymethods[i] == defaultval) {
							str += '<option value="'+paymethods[i]+'" selected>'+paymethods_titles[i]+'</option>';
							} else {
							str += '<option value="'+paymethods[i]+'">'+paymethods_titles[i]+'</option>';
							}
						}
					} else {
					 str = '<option selected="selected" value="">Please select</option>';
					}
					jQuery('#woocommerce_clickandpledge_DefaultpaymentMethod').html(str);
				}
			
	jQuery('#woocommerce_clickandpledge_AccountID').change(function() {
		
		var  cnpwcaccountid= jQuery('#woocommerce_clickandpledge_AccountID').val();
		
		 	 jQuery.ajax({
				  type: "POST", 
				  url: ajaxurl ,
				  data: {
						'action':'getCnPUserEmailAccountList',
					  	'cnpacid':cnpwcaccountid,
						},
				  cache: false,
				  beforeSend: function() {
					
					jQuery("#woocommerce_clickandpledge_ConnectCampaignAlias").html("<option>Loading............</option>");
					},
					complete: function() {
					
					},	
				  success: function(htmlText) {
				
				  if(htmlText !== "")
				  {
					console.log(htmlText);
					var res = htmlText.split("||");
					jQuery("#woocommerce_clickandpledge_ConnectCampaignAlias").html(res[0]);  
					jQuery("#cnpacceptedcards").html(res[1]);  
					jQuery(".cnpcurr").html(res[2]);  
                  
					  if(jQuery("#woocommerce_clickandpledge_CustomPayment").prop('checked') == true){
						
						jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').closest('tr').show();
						jQuery('#woocommerce_clickandpledge_ReferenceNumber_Label').closest('tr').show();
						  admdefaultpayment();
					  }
					  else{	
						jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').closest('tr').hide();
						jQuery('#woocommerce_clickandpledge_ReferenceNumber_Label').closest('tr').hide();

						   admdefaultpayment();}
				
				  }
				  else
				  {
				  jQuery(".cnperror").show();
				  }
					
				  },
				  error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				  }
				});
	 return false;
 });
jQuery('#rfrshtokens').on('click', function() 
		 {  	
        
			var rcnpwcaccntid = jQuery('#woocommerce_clickandpledge_AccountID').val();

		 	 jQuery.ajax({
				  type: "POST", 
				  url: ajaxurl ,
				  data: {
						'action':'getWCCnPAccountList',
					  	'rcnpwcaccountid':rcnpwcaccntid,
						},
				    cache: false,
				    beforeSend: function() {
                    jQuery("#woocommerce_clickandpledge_AccountID").html("<option>Loading............</option>");
					jQuery('.cnp_loader').show();
					
					},
					complete: function() {
						jQuery('.cnp_loader').hide();
					
					},	
				  success: function(htmlText) {
					
				  if(htmlText !== "")
				  {
					
					jQuery("#woocommerce_clickandpledge_AccountID").html(htmlText);  
					 // $(selector).trigger("change");
					   jQuery("#woocommerce_clickandpledge_AccountID").change();
				  
				  }
				  else
				  {
				  jQuery(".cnperror").show();
				  }
					
				  },
				  error: function(xhr, ajaxOptions, thrownError) {
				  alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				  }
				});
	 return false;
 });
		function validateEmail($email) {
		  var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
		  return emailReg.test( $email );
		}
		jQuery('#cnp_emailid').on('keypress', function(e) {
        if (e.which == 32)
            return false;
        });
		jQuery('#cnp_btncode').on('click', function() 
		 {  
		 	 if(jQuery('#cnp_btncode').val() == "Get the code")
			 {
			 var cnpemailid = jQuery('#cnp_emailid').val();
			//	var ajaxurl = "admin-ajax.php" 
			 if(jQuery('#cnp_emailid').val() != "" && validateEmail(cnpemailid))
			 {
				 jQuery.ajax({
				  type: "POST", 
				  url: ajaxurl ,
				  data: {
						'action':'cnp_getcode',
						'cnpemailid' : cnpemailid
					  },
					cache: false,
					beforeSend: function() {
					jQuery('.cnploaderimage').show();
					jQuery(".cnperror").hide();
					},
					complete: function() {
					jQuery('.cnploaderimage').hide();
						
					},	
				  success: function(htmlText) { 
					if(htmlText !=""){
				  var htmlText = jQuery.parseJSON(htmlText);}
				
				  if(htmlText == "Code has been sent successfully")
				  {
					  jQuery(".cnpcode").show();
					  jQuery("#cnp_btncode").prop('value', 'Login');
					  jQuery(".text-danger").html("");
					  jQuery(".text-success").html("");
					  jQuery(".cnperror").show();
					  jQuery(".text-success").html("Please enter the code sent to your email");
				  }
				  else  
				  {
				   	jQuery(".cnperror").show();
				  }
					
				  },
				  error: function(xhr, ajaxOptions, thrownError) { 
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				  }
				});
			  }
			  else{
			  alert("Please enter valid connect user name");
			  jQuery('#cnp_emailid').focus();
			  return false;
			  }
			 }
			 if(jQuery('#cnp_btncode').val() == "Login")
			 {
			 	 var cnpemailid = jQuery('#cnp_emailid').val().trim();
				 var cnpcode    = jQuery('#cnp_code').val().trim();
				 if(cnpemailid != "" && cnpcode != "")
				 {
				 jQuery.ajax({
				  type: "POST", 
				  url: ajaxurl ,
				  data: {
						'action':'cnp_getAccounts',
						'wccnpemailid' : cnpemailid,
					  	'wccnpcode' : cnpcode
					  },
				  cache: false,
				  beforeSend: function() {
					jQuery("#cnp_btncode").prop('value', 'Loading....');
					jQuery("#cnp_btncode").prop('disabled', 'disabled');
					},
					complete: function() {
					
					},	
				  success: function(htmlText) {
				console.log(htmlText);
				  if(htmlText.trim() == "success")
				  {
				      jQuery('#cnp_emailid').val("");
					  jQuery('#cnp_code').val("");
  				  	  jQuery(".cnpcode").hide();
                   jQuery(".text-danger").html("");
					  jQuery("#cnp_btncode").prop('disabled', '');
				      jQuery("#cnp_btncode").prop('value', 'Get the code');
					  jQuery("#cnpfrmwcregister").hide();
					  jQuery("#cnpfrmwcsettings").show();
					  jQuery('.form-table').show();
					  jQuery('.ReceiptSettingsSection').show();
					  jQuery('.RecurringSection').show();
        			  jQuery('.woocommerce-save-button').show();
					  window.location.reload();
				  }
				  else
				  {
					  jQuery(".text-danger").html("");
					  jQuery(".text-success").html("");
					  jQuery(".cnperror").show();
					  jQuery(".text-danger").html("Invalid");
					  jQuery("#cnp_btncode").prop('value', 'Login');
					  jQuery("#cnp_btncode").prop('disabled', false);
				  }
					
				  },
				  error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				  }
				});
			  }
			 }
			 else if(jQuery('#cnp_emailid').val() == "")
			 {
			  alert("Please enter connect user name");
			  return false;
			 }
		 
		
		 });
</script>
		<?php
		}
    
    
	    public function generate_recurring_details_html() {
		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php  _e( 'Settings', 'woocommerce' ); ?></th>
			<td class="forminp" id="cnp_recdtl" style="padding: 15px 0px !important;">
				<table  cellspacing="0">
					<tbody>
					<tr><td valign="top">
					<label for="woocommerce_recurring_paymentoptions_label">	
					<input type="text" name="woocommerce_clickandpledge_payoptn" id="woocommerce_clickandpledge_payoptn" value='<?php if(esc_attr( $this->recurring_details['payoptn'] ) == ""){ echo "Payment options";}else{ echo esc_attr( $this->recurring_details['payoptn']);}  ?>'  placeholder='Payment options' onchange="" />
					</label>
					</td>
					<td>
					<div><input type="checkbox" id="woocommerce_clickandpledge_isRecurring_oto" name="woocommerce_clickandpledge_isRecurring_oto" value="0" <?php echo checked($this->recurring_details['isRecurring_oto'],'0',false )?> class="rectyp"> One Time Only</div>	
					<div><input type="checkbox" id="woocommerce_clickandpledge_isRecurring_recurring" name="woocommerce_clickandpledge_isRecurring_recurring" value="1"  <?php echo checked($this->recurring_details['isRecurring_recurring'],1,false );?> class="rectyp"> Recurring</div>
					</td></tr>
					
					<tr class="trdfltpymntoptn"><td><label>Default payment options </label></td><td>
					<select name="woocommerce_clickandpledge_dfltpayoptn" id="woocommerce_clickandpledge_dfltpayoptn" class="cnpselect" >
					<option value="Recurring" <?php selected( $this->recurring_details['dfltpayoptn'],'Recurring' ); ?>>Recurring</option>
					<option value="One Time Only" <?php selected( $this->recurring_details['dfltpayoptn'],'One Time Only' ); ?>>One Time Only</option>
					</select></td></tr>	
					
					<tr class="trrectyp"><td valign="top">
					<label for="gfcnp_recurring_RecurringTypes_label"><input type="text" name="woocommerce_clickandpledge_rectype" id="woocommerce_clickandpledge_rectype" 
					value='<?php if(esc_attr( $this->recurring_details['rectype'] ) == ""){ echo "Recurring types";}else{ echo esc_attr( $this->recurring_details['rectype']);}  ?>'  placeholder='Recurring types' /></label>	
					</td><td><div><input type="checkbox" id="woocommerce_clickandpledge_Installment" name="woocommerce_clickandpledge_Installment"  value="Installment" <?php echo checked($this->recurring_details['installment'],'Installment',false )?> class='clsrectype' > Installment (e.g. pay $1000 in 10 installments of $100 each)</div>
					<div><input type="checkbox" id="woocommerce_clickandpledge_Subscription" name="woocommerce_clickandpledge_Subscription" value="Subscription" class='clsrectype'  <?php echo checked($this->recurring_details['subscription'], 'Subscription',false )?>> Subscription (e.g. pay $100 every month for 12  months)</div>	
					
					</td></tr>
					
					<tr class="trdfltrecoptn" id="trdfltrecoptn"><td><label>Default Recurring type</label></td><td>
					<select name="woocommerce_clickandpledge_dfltrectypoptn" id="woocommerce_clickandpledge_dfltrectypoptn" class="cnpselect">
					<option value="Subscription" <?php selected( $this->recurring_details['dfltrectypoptn'],'Subscription' ); ?>>Subscription</option>
					<option value="Installment" <?php selected( $this->recurring_details['dfltrectypoptn'],'Installment' ); ?>>Installment</option>
					</select></td></tr>	
					<script language="javascript">
					
						jQuery('#woocommerce_clickandpledge_Subscription').click(function(){
						if(jQuery("#woocommerce_clickandpledge_Installment").is(':checked') && jQuery("#woocommerce_clickandpledge_Subscription").is(':checked'))
						{
						  jQuery("tr.trdfltrecoptn").show();
						}
						});
					</script>
					<tr class="trprdcty"><td valign="top">
					<label for="gfcnp_recurring_periodicity_label">
					<input type="text" name="woocommerce_clickandpledge_periodicity" id="woocommerce_clickandpledge_periodicity"  placeholder='Periodicity'  value="<?php if(esc_attr( $this->recurring_details['periodicity'] ) == ""){ echo "Periodicity";}else{ echo esc_attr( $this->recurring_details['periodicity']);}  ?>"/>
					</label></td>
					<td><div>
					<input type="checkbox" id="woocommerce_clickandpledge_Week" name="woocommerce_clickandpledge_Week" value="Week" <?php echo checked($this->recurring_details['week'], 'Week',false )?>> Week<br>
					<input type="checkbox" id="woocommerce_clickandpledge_2_Weeks" name="woocommerce_clickandpledge_2_Weeks" value="2 Weeks" <?php echo checked($this->recurring_details['2_weeks'], '2 Weeks',false )?>> 2 Weeks<br>
					<input type="checkbox" id="woocommerce_clickandpledge_Month" name="woocommerce_clickandpledge_Month" value="Month" <?php echo checked($this->recurring_details['month'], 'Month',false )?>> Month<br>
					<input type="checkbox" id="woocommerce_clickandpledge_2_Months" name="woocommerce_clickandpledge_2_Months" value="2 Months" <?php echo checked($this->recurring_details['2_months'], '2 Months',false )?>> 2 Months<br>
					<input type="checkbox" id="woocommerce_clickandpledge_Quarter" name="woocommerce_clickandpledge_Quarter" value="Quarter" <?php echo checked($this->recurring_details['quarter'], 'Quarter',false )?>> Quarter<br>
					<input type="checkbox" id="woocommerce_clickandpledge_6_Months" name="woocommerce_clickandpledge_6_Months" value="6 Months" <?php echo checked($this->recurring_details['6_months'], '6 Months',false )?>> 6 Months<br>
					<input type="checkbox" id="woocommerce_clickandpledge_Year" name="woocommerce_clickandpledge_Year" value="Year" <?php echo checked($this->recurring_details['year'], 'Year',false )?>> Year<br><br>
					</div></td></tr>
					<tr class="trnoofpaymnts"><td valign="top">
					<label for="woocommerce_clickandpledge_recurring_Noofpaymnts_label"><input type="text" name="woocommerce_clickandpledge_noofpayments" id="woocommerce_clickandpledge_noofpayments" value="<?php if(esc_attr( $this->recurring_details['noofpayments'] ) == ""){ echo "Number of payments";}else{ echo esc_attr( $this->recurring_details['noofpayments']);}  ?>" placeholder='Number of payments' /></label>
					</td><td><div id="indefinite_div">
					<input type="radio" class='clsnoofpaymnts' name="woocommerce_clickandpledge_indefinite" id="woocommerce_clickandpledge_indefinite" value="1" <?php echo checked($this->recurring_details['indefinite'], '1', false )?>> Indefinite Only
					</div><div id="openfild_div">
					<input type="radio" class='clsnoofpaymnts' name="woocommerce_clickandpledge_indefinite" id="woocommerce_clickandpledge_indefinite" value="openfield"  <?php echo checked($this->recurring_details['indefinite'], 'openfield',false )?>> Open Field Only
					</div><div id="indefinite_openfield_div">
					<input type="radio" class='clsnoofpaymnts' name="woocommerce_clickandpledge_indefinite"  id="woocommerce_clickandpledge_indefinite" value="indefinite_openfield"  <?php echo checked($this->recurring_details['indefinite'], 'indefinite_openfield',false )?>> Indefinite + Open Field Option</div><div id="fixdnumber_div">
					<input type="radio" class='clsnoofpaymnts' name="woocommerce_clickandpledge_indefinite"  id="woocommerce_clickandpledge_indefinite" value="fixednumber"  <?php echo checked($this->recurring_details['indefinite'], 'fixednumber',false )?>> Fixed Number - No Change Allowed</div>
				   </td></tr>
					<tr class="dfltnoofpaymnts"><td>
					<label><input type="text" name="woocommerce_clickandpledge_dfltnoofpaymentslbl" id="woocommerce_clickandpledge_dfltnoofpaymentslbl"  placeholder='Default number of payments' value="<?php if(esc_attr( $this->recurring_details['dfltnoofpaymentslbl'] ) == ""){ echo "Default number of payments";}else{ echo esc_attr( $this->recurring_details['dfltnoofpaymentslbl']);}  ?>"/></label></td>
					<td><input type="text"  id="woocommerce_clickandpledge_dfltnoofpaymnts"  maxlength="3" name="woocommerce_clickandpledge_dfltnoofpaymnts" value="<?php echo esc_attr( $this->recurring_details['dfltnoofpaymnts'] ) ?>"  /></td></tr>
					<tr class="maxnoofinstlmnts"><td><div id="maxnoofinstlmntslbl_div">
					<label><input type="text" name="woocommerce_clickandpledge_maxnoofinstallments" id="woocommerce_clickandpledge_maxnoofinstallments"  placeholder='Maximum number of installments allowed' value="<?php if(esc_attr( $this->recurring_details['maxnoofinstallments'] ) == ""){ echo "Maximum number of installments allowed";}else{ echo esc_attr( $this->recurring_details['maxnoofinstallments']);}  ?>"/></label></div></td>
					<td><div id="maxnoofinstlmnts_div"><input type="text" id="woocommerce_clickandpledge_maxrecurrings_Subscription" name="woocommerce_clickandpledge_maxrecurrings_Subscription"  maxlength="3" value="<?php echo esc_attr( $this->recurring_details['maxrecurrings_Subscription'] ) ?>"/></div></td></tr>

					<script language="javascript">
			
               
					jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').keypress(function(e) {
						var a = [];
						var k = e.which;

						for (i = 48; i < 58; i++)
							a.push(i);

						if (!(a.indexOf(k)>=0))
							e.preventDefault();
					});
					jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').keypress(function(e) {
						var a1 = [];
						var k1 = e.which;

						for (i1 = 48; i1 < 58; i1++)
							a1.push(i1);

						if (!(a1.indexOf(k1)>=0))
							e.preventDefault();
					});
					jQuery('#woocommerce_clickandpledge_ConnectCampaignAlias').change(function(e) {
					campaignlimitText(jQuery('#woocommerce_clickandpledge_ConnectCampaignAlias'),'',50);
					});
					function campaignlimitText(limitField, limitCount, limitNum) {
					var regex = new RegExp("^[a-zA-Z0-9-_]+$");
					var isValidcamp = regex.test(limitField.val());
					if(!isValidcamp){ limitField.val(limitField.val().replace(/[^a-zA-Z0-9-_]/, "")); }
					if (limitField.val().length > limitNum) {
						limitField.val( limitField.val().substring(0, limitNum) );
					} else {
						//limitCount.html (limitNum - limitField.val().length);
					}
				}
								
				jQuery('#woocommerce_clickandpledge_ConnectCampaignAlias').keyup(function(){
					campaignlimitText(jQuery('#woocommerce_clickandpledge_ConnectCampaignAlias'),'',50);
				});	
					
					jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').change(function(e) {
					var paymethods1 = []; var paymethods_titles1 =[];var str1 = '';
					if(jQuery('#woocommerce_clickandpledge_CreditCard').val()!="") {
						paymethods1.push('CreditCard');
						paymethods_titles1.push('Credit Card');
					}
					if(jQuery('#woocommerce_clickandpledge_eCheck').val()!="") {
						paymethods1.push('eCheck');
						paymethods_titles1.push('eCheck');
					}
					var defaultval1 = jQuery('#woocommerce_clickandpledge_DefaultpaymentMethod').val();
					if(jQuery('#woocommerce_clickandpledge_CustomPayment').is(':checked')) {	
					 var titles1 = jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').val();
						var titlesarr1 = titles1.split(";");
						for(var j1=0;j1 < titlesarr1.length; j1++)
						{ 
							if(titlesarr1[j1] !=""){
								paymethods1.push(titlesarr1[j1]);
								paymethods_titles1.push(titlesarr1[j1]);
							}
						}
						if(paymethods1.length > 0) {
						for(var i1 = 0; i1 < paymethods1.length; i1++) {
							if(paymethods1[i1] == defaultval1) {
							str1 += '<option value="'+paymethods1[i1]+'" selected>'+paymethods_titles1[i1]+'</option>';
							} else {
							str1 += '<option value="'+paymethods1[i1]+'">'+paymethods_titles1[i1]+'</option>';
							}
						}
					} else {
					 str = '<option selected="selected" value="">Please select</option>';
					}
					jQuery('#woocommerce_clickandpledge_DefaultpaymentMethod').html(str1);
					}
					});
					jQuery('#woocommerce_clickandpledge_additionalfeeenabled').change(function(e) {
					if(jQuery('#woocommerce_clickandpledge_additionalfeeenabled').val() == "no")
					{
					     jQuery("tr.traddfeelbl").hide();
						 jQuery("tr.trdaddfeesku").hide();
						 jQuery("tr.trdaddfee").hide();
						 jQuery("tr.traddfeetax").hide();
						 jQuery("tr.traddfeeinstruction").hide();
						 jQuery("tr.traddfeeoptoutlbl").hide();
						 jQuery("tr.traddfeeoptinlbl").hide();
						 jQuery("tr.traddfeedfltoptn").hide();
						
					}
					else if(jQuery('#woocommerce_clickandpledge_additionalfeeenabled').val() == "yes")
					{					
					    jQuery("tr.traddfeelbl").show();
						jQuery("tr.trdaddfeesku").show();
						jQuery("tr.trdaddfee").show();
						jQuery("tr.traddfeetax").show();
						jQuery("tr.traddfeeinstruction").show();
						jQuery("tr.traddfeeoptoutlbl").hide();
						jQuery("tr.traddfeeoptinlbl").hide();
						jQuery("tr.traddfeedfltoptn").hide();
						
					}
					else{
							jQuery("tr.traddfeelbl").show();
							jQuery("tr.trdaddfeesku").show();
							jQuery("tr.trdaddfee").show();
							jQuery("tr.traddfeetax").show();
							jQuery("tr.traddfeeinstruction").show();
							jQuery("tr.traddfeeoptoutlbl").show();
							jQuery("tr.traddfeeoptinlbl").show();
							jQuery("tr.traddfeedfltoptn").show();
						}
					});
					jQuery('#woocommerce_clickandpledge_isRecurring_recurring').click(function(e) {
					if(jQuery('#woocommerce_clickandpledge_isRecurring_recurring').is(':checked')== false)
					{
					    jQuery("tr.trdfltpymntoptn").hide();
						 jQuery("tr.trrectyp").hide();
						 jQuery("tr.trdfltrecoptn").hide();
						 jQuery("tr.trprdcty").hide();
						 jQuery("tr.trnoofpaymnts").hide();
						 jQuery("tr.dfltnoofpaymnts").hide();
						 jQuery("tr.maxnoofinstlmnts").hide();
						
					}
					else
					{
					    jQuery("tr.trdfltpymntoptn").show();
						jQuery("tr.trrectyp").show();
						jQuery("tr.trdfltrecoptn").show();
						jQuery("tr.trprdcty").show();
						jQuery("tr.trnoofpaymnts").show();
						jQuery("tr.dfltnoofpaymnts").show();
						jQuery("tr.maxnoofinstlmnts").show();
					}
					});
					</script>				
					</tbody>
					
				</table>
				
			</td>
		</tr>
		<?php
		return ob_get_clean();

	}
		/**
	 * Save Additionalfee details table.
	 */
		public function save_paymentsettings_details(){
		$cnpsettings = array();

		if ( isset($_POST['woocommerce_clickandpledge_enabled'] )  || 
			 isset($_POST['woocommerce_clickandpledge_AccountID'])) {

			$enabled 	     				     =  $_POST['woocommerce_clickandpledge_enabled'];
			$title    				             =  $_POST['woocommerce_clickandpledge_title'];
			$description           				 =  $_POST['woocommerce_clickandpledge_description'];
			$AccountID          				 =  $_POST['woocommerce_clickandpledge_AccountID'] ;
			$testmode           				 =  $_POST['woocommerce_clickandpledge_testmode'] ;
			$ConnectCampaignAlias                =  $_POST['woocommerce_clickandpledge_ConnectCampaignAlias'] ;
			$CreditCard                          =  $_POST['woocommerce_clickandpledge_CreditCard'];
			$eCheck                              =  $_POST['woocommerce_clickandpledge_eCheck'];
			$American_Express                    =  $_POST['woocommerce_clickandpledge_American_Express'] ;
			$JCB                                 =  $_POST['woocommerce_clickandpledge_JCB'];
			$MasterCard                          =  $_POST['woocommerce_clickandpledge_MasterCard'];
			$Visa                                =  $_POST['woocommerce_clickandpledge_Visa'];
			$Discover                            =  $_POST['woocommerce_clickandpledge_Discover'];
			//$Preauthorization                    =  $_POST['woocommerce_clickandpledge_Preauthorization'];
			$CustomPayment                       =  $_POST['woocommerce_clickandpledge_CustomPayment'];
			$CustomPayment_Titles                =  $_POST['woocommerce_clickandpledge_CustomPayment_Titles'];
		    $ReferenceNumber_Label               =  $_POST['woocommerce_clickandpledge_ReferenceNumber_Label'];
			$cnpsettings = array(
					'enabled'             => $enabled,
					'title'               => $title,
					'description'         => $description,
					'AccountID'           => $AccountID,
					'testmode'            => $testmode,
					'ConnectCampaignAlias'=> $ConnectCampaignAlias,
					'CreditCard'          => $CreditCard,
					'eCheck'              => $eCheck,
					'American_Express'    => $American_Express,
					'JCB'                 => $JCB,
					'MasterCard'          => $MasterCard,
					'Visa'                => $Visa,	
					'dfltpayoptn'         => $Discover,	
				//	'Preauthorization'    => $Preauthorization,
					'CustomPayment'       => $CustomPayment,
            		'ReferenceNumber_Label'       => $ReferenceNumber_Label,
           			'CustomPayment_Titles'       => $CustomPayment_Titles
										
				);
			
		}

		update_option('woocommerce_clickandpledge_paymentsettings', $cnpsettings);
		
	}
		/**
	 * Save cnp account details table.
	 */
	public function save_additionalfee_details(){
		$cnpaddfeesettings = array();

		if ( isset($_POST['woocommerce_clickandpledge_additionalfeeenabled'] )  || 
			 ($_POST['woocommerce_clickandpledge_additionalfeeenabled']) == 'yes') {

			$feeenabled 	     				 =  $_POST['woocommerce_clickandpledge_additionalfeeenabled'];
			$feetitle    				         =  $_POST['woocommerce_clickandpledge_addfeetitle'];
			$feeper 	     				     =  $_POST['woocommerce_clickandpledge_addfeeper'];
			$feeamt    				             =  $_POST['woocommerce_clickandpledge_addfeeamt'];
			$feetax    				             =  $_POST['woocommerce_clickandpledge_addfeetax'];
			$feesku    				             =  $_POST['woocommerce_clickandpledge_addfeesku'];
			$feeinstructions 	     		     =  $_POST['woocommerce_clickandpledge_addfeeinstructions'];
			$feeoptoutlbl    			         =  $_POST['woocommerce_clickandpledge_addfeeoptoutlbl'];
			$feeoptinlbl    			         =  $_POST['woocommerce_clickandpledge_addfeeoptinlbl'];
			$feedfltoptn    			         =  $_POST['woocommerce_clickandpledge_dfltoptn'];
			$cnpaddfeesettings = array(
					'feeenabled'             => $feeenabled,
					'feetitle'               => $feetitle,
					'feeper'                 => $feeper,
					'feeamt'                 => $feeamt,
					'feetax'                 => $feetax,
					'feeinstructions'        => $feeinstructions,
					'feeoptoutlbl'           => $feeoptoutlbl,
					'feeoptinlbl'            => $feeoptinlbl,
					'feedfltoptn'            => $feedfltoptn,
					'feesku'                 => $feesku
					
										
				);
			
		}

		update_option('woocommerce_clickandpledge_additionalfee', $cnpaddfeesettings);
		
	}
	
	/**
	 * Save Recurring details table.
	 */
	public function save_recurring_details(){

		$cnprecurring = array();

		if ( isset( $_POST['woocommerce_clickandpledge_Installment'] )  || isset( $_POST['woocommerce_clickandpledge_Subscription']) || 
		    isset( $_POST['woocommerce_clickandpledge_isRecurring_recurring'] )   || isset( $_POST['woocommerce_clickandpledge_isRecurring_oto'] ) ) {

			$installment     				 =  $_POST['woocommerce_clickandpledge_Installment'];
			$subscription    				 =  $_POST['woocommerce_clickandpledge_Subscription'];
			$week           				 =  $_POST['woocommerce_clickandpledge_Week'];
			$tweeks          				 =  $_POST['woocommerce_clickandpledge_2_Weeks'] ;
			$month           				 =  $_POST['woocommerce_clickandpledge_Month'] ;
			$tmonths                         =  $_POST['woocommerce_clickandpledge_2_Months'] ;
			$quarter                         =  $_POST['woocommerce_clickandpledge_Quarter'];
			$smonths                         =  $_POST['woocommerce_clickandpledge_6_Months'];
			$year                            =  $_POST['woocommerce_clickandpledge_Year'] ;
			$indefinite                      =  $_POST['woocommerce_clickandpledge_indefinite'];
			$isRecurring_oto                 =  $_POST['woocommerce_clickandpledge_isRecurring_oto'];
			$isRecurring_recurring           =  $_POST['woocommerce_clickandpledge_isRecurring_recurring'];
			$dfltpayoptn                     =  $_POST['woocommerce_clickandpledge_dfltpayoptn'];
			$dfltrectypoptn                  =  $_POST['woocommerce_clickandpledge_dfltrectypoptn'];
			$dfltnoofpaymnts                 =  $_POST['woocommerce_clickandpledge_dfltnoofpaymnts'];
			$payoptn                         =  $_POST['woocommerce_clickandpledge_payoptn'];
			$rectype                         =  $_POST['woocommerce_clickandpledge_rectype'];
	  	    $periodicity                     =  $_POST['woocommerce_clickandpledge_periodicity'];
			$noofpayments                    =  $_POST['woocommerce_clickandpledge_noofpayments'];
			$dfltnoofpaymentslbl             =  $_POST['woocommerce_clickandpledge_dfltnoofpaymentslbl'];
			$maxnoofinstallments             =  $_POST['woocommerce_clickandpledge_maxnoofinstallments'];
			$maxrecurrings_Subscription      =  $_POST['woocommerce_clickandpledge_maxrecurrings_Subscription'];
		
			
			
				$cnprecurring = array(
					'installment'      => $installment,
					'subscription'     => $subscription,
					'week'             => $week,
					'2_weeks'          => $tweeks,
					'month'            => $month,
					'2_months'         => $tmonths,
					'quarter'          => $quarter,
					'6_months'         => $smonths,
					'year'             => $year,
					'indefinite'       => $indefinite,
					'isRecurring_oto'  => $isRecurring_oto,
					'isRecurring_recurring' => $isRecurring_recurring,	
					'dfltpayoptn'      => $dfltpayoptn,	
					'dfltrectypoptn'   => $dfltrectypoptn,	
					'dfltnoofpaymnts'  => $dfltnoofpaymnts,	
					'payoptn'          => $payoptn,	
					'rectype'          => $rectype,	
					'periodicity'      => $periodicity,	
					'noofpayments'     => $noofpayments,
					'dfltnoofpaymentslbl'         => $dfltnoofpaymentslbl,
					'maxnoofinstallments'         => $maxnoofinstallments,
					'maxrecurrings_Subscription'  => $maxrecurrings_Subscription					
				);
			
		}

		update_option( 'woocommerce_clickandpledge_recurring', $cnprecurring );

	}
	/**
	 * Save acceptedcreditcards details table.
	 */
	public function save_acceptedcreditcards_details() {

		$cnprecurring = array();

		if ( isset( $_POST['woocommerce_clickandpledge_Visa'] )  || isset( $_POST['woocommerce_clickandpledge_American_Express'] ) ||
		     isset( $_POST['woocommerce_clickandpledge_Discover'] )  || isset( $_POST['woocommerce_clickandpledge_MasterCard'] ) ||
		     isset( $_POST['woocommerce_clickandpledge_JCB'] )  ) {

						
			$Visa                         =  $_POST['woocommerce_clickandpledge_Visa'];
	  	    $American_Express             =  $_POST['woocommerce_clickandpledge_American_Express'];
			$Discover                     =  $_POST['woocommerce_clickandpledge_Discover'];
			$MasterCard                   =  $_POST['woocommerce_clickandpledge_MasterCard'];
			$JCB                          =  $_POST['woocommerce_clickandpledge_JCB'];
			
			
				$cnpcreditcards = array(
					'Visa'                 => $Visa,
					'American_Express'     => $American_Express,
					'Discover'             => $Discover,
					'MasterCard'           => $MasterCard,
					'JCB'                  => $JCB				
				);
			
		}

		update_option( 'woocommerce_clickandpledge_acceptedcreditcards', $cnpcreditcards );

	}
    /**
	 * Save zeropayment details table.
	 */
	public function save_zeropaymentsettings_details() {

		$cnprecurring = array();

		if ( isset( $_POST['woocommerce_clickandpledge_zerocreditcard'] )  || isset( $_POST['woocommerce_clickandpledge_zerocustom'] ) ||
		     isset( $_POST['woocommerce_clickandpledge_zerocustompaynt'] )    ) {

						
			$zerocreditcard                         =  $_POST['woocommerce_clickandpledge_zerocreditcard'];
	  	    $zerocustom                             =  $_POST['woocommerce_clickandpledge_zerocustom'];
			$zerocustompaynt                        =  $_POST['woocommerce_clickandpledge_zerocustompaynt'];
			
			
			
				$cnpzeropaymnt = array(
					'zerocreditcard'                 => $zerocreditcard,
					'zerocustom'                     => $zerocustom,
					'zerocustompaynt'                => $zerocustompaynt
							
				);
			
		}

		update_option( 'woocommerce_clickandpledge_zeropaymentsettings', $cnpzeropaymnt );

	}
	    /**
		 * Admin Panel Options 
		 * - Options for bits like 'title' and availability on a country-by-country basis
		 */
		function admin_options() {
	    	?>
	    	<h3><?php //_e( 'Click & Pledge', 'woothemes' ); ?></h3>
	    	<?php echo "<img src='".WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . "/images/image_3422.png' title='Click & Pledge' alt='Click & Pledge'/>";?>
	    	<p><?php _e( 'Click & Pledge works by adding credit card fields on the checkout and then sending the details to Click & Pledge for verification.', 'woothemes' ); ?>
			</p>
	    	<table class="form-table">
	    		<?php $this->generate_settings_html(); ?>
			</table><!--/.form-table-->
			
			<script>
			
			jQuery(document).ready(function(){
			<?php  global $wpdb;
			 $accountstable_name =$wpdb->prefix . 'cnp_wp_wccnpaccountsinfo';
			 $cnpsqlst= "SELECT count(*) FROM ". $accountstable_name;
			 $rowcount = $wpdb->get_var( $cnpsqlst );if ($rowcount > 0){?>	
				limitText(jQuery('#woocommerce_clickandpledge_OrganizationInformation'),jQuery('#OrganizationInformation_countdown'),1500);	
				limitText(jQuery('#woocommerce_clickandpledge_TermsCondition'),jQuery('#TermsCondition_countdown'),1500);
				displaycheck();
				
				 jQuery("#woocommerce_clickandpledge_DefaultpaymentMethod option[value='<?php echo $this->defaultpayment;?>']").prop('selected', true);
				<?php }?>
				function displaycheck() {
					
					if(jQuery('#woocommerce_clickandpledge_CreditCard').val()=="" &&        jQuery('#woocommerce_clickandpledge_eCheck').val()=="") {
					
						jQuery('.CredicardSection').next('table').hide();
						jQuery('.CredicardSection').hide();
						jQuery('.clsacptcrds').hide();
							
						jQuery('.RecurringSection').next('table').hide();
						jQuery('.RecurringSection').hide();
					} else {
						if(jQuery('#woocommerce_clickandpledge_CreditCard').val != "") {
						
							jQuery('.CredicardSection').next('table').show();
							jQuery('.CredicardSection').show();
							jQuery('#woocommerce_clickandpledge_Preauthorization').closest('tr').show();
							jQuery('.clsacptcrds').show();
						} else {
							jQuery('.CredicardSection').next('table').hide();
							jQuery('.CredicardSection').hide();
							jQuery('#woocommerce_clickandpledge_Preauthorization').closest('tr').hide();
							jQuery('.clsacptcrds').hide();
						}
						
						if(jQuery('#woocommerce_clickandpledge_CreditCard').val()!="" || jQuery('#woocommerce_clickandpledge_eCheck').val()!="") {
						
							jQuery('.RecurringSection').next('table').show();
							jQuery('.RecurringSection').show();
						}
					}
					if(jQuery('#woocommerce_clickandpledge_additionalfeeenabled').val() == "no")
					{
					     jQuery("tr.traddfeelbl").hide();
						 jQuery("tr.trdaddfeesku").hide();
						 jQuery("tr.trdaddfee").hide();
						 jQuery("tr.traddfeetax").hide();
						 jQuery("tr.traddfeeinstruction").hide();
						 jQuery("tr.traddfeeoptoutlbl").hide();
						 jQuery("tr.traddfeeoptinlbl").hide();
						 jQuery("tr.traddfeedfltoptn").hide();
						
					}
					else if(jQuery('#woocommerce_clickandpledge_additionalfeeenabled').val() == "yes")
					{					
					    jQuery("tr.traddfeelbl").show();
						jQuery("tr.trdaddfeesku").show();
						jQuery("tr.trdaddfee").show();
						jQuery("tr.traddfeetax").show();
						jQuery("tr.traddfeeinstruction").show();
						jQuery("tr.traddfeeoptoutlbl").hide();
						jQuery("tr.traddfeeoptinlbl").hide();
						jQuery("tr.traddfeedfltoptn").hide();
						
					}
					else{
							jQuery("tr.traddfeelbl").show();
							jQuery("tr.trdaddfeesku").show();
							jQuery("tr.trdaddfee").show();
							jQuery("tr.traddfeetax").show();
							jQuery("tr.traddfeeinstruction").show();
							jQuery("tr.traddfeeoptoutlbl").show();
							jQuery("tr.traddfeeoptinlbl").show();
							jQuery("tr.traddfeedfltoptn").show();
						}
					if(jQuery('#woocommerce_clickandpledge_isRecurring_oto').is(':checked') == true && 
					   jQuery('#woocommerce_clickandpledge_isRecurring_recurring').is(':checked') == true)
					{
					    jQuery("tr.trdfltpymntoptn").show();
					}
					else
					{
					     jQuery("tr.trdfltpymntoptn").hide();
					}
					if(jQuery('#woocommerce_clickandpledge_Installment').is(':checked') == true &&     jQuery('#woocommerce_clickandpledge_Subscription').is(':checked') == true)
					{
					     jQuery("tr.trdfltrecoptn").show();
					}
					else
					{
					     jQuery("tr.trdfltrecoptn").hide();
					}
					if(jQuery('#woocommerce_clickandpledge_Subscription').is(':checked') == true)
					{
						
						jQuery("#indefinite_div").show();jQuery("#indefinite_openfield_div").show();jQuery("#openfild_div").show();jQuery("#fixdnumber_div").show();
					}
					else
					{
						jQuery("#indefinite_div").hide();jQuery("#indefinite_openfield_div").hide();
                        



					}
					if(jQuery('#woocommerce_clickandpledge_Installment').is(':checked') == true)
					{
					
					    jQuery("#openfild_div").show();jQuery("#fixdnumber_div").show();
					}
				    if(jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() == 1)
					{ 
					
						jQuery("tr.dfltnoofpaymnts").hide();jQuery("tr.maxnoofinstlmnts").hide();
						jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').attr('readonly', false);
					}
					if(jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() == "openfield")
					{
					
						jQuery("tr.dfltnoofpaymnts").show();jQuery("tr.maxnoofinstlmnts").show();
						jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').attr('readonly', false);
					}
					if(jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() == "indefinite_openfield")
					{
						jQuery("tr.dfltnoofpaymnts").show();jQuery("tr.maxnoofinstlmnts").show();
						jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").val('999');
						jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').attr('readonly', true);
					}
					if(jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() == "fixednumber")
					{
						
						jQuery("tr.dfltnoofpaymnts").show();jQuery("tr.maxnoofinstlmnts").hide();
						jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').attr('readonly', false);
					}
					if(jQuery('#woocommerce_clickandpledge_isRecurring_recurring').is(':checked')== false)
					{
					    jQuery("tr.trdfltpymntoptn").hide();
						 jQuery("tr.trrectyp").hide();
						 jQuery("tr.trdfltrecoptn").hide();
						 jQuery("tr.trprdcty").hide();
						 jQuery("tr.trnoofpaymnts").hide();
						 jQuery("tr.dfltnoofpaymnts").hide();
						 jQuery("tr.maxnoofinstlmnts").hide();
						
						
					}
					else
					{
					   
						jQuery("tr.trrectyp").show();
						jQuery("tr.trprdcty").show();
						jQuery("tr.trnoofpaymnts").show();
						if(jQuery('#woocommerce_clickandpledge_isRecurring_oto').is(':checked') == true && 
						   jQuery('#woocommerce_clickandpledge_isRecurring_recurring').is(':checked') == true)
					{
					    jQuery("tr.trdfltpymntoptn").show();
					}
					else
					{
					     jQuery("tr.trdfltpymntoptn").hide();
					}
					if(jQuery('#woocommerce_clickandpledge_Installment').is(':checked') == true && jQuery('#woocommerce_clickandpledge_Subscription').is(':checked') == true)
					{
					    jQuery("tr.trdfltrecoptn").show();
					}
					else
					{
					     jQuery("tr.trdfltrecoptn").hide();
					}
						if(jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() == 1)
					    { 
							jQuery("tr.dfltnoofpaymnts").hide();
							jQuery("tr.maxnoofinstlmnts").hide();
						}
						else  if(jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() == "fixednumber")
						{
							jQuery("tr.dfltnoofpaymnts").show();
						   
						}
						else
						{
							jQuery("tr.dfltnoofpaymnts").show();
						    jQuery("tr.maxnoofinstlmnts").show();
						}
					}
					defaultpayment();
				}
				function defaultpayment() {
					var paymethods = [];
					var paymethods_titles = [];
					var str = '';
					var defaultval = jQuery('#woocommerce_clickandpledge_DefaultpaymentMethod').val();
					if(jQuery('#woocommerce_clickandpledge_CreditCard').val()!=""){						        paymethods.push('CreditCard');
						paymethods_titles.push('Credit Card');
					}
					if(jQuery('#woocommerce_clickandpledge_eCheck').val()!="") {
						paymethods.push('eCheck');
						paymethods_titles.push('eCheck');
					}
					
					if(jQuery('#woocommerce_clickandpledge_CustomPayment').is(':checked')) {
						jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').closest('tr').show();
						jQuery('#woocommerce_clickandpledge_ReferenceNumber_Label').closest('tr').show();
						
						var titles = jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').val();
						var titlesarr = titles.split(";");
						for(var j=0;j < titlesarr.length; j++)
						{
							if(titlesarr[j] !=""){
								paymethods.push(titlesarr[j]);
								paymethods_titles.push(titlesarr[j]);
							}
						}
					} else {
						jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').closest('tr').hide();
						jQuery('#woocommerce_clickandpledge_ReferenceNumber_Label').closest('tr').hide();
					}
					
					if(paymethods.length > 0) {
						for(var i = 0; i < paymethods.length; i++) {
							if(paymethods[i] == defaultval) {
							str += '<option value="'+paymethods[i]+'" selected>'+paymethods_titles[i]+'</option>';
							} else {
							str += '<option value="'+paymethods[i]+'">'+paymethods_titles[i]+'</option>';
							}
						}
					} else {
					 str = '<option selected="selected" value="">Please select</option>';
					}
					jQuery('#woocommerce_clickandpledge_DefaultpaymentMethod').html(str);
				}
				jQuery('.rectyp').click(function()
				{
				  if(jQuery('#woocommerce_clickandpledge_isRecurring_oto').is(':checked') && jQuery('#woocommerce_clickandpledge_isRecurring_recurring').is(':checked'))
					{
					    jQuery("tr.trdfltpymntoptn").show();
					}
					else
					{
					     jQuery("tr.trdfltpymntoptn").hide();
					}
				
				});
				jQuery('.clsrectype').click(function()
				{
				  if(jQuery('#woocommerce_clickandpledge_Installment').is(':checked') && jQuery('#woocommerce_clickandpledge_Subscription').is(':checked'))
					{
					    jQuery("tr.trdfltrecoptn").show();
					}
					else
					{
					     jQuery("tr.trdfltrecoptn").hide();
					}
				
				});
				
				jQuery(".clsnoofpaymnts").change(function(){
																			
					var noofpay = jQuery(this).val();
					if(noofpay == 1)
					{
						jQuery("#woocommerce_clickandpledge_dfltnoofpaymnts").val('');
						jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").val('');
						jQuery("tr.dfltnoofpaymnts").hide();jQuery("tr.maxnoofinstlmnts").hide();
						jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').attr('readonly', false);
					}
					if(noofpay == "openfield")
					{
					    jQuery("#woocommerce_clickandpledge_dfltnoofpaymnts").val('');
						jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").val('');
						jQuery("tr.dfltnoofpaymnts").show();jQuery("tr.maxnoofinstlmnts").show();
						jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').attr('readonly', false);
				   }
					if(noofpay == "indefinite_openfield")
					{
					    jQuery("#woocommerce_clickandpledge_dfltnoofpaymnts").val('');
						jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").val('');
						jQuery("tr.dfltnoofpaymnts").show();jQuery("tr.maxnoofinstlmnts").show();
						jQuery("#woocommerce_clickandpledge_dfltnoofpaymnts").val('999');
						jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").val('999');
						jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').attr('readonly', true);
					}
					if(noofpay == "fixednumber")
					{
					    jQuery("#woocommerce_clickandpledge_dfltnoofpaymnts").val('');
						jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").val('');
						jQuery("tr.dfltnoofpaymnts").show();jQuery("tr.maxnoofinstlmnts").hide();
						jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').attr('readonly', false);
					}
					
				});
				jQuery("#woocommerce_clickandpledge_Subscription").click(function(){
				if(jQuery('#woocommerce_clickandpledge_Subscription').is(':checked') == true)
				{
				
				 jQuery("#indefinite_div").show();jQuery("#indefinite_openfield_div").show();jQuery("#openfild_div").show();jQuery("#fixdnumber_div").show();
				 
				}
				else if(jQuery('#woocommerce_clickandpledge_Subscription').is(':checked') == false)
				{
				
				  jQuery("#indefinite_div").hide();jQuery("#indefinite_openfield_div").hide();
                jQuery('input[name="woocommerce_clickandpledge_indefinite"]').prop('checked', false);

                 jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val(''); //txtID is textbox ID
                         jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').val(''); //txtID is textbox ID
                    						jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').attr('readonly', false);
				}
				
				});
				/*<!--jQuery('#woocommerce_clickandpledge_addfeetitle').on('keypress', function(e) {
        if (e.which == 32)
            return false;
        });-->*/
				jQuery( "form" ).submit(function( event ) {
				  if(jQuery('input[name=woocommerce_clickandpledge_testmode]:checked').length <= 0)
					{
						alert('Please select API Mode');
						jQuery('#woocommerce_clickandpledge_testmode').focus();
						return false;
					}
					if(jQuery('#woocommerce_clickandpledge_title').val() == '')
					{
						alert('Please enter title');
						jQuery('#woocommerce_clickandpledge_title').focus();
						return false;
					}
					
					
					if(jQuery('#woocommerce_clickandpledge_CustomPayment').is(':checked') && jQuery.trim(jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').val()) == '') {
						alert('Please enter at least one payment method name');
						jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').val('');
						jQuery('#woocommerce_clickandpledge_CustomPayment_Titles').focus();
						return false;	
					}
					if(jQuery('#woocommerce_clickandpledge_additionalfeeenabled').val() == "yes" ||
					  jQuery('#woocommerce_clickandpledge_additionalfeeenabled').val() == "optin")
					{
						
						if((jQuery('#woocommerce_clickandpledge_addfeetitle').val().trim().length !== "0") && jQuery('#woocommerce_clickandpledge_addfeetitle').val().trim().length < 2)
						{ 
							alert('Please enter additional fee label value greater than 2');
							jQuery('#woocommerce_clickandpledge_addfeetitle').focus();
							return false;
						}
						if((jQuery('#woocommerce_clickandpledge_addfeesku').val().trim().length !== "0") && jQuery('#woocommerce_clickandpledge_addfeesku').val().trim().length < 2)
						{ 
							alert('Please enter additional fee SKU value greater than 2');
							jQuery('#woocommerce_clickandpledge_addfeesku').focus();
							return false;
						}
						if(jQuery('#woocommerce_clickandpledge_addfeeper').val() == '')
						{
							alert('Please enter additional fee');
							jQuery('#woocommerce_clickandpledge_addfeeper').focus();
							return false;
						}
						var addfeetot = parseFloat(jQuery('#woocommerce_clickandpledge_addfeeper').val())
+parseFloat(jQuery('#woocommerce_clickandpledge_addfeeamt').val())	;
						
						if(jQuery('#woocommerce_clickandpledge_addfeeper').val() != "" && addfeetot <= 0)
						{
						   alert("Please enter Additional Fee percentage value greater than 0");
						   jQuery("#woocommerce_clickandpledge_addfeeper").focus();
						   return false;														
						}
						if(jQuery('#woocommerce_clickandpledge_addfeeper').val() != "" && jQuery('#woocommerce_clickandpledge_addfeeper').val() > 100)
						{
						   alert("Please enter Additional Fee percentage should be less than 100.");
						   jQuery("#woocommerce_clickandpledge_addfeeper").focus();
						   return false;														
						}
						if(jQuery('#woocommerce_clickandpledge_addfeetax').val() != "" && jQuery('#woocommerce_clickandpledge_addfeetax').val() < 0)
						{
						   alert("Please enter Tax Deductible percentage value greater than 0");
						   jQuery("#woocommerce_clickandpledge_addfeetax").focus();
						   return false;														
						}
						if(jQuery('#woocommerce_clickandpledge_addfeetax').val() != "" && jQuery('#woocommerce_clickandpledge_addfeetax').val() > 100)
						{
						   alert("Please enter Tax Deductible percentage should be less than 100.");
						   jQuery("#woocommerce_clickandpledge_addfeetax").focus();
						   return false;														
						}
					}
					
					
					var selected5 = 0;
					if(jQuery("#woocommerce_clickandpledge_isRecurring_oto").prop('checked')) selected5++;
					if(jQuery("#woocommerce_clickandpledge_isRecurring_recurring").prop('checked')) selected5++;
				
					if(selected5 == 0) {
						alert('Please select at least  one payment option');
						jQuery("#woocommerce_clickandpledge_isRecurring_oto").focus();
						return false;
					}
					
					if(jQuery('#woocommerce_clickandpledge_isRecurring_recurring').is(':checked') == true) {		
					var selected = 0;
			
					if(jQuery("#woocommerce_clickandpledge_Week").prop('checked')) selected++;
					if(jQuery("#woocommerce_clickandpledge_2_Weeks").prop('checked')) selected++;
					if(jQuery("#woocommerce_clickandpledge_Month").prop('checked')) selected++;
					if(jQuery("#woocommerce_clickandpledge_2_Months").prop('checked')) selected++;
					if(jQuery("#woocommerce_clickandpledge_Quarter").prop('checked')) selected++;
					if(jQuery("#woocommerce_clickandpledge_6_Months").prop('checked')) selected++;
					if(jQuery("#woocommerce_clickandpledge_Year").prop('checked')) selected++;
					if(selected == 0) {
						alert('Please select at least one period');
						jQuery("#woocommerce_clickandpledge_Week").focus();
						return false;
					}
					var selected2 = 0;
					if(jQuery("#woocommerce_clickandpledge_Installment").prop('checked')) selected2++;
					if(jQuery("#woocommerce_clickandpledge_Subscription").prop('checked')) selected2++;
				
					if(selected2 == 0) {
						alert('Please select at least one recurring type');
						jQuery("#woocommerce_clickandpledge_Installment").focus();
						return false;
					}
					
					if(jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').length<=0)
					{
					   alert("Please select at least one option for number of payments");
					   jQuery("#woocommerce_clickandpledge_indefinite").focus();
					   return false;
					}
					if(jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() != "1")
			        {
			
				if(jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val() == "" && jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() == "fixednumber")
				{
				   alert("Please enter default number of payments");
				   jQuery("#woocommerce_clickandpledge_dfltnoofpaymnts").focus();
				   return false;														
				}
				if(jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val() != "" && jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val() <= 1)
				{
				   alert("Please enter default number of payments value greater than 1");
				   jQuery("#woocommerce_clickandpledge_dfltnoofpaymnts").focus();
				   return false;														
				}
				if(!isInteger(jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val()) && jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val() != "" )
				
				{
					
				   alert("Please enter an integer value only");
				   jQuery("#woocommerce_clickandpledge_dfltnoofpaymnts").focus();
				   return false;														
				}
				if(jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').val() != "" && jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').val() <=1)
				{
				   alert("Please enter maximum number of installments allowed value greater than 1");
				   jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").focus();
				   return false;														
				}
				if(parseInt(jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').val()) < parseInt(jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val()))
				{
					alert("Maximum number of installments allowed to be greater than or equal to default number of payments");
					jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').focus();
					return false;
				}
				if(!isInteger(jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').val()) && jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').val() != "" )
				{
				   alert("Please enter an integer value only");
				   jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").focus();
				   return false;														
				}
			     		if(jQuery("#woocommerce_clickandpledge_Installment").is(':checked') && jQuery("#woocommerce_clickandpledge_Subscription").is(':checked'))
						{
						  jQuery("tr.trdfltrecoptn").show();
						}
						else{
						  jQuery("tr.trdfltrecoptn").hide();
						}
				if(jQuery('#woocommerce_clickandpledge_Installment').is(':checked') && !jQuery('#woocommerce_clickandpledge_Subscription').is(':checked'))
				{
					
					if(jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val() !=""  && jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val() > 998 &&
					 jQuery('tr.dfltnoofpaymnts').css('display') != 'none')
					{
						   alert("Please enter value between 2 to 998 for installment");
						   jQuery("#woocommerce_clickandpledge_dfltnoofpaymnts").focus();
						   return false;
					}
					if(jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').val() !=""  && jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').val() > 998 && jQuery('tr.maxnoofinstlmnts').css('display') != 'none')
					{
						   alert("Please enter value between 2 to 998 for installment");
						   jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").focus();
						   return false;
					}
				}
				else if(jQuery('#woocommerce_clickandpledge_Installment').is(':checked') && jQuery('#woocommerce_clickandpledge_Subscription').is(':checked'))
				{
					
					if(jQuery('#woocommerce_clickandpledge_dfltrectypoptn').val() == "Installment" && jQuery('#woocommerce_clickandpledge_dfltnoofpaymnts').val() > 998 && 
					   jQuery('tr.dfltnoofpaymnts').css('display') != 'none' && 
					   jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() != "indefinite_openfield")
					{
						 alert("Please enter value between 2 to 998 for installment");
						   jQuery("#gfcnp_dfltnoofpaymnts").focus();
						   return false;
					}
					if(jQuery('#woocommerce_clickandpledge_dfltrectypoptn').val() == "Installment" && 
					   jQuery('#woocommerce_clickandpledge_maxrecurrings_Subscription').val() > 998 &&
					   jQuery('tr.gfcnp_maxnoofinstallmentsallowed').css('display') != 'none' && 
					   jQuery('input[name=woocommerce_clickandpledge_indefinite]:checked').val() != "indefinite_openfield")
					{
						  alert("Please enter value between 2 to 998 for installment");
						   jQuery("#woocommerce_clickandpledge_maxrecurrings_Subscription").focus();
						   return false;
					}
				}
				
			}
		}
					
					function isInt(n) {
						return n % 1 === 0;
					}
					 function isInteger(n) {
						return /^[0-9]+$/.test(n);
					}
				
				});
				jQuery('#woocommerce_clickandpledge_CustomPayment').click(function(){
					
					defaultpayment();
				});	

				function limitText(limitField, limitCount, limitNum) {
					if (limitField.val().length > limitNum) {
						limitField.val( limitField.val().substring(0, limitNum) );
					} else {
						limitCount.html (limitNum - limitField.val().length);
					}
				}
				
				jQuery("input#woocommerce_clickandpledge_addfeeper").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
				jQuery("input#woocommerce_clickandpledge_addfeeamt").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
				jQuery("input#woocommerce_clickandpledge_addfeetax").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
				///////Events Start
				
				//OrganizationInformation
				jQuery('#woocommerce_clickandpledge_OrganizationInformation').keydown(function(){
					limitText(jQuery('#woocommerce_clickandpledge_OrganizationInformation'),jQuery('#OrganizationInformation_countdown'),1500);
				});
				jQuery('#woocommerce_clickandpledge_OrganizationInformation').keyup(function(){
					limitText(jQuery('#woocommerce_clickandpledge_OrganizationInformation'),jQuery('#OrganizationInformation_countdown'),1500);
				});
					
				
				jQuery('#woocommerce_clickandpledge_TermsCondition').keydown(function(){
					limitText(jQuery('#woocommerce_clickandpledge_TermsCondition'),jQuery('#TermsCondition_countdown'),1500);
				});
				jQuery('#woocommerce_clickandpledge_TermsCondition').keyup(function(){
					limitText(jQuery('#woocommerce_clickandpledge_TermsCondition'),jQuery('#TermsCondition_countdown'),1500);
				});
				
			
		
			
				
			});
			</script>
	    	<?php
	    }
				
		/**
	     * Get the users country either from their order, or from their customer data
	     */
		function get_country_code() {
			global $woocommerce;
			
			if(isset($_GET['order_id'])) {
			
				$order = new WC_Order($_GET['order_id']);
	
				return $order->billing_country;
				
			} elseif ($woocommerce->customer->get_country()) {
				
				return $woocommerce->customer->get_country();
			
			}
			
			return NULL;
		}
	
		/**
	     * Payment form on checkout page
	     */
		function payment_fields() {			
			$user_country = $this->get_country_code();			
			if(empty($user_country)) :
				echo __('Select a country to see the payment form', 'woothemes');
				return;
			endif;		
			$available_cards = $this->available_cards;
			$testmodedesc="";
				if(isset($this->testmode) && $this->testmode == 'yes')
			    {
					$testmodedesc= "TEST MODE/SANDBOX ENABLED";
				}
				if(isset($this->paymentsettings_details['testmode']) && 
				   		 $this->paymentsettings_details['testmode'] == 'yes')
			    {
				    $testmodedesc= "TEST MODE/SANDBOX ENABLED";
				}
				elseif(isset($this->paymentsettings_details['testmode']) && 
				   		 $this->paymentsettings_details['testmode'] == 'no')
			    {  $testmodedesc= "";}
if($testmodedesc!="")
{?>
	<script>
			jQuery( document ).ready(function() {
			var d = new Date();
			var n = d.getFullYear() + 1;
			document.getElementById('clickandpledge_card_number').value = "4111111111111111";
			
			jQuery("#clickandpledge_card_number").prop("readonly", true);
			jQuery("#clickandpledge_card_number").css({"background-color": "#f4f4f4"});
			
			document.getElementById('cc-expire-month').value = "06";
			jQuery("#cc-expire-month").prop("readonly", true);
			document.getElementById('cc-expire-year').value = n ;
			jQuery("#cc-expire-year").prop("readonly", true);
			document.getElementById('clickandpledge_card_csc').value = "123";
			jQuery("#clickandpledge_card_csc").prop("readonly", true);
				
			jQuery("#clickandpledge_echeck_AccountType").prop("disabled", true);
			jQuery("#clickandpledge_echeck_AccountType").css({"background-color": "#f4f4f4"});
			jQuery("#clickandpledge_echeck_NameOnAccount").prop("disabled", true);
			jQuery("#clickandpledge_echeck_NameOnAccount").css({"background-color": "#f4f4f4"});
			jQuery("#clickandpledge_echeck_IdType").prop("disabled", true);
			jQuery("#clickandpledge_echeck_IdType").css({"background-color": "#f4f4f4"});
			jQuery("#clickandpledge_echeck_CheckType").prop("disabled", true);
			jQuery("#clickandpledge_echeck_CheckType").css({"background-color": "#f4f4f4"});
			jQuery("#clickandpledge_echeck_CheckNumber").prop("disabled", true);
			jQuery("#clickandpledge_echeck_CheckNumber").css({"background-color": "#f4f4f4"});
			jQuery("#clickandpledge_echeck_RoutingNumber").prop("disabled", true);
			jQuery("#clickandpledge_echeck_RoutingNumber").css({"background-color": "#f4f4f4"});
			jQuery("#clickandpledge_echeck_TransitNumber").prop("disabled", true);
			jQuery("#clickandpledge_echeck_TransitNumber").css({"background-color": "#f4f4f4"});
			jQuery("#clickandpledge_echeck_BankNumber").prop("disabled", true);
			jQuery("#clickandpledge_echeck_BankNumber").css({"background-color": "#f4f4f4"});
			jQuery("#clickandpledge_echeck_AccountNumber").prop("disabled", true);
			jQuery("#clickandpledge_echeck_AccountNumber").css({"background-color": "#f4f4f4"});
			jQuery("#clickandpledge_echeck_retypeAccountNumber").prop("disabled", true);
			jQuery("#clickandpledge_echeck_retypeAccountNumber").css({"background-color": "#f4f4f4"});
				
			});
			</script>
			
			<?php
}
			
$postrecdefaultpayment  = "";
			    $paymntdesc="";
				if(isset($this->description) && $this->description != '')
			    {
					$paymntdesc= $this->description;
				}
				if(isset($this->paymentsettings_details['description']) && $this->paymentsettings_details['description'] != '')
			    {
				    $paymntdesc= $this->paymentsettings_details['description'];
				}
			
			?>
			<p><?php _e($testmodedesc, 'woothemes'); ?></p>
			
			<p><?php echo stripslashes_deep($paymntdesc); ?></p>
			<?php
			if(WC()->cart->total == 0 && ((isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == 'zerocp') ) &&  ((!isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] != 'zerocc') ) ){
			//	echo '<style>li.wc_payment_method.payment_method_clickandpledge {display: none;}</style>';
			}
			if(count($this->Paymentmethods) > 0) {
			if($this->recurring_details['isRecurring_recurring'] == 1 && WC()->cart->total > 0) { ?>
				<input type="hidden" name="hdnrecdtl" id="hdnrecdtl" value="<?php echo $this->recurring_details['indefinite']?>">

				<script type="text/javascript">
					
				manageshowhideRecpay();manageRecpay();
				function manageshowhideRecpay() { 
				if(jQuery('.recpayoptions:checked').val() == 'One Time Only') {	
						jQuery('#dvperdcty').hide();
						jQuery('#dvrecurtyp').hide();
						jQuery('#dvnoofpymnts').hide();
				
									
					} else {	
						jQuery('#dvperdcty').show();
					    jQuery('#dvrecurtyp').show();	
						jQuery('#dvnoofpymnts').show();
						
						
						}
						manageRecpay();
					}
					
					function manageRecpay() { 
					if(jQuery('.recpayoptions:checked').val() == 'Recurring') {
					
					 if(jQuery('.cnpflow').length > 0) { 
					
					 if(jQuery('.cnpflow').val() == 'Installment' && jQuery('#hdnrecdtl').val() == 'indefinite_openfield') {
					 		
					if(jQuery('#clickandpledge_Installment').val() != '' && jQuery('#clickandpledge_Installment').val() <= 998){jQuery('#clickandpledge_Installment').val(jQuery('#clickandpledge_Installment').val());}else{jQuery('#clickandpledge_Installment').val('998');}
						
						} 
						if(jQuery('.cnpflow').val() == 'Subscription' &&  jQuery('#hdnrecdtl').val() == 'indefinite_openfield') {
						if(jQuery('#clickandpledge_Installment').val() != ''){jQuery('#clickandpledge_Installment').val(jQuery('#clickandpledge_Installment').val());}else{jQuery('#clickandpledge_Installment').val('999');}			
						} 
					}
					}
					}
			
				
				
				</script>
				
				<?php
				if(in_array("Credit Card", $this->Paymentmethods) || in_array("eCheck", $this->Paymentmethods))
				{
				?>			
				
					<span>
					<!--<input type="checkbox" name="clickandpledge_isRecurring" id="clickandpledge_isRecurring" onclick="isRecurring()"> -->
					<strong><?php echo __($this->settings['RecurringLabel'], 'woocommerce') ?> </strong></span>
					<?php 

$cnprecpstarry = explode("&",$_POST['post_data']); 
$mrec_array = preg_grep('/^clickandpledge_isRecurring\=.*/', $cnprecpstarry);
foreach($mrec_array as $psrrxx => $psrrval) {
	 $postrecdefaultpaymentarr= explode("=",$psrrval);
	  $postrecdefaultpayment = urldecode($postrecdefaultpaymentarr[1]);
  } 
  
?>
				<table  style="border-collapse:collapse;border: 0px solid rgba(0, 0, 0, 0.1) !important;">
				<?php if($this->recurring_details['isRecurring_oto'] != "" ){ //echo $postrecdefaultpayment;
					if($postrecdefaultpayment == ""){
						$selpostval = $this->recurring_details['dfltpayoptn'];
					}
					else{
						$selpostval = $postrecdefaultpayment;
					}
					?> 
				<tr><td style="border:none;outline:none;">
				 <label for="clickandpledge_cart_type">
				<?php echo __($this->recurring_details['payoptn'], 'woocommerce') ?><span class="required" style="color:red;">*</span> </label>
			    </td><td style="border:none;outline:none;">
				<input type='radio' class='recpayoptions' name='clickandpledge_isRecurring' id='clickandpledge_isRecurringo' style='margin: 0 0 0 0;' value='One Time Only'  onclick='manageshowhideRecpay();' 
				<?php if($selpostval== "One Time Only"){echo "checked";}?>> One Time Only
				   <input type='radio' class='recpayoptions' name='clickandpledge_isRecurring' id='clickandpledge_isRecurringr'  style="margin: 0 0 0 0;" value='Recurring' onclick='manageshowhideRecpay();' 
				<?php if($selpostval == "Recurring"){echo "checked";}
										 ?>> Recurring
				
				 </td></tr>
				 <?php } else { ?>
				 <input type="hidden" name="clickandpledge_isRecurring" id="clickandpledge_isRecurring" value="Recurring" />
				 <?php }?>
				 <tr id="dvrecurtyp" ><td style="border:none;outline:none;">
				
					<label for="clickandpledge_cart_number"><?php echo __($this->recurring_details['rectype'], 'woocommerce') ?> <span class="required" style="color:red;">*</span></label>
			</td><td style="border:none;outline:none;">
					    <?php  
						  if(count($this->RecurringMethod) > 1 ){
						?>
							<select id="clickandpledge_RecurringMethod" name="clickandpledge_RecurringMethod"  class="cnpflow" onchange="manageRecpay();">
								<?php foreach ($this->RecurringMethod as $r) : ?>
											<option value="<?php echo $r ?>" <?php selected( $this->recurring_details['dfltrectypoptn'],$r); ?>><?php echo $r; ?></options>
								<?php endforeach; ?>			
							</select>
						<?php
					}
						
						else
						{
						  if(isset($this->RecurringMethod["Installment"]) && $this->RecurringMethod["Installment"] !== ""){
						     echo $this->RecurringMethod["Installment"];
							 echo "<input type='hidden' name='clickandpledge_RecurringMethod' id='clickandpledge_RecurringMethod' value='".$this->RecurringMethod["Installment"]."'>";
						   }
						    if(isset($this->RecurringMethod["Subscription"]) && $this->RecurringMethod["Subscription"] !== ""){
						  	 echo $this->RecurringMethod["Subscription"];
							 echo "<input type='hidden' name='clickandpledge_RecurringMethod' id='clickandpledge_RecurringMethod' value='".$this->RecurringMethod["Subscription"]."'>";
						   }
					}
						?>
						</td></tr><tr id="dvperdcty"><td style="border:none;outline:none;">
								
					<?php echo __($this->recurring_details['periodicity'], 'woocommerce');?>
					 <span class="required" style="color:red;">*</span></td><td style="border:none;outline:none;"><?php  
						 if(count($this->Periodicity) > 1 ){
						?>
						<select id="clickandpledge_Periodicity" name="clickandpledge_Periodicity" class="cnpflow">
					    
						<?php foreach ($this->Periodicity as $p) : ?>
									<option value="<?php echo $p ?>"><?php echo $p; ?></options>
						<?php endforeach; ?>
					</select>
					<?php
					}
						else
						{
							$this->pselectedval ="";
						   if(isset($this->Periodicity["Week"]) && $this->Periodicity["Week"] !== ""){
						     echo $this->Periodicity["Week"];
							 echo "<input type='hidden' name='clickandpledge_Periodicity' id='clickandpledge_Periodicity' value='".$this->Periodicity["Week"]."'>";
						   }
						   if(isset($this->Periodicity["2 Weeks"]) && $this->Periodicity["2 Weeks"] !== ""){
						     echo $this->Periodicity["2 Weeks"];
							 echo "<input type='hidden' name='clickandpledge_Periodicity' id='clickandpledge_Periodicity' value='".$this->Periodicity["2 Weeks"]."'>";
						   }
						   if(isset($this->Periodicity["Month"]) && $this->Periodicity["Month"] !== ""){
						     echo $this->Periodicity["Month"];
							 echo "<input type='hidden' name='clickandpledge_Periodicity' id='clickandpledge_Periodicity' value='".$this->Periodicity["Month"]."'>";
						   }
						   if(isset($this->Periodicity["2 Months"]) && $this->Periodicity["2 Months"] !== ""){
						     echo $this->Periodicity['2 Months'];
							 echo "<input type='hidden' name='clickandpledge_Periodicity' id='clickandpledge_Periodicity' value='".$this->Periodicity["2 Months"]."'>";
						   }
						   if(isset($this->Periodicity["Quarter"]) && $this->Periodicity["Quarter"] !== ""){
						     echo $this->Periodicity["Quarter"];
							 echo "<input type='hidden' name='clickandpledge_Periodicity' id='clickandpledge_Periodicity' value='".$this->Periodicity["Quarter"]."'>";
						   }
						   if(isset($this->Periodicity["6 Months"]) && $this->Periodicity["6 Months"] !== ""){
						     echo $this->Periodicity["6 Months"];
							 echo "<input type='hidden' name='clickandpledge_Periodicity' id='clickandpledge_Periodicity' value='".$this->Periodicity["6 Months"]."'>";
						   }
						   if(isset($this->Periodicity["Year"]) && $this->Periodicity["Year"] !== ""){
						
						     echo $this->Periodicity["Year"];
							 echo "<input type='hidden' name='clickandpledge_Periodicity' id='clickandpledge_Periodicity' value='".$this->Periodicity["Year"]."'>";
						   }
						   
						    
						}
						?>
				</td></tr><tr id="dvnoofpymnts"> <td style="border:none;outline:none;">
				<?php echo __($this->recurring_details['noofpayments'], 'woocommerce') ?> <span class="required" style="color:red;">*</span></td><td style="border:none;outline:none;"><p>
				<?php if($this->recurring_details['indefinite'] == 'fixednumber'){?>	
				<label for="clickandpledge_cart_number"> <?php echo $this->recurring_details['dfltnoofpaymnts'] ;?> </label>
				<input type="hidden" name="clickandpledge_indefinite" id="clickandpledge_indefinite" value="no" />
				<input type="hidden" name="clickandpledge_Installment" id="clickandpledge_Installment" value="<?php echo $this->recurring_details['dfltnoofpaymnts'];?>" />
				
				<?php }?>
				<?php if($this->recurring_details['indefinite'] == '1'){?>	
				<label for="clickandpledge_cart_number"> Indefinite Recurring Only</label>
				<input type="hidden" name="clickandpledge_indefinite" id="clickandpledge_indefinite" value="on" />
				<input type="hidden" name="clickandpledge_Installment" id="clickandpledge_Installment" value="999" />
				
				<?php }?>
				<?php if($this->recurring_details['indefinite'] == 'openfield'){?>	
				
				<input type="text" class="input-text " id="clickandpledge_Installment" name="clickandpledge_Installment" maxlength="3" style="margin-right:2px; width:150px;" value="<?php echo $this->recurring_details['dfltnoofpaymnts'];?>" />
				<input type="hidden" name="clickandpledge_indefinite" id="clickandpledge_indefinite" value="no" />
			
				<?php }?>
				<?php if($this->recurring_details['indefinite'] == 'indefinite_openfield'){?>	
				<input type="text" class="input-text " id="clickandpledge_Installment" name="clickandpledge_Installment" maxlength="3" style="width:150px; margin-right:2px;" value="<?php echo $this->recurring_details['dfltnoofpaymnts'];?>" />
				<input type="hidden" name="clickandpledge_indefinite" id="clickandpledge_indefinite" value="no" />
				<?php }?>
					<script>
					jQuery('#clickandpledge_Installment').keypress(function(e) {
						var a = [];
						var k = e.which;

						for (i = 48; i < 58; i++)
							a.push(i);

						if (!(a.indexOf(k)>=0))
							e.preventDefault();
					});
					</script>
					
				</p>
						
				<?php }
				
				?></td></tr>
				</table><br>
				<?php
				 }
				echo '<span id="payment_methods"> <strong>Payment Methods</strong> <br> ';
				echo '<div style="display:none;"><input type="hidden" name="cnpversion" id="cnpversion" value="2.24070000-WP6.6.1-WC9.1.2"/></div>';
			if(isset($this->zeropaymentsettings_details['zerocustompaynt']) && $this->zeropaymentsettings_details['zerocustompaynt'] != '')
			{
            $cnpfreecustnm = $this->zeropaymentsettings_details['zerocustompaynt'];
			}
            else
            {
            $cnpfreecustnm ='Free';
            }
    
				if(WC()->cart->total != 0 && !in_array($this->defaultpayment,array('CreditCard','eCheck'))) { 
					if(count($this->CustomPayments) > 0) {
						$this->defaultpayment = $this->defaultpayment;
					} else {
						$this->CustomPayments[] = $cnpfreecustnm;
						$this->defaultpayment = $this->CustomPayments[0];
					}
				} 
            else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && (!isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == '') && in_array($this->defaultpayment, array('CreditCard','eCheck')))
                { 
					if($this->defaultpayment == 'CreditCard') {
						$this->defaultpayment = 'CreditCard';
					} else {
						$this->Paymentmethods['Free'] = $cnpfreecustnm;
						$this->CustomPayments[] = $cnpfreecustnm;
						$this->defaultpayment = $this->CustomPayments[0];
					}
				}
             else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && (isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == 'zerocp') && (!in_array($this->defaultpayment, array('CreditCard','eCheck'))))
                {
					 
						$this->defaultpayment = 'CreditCard';
					
				}
            else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && (!isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == '') && (!in_array($this->defaultpayment, array('CreditCard','eCheck'))))
                {
				
						$this->defaultpayment = 'CreditCard';
					
				}
             else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && (isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == 'zerocp') && in_array($this->defaultpayment, array('CreditCard','eCheck')))
                { 
				
						$this->defaultpayment = $this->defaultpayment;
					
				}
            else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == 'zerocp') && in_array($this->defaultpayment, array('CreditCard','eCheck'))){
					
						$this->Paymentmethods['Free'] = $cnpfreecustnm;
						$this->CustomPayments[] = $cnpfreecustnm;
						$this->defaultpayment = $cnpfreecustnm;
					
				}
      
		 if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && (isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == 'zerocp'))
         {
			
         $this->Paymentmethods= array();
         $this->Paymentmethods['CreditCard'] = 'Credit Card';
         $this->defaultpayment = 'CreditCard';
        $this->Paymentmethods[$this->zeropaymentsettings_details['zerocustompaynt']] = $this->zeropaymentsettings_details['zerocustompaynt'];
       
		}else  if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && (!isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == ''))
         {
			
         $this->Paymentmethods= array();
         $this->Paymentmethods['CreditCard'] = 'Credit Card';
      $this->defaultpayment ='CreditCard';
       
		} 
         else  if(WC()->cart->total == 0 && (!isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == '') && (isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == 'zerocp') ){
				
						$this->Paymentmethods= array();
         				$this->Paymentmethods['Free'] = $cnpfreecustnm;
						$this->CustomPayments[] = $cnpfreecustnm;
						$this->defaultpayment = $cnpfreecustnm;
					
				}
            ?>
           <script type="text/javascript">
           jQuery('[name="cnp_payment_method_selection"]').removeAttr('checked');
           jQuery("input[name=cnp_payment_method_selection][value='<?php echo $this->defaultpayment;?>']").prop('checked', true);
           </script>
           <?php
           //echo $this->defaultpayment;
            foreach($this->Paymentmethods as $pkey => $pval) {
	
                   if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocustompaynt']) && $this->zeropaymentsettings_details['zerocustompaynt'] == 'zerocp') && in_array($pkey, array('CreditCard','eCheck'))){
						if(in_array($this->defaultpayment,array('CreditCard','eCheck'))) {
							if(count($this->CustomPayments) > 0) {
								$this->defaultpayment = $this->CustomPayments[0];
							}
						}
					} else if(WC()->cart->total == 0 ){
                   
						if($pkey == $this->defaultpayment) { //echo "in".$pkey."---".$this->defaultpayment;
							echo '<input type="radio" id="cnp_payment_method_selection1_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection1" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked="checked">&nbsp<b>'.$pval.'</b>   ';
						} else {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection1" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					}
				/*	else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocustompaynt']) && $this->zeropaymentsettings_details['zerocustompaynt'] == 'zerocp') && !in_array($pkey, array('eCheck'))){
						if($pkey == $this->defaultpayment) {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection1" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked="checked">&nbsp<b>'.$pval.'</b>   ';
						} else {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection1" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					} else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && !in_array($pkey, array('eCheck'))){
						if($pkey == $this->defaultpayment) {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection1" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked="checked">&nbsp<b>'.$pval.'</b>   ';
						} else {
                       // echo "in".$pkey;
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection1" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					}*/else if(WC()->cart->total > 0){
						
						if($pkey == $this->defaultpayment) {  //echo "in2".$pkey."---".$this->defaultpayment;
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection1" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked="checked">&nbsp<b>'.$pval.'</b>   ';
						} else {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection1" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					}
				}
              ?>
       
       
            <?php
			/*	foreach($this->Paymentmethods as $pkey => $pval) {
               
              			if($pkey == $this->defaultpayment) { 
                         // echo $pkey."----".$this->defaultpayment;
             
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);"  style="margin: 0 0 0 0;" value="'.$pkey.'"   checked="checked">&nbsp<b>'.$pval.'</b>   ';
						} else {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					/*if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == 'zerocp')  && (!isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] != 'zerocc') && in_array($pkey, array('CreditCard','eCheck'))){
						if(in_array($this->defaultpayment,array('CreditCard','eCheck'))) { 
							if(count($this->CustomPayments) > 0) {
								$this->defaultpayment = $this->CustomPayments[0];
							}
						}
					} else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && (isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == 'zerocp') && in_array($pkey, array('CreditCard'))){
              
						if($pkey == $this->defaultpayment) { 
                          echo $pkey."----".$this->defaultpayment;
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked="checked" >&nbsp<b>'.$pval.'</b>   ';
						} else {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					}
					else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == 'zerocp') && !in_array($pkey, array('eCheck'))){
						if($pkey == $this->defaultpayment) {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked>&nbsp<b>'.$pval.'</b>   ';
						} else {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					} else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && !in_array($pkey, array('eCheck'))){
						if($pkey == $this->defaultpayment) {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked>&nbsp<b>'.$pval.'</b>   ';
						} else {
                       // echo "in".$pkey;
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					}
                  else if(WC()->cart->total == 0 && (!isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == '') && (!isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == '')){
						
						if($pkey == $this->defaultpayment) {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked>&nbsp<b>'.$pval.'</b>   ';
						} else {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					}
                 else if(WC()->cart->total == 0 && (isset($this->zeropaymentsettings_details['zerocreditcard']) && $this->zeropaymentsettings_details['zerocreditcard'] == 'zerocc') && (!isset($this->zeropaymentsettings_details['zerocustom']) && $this->zeropaymentsettings_details['zerocustom'] == '') && (!in_array($pkey, array('CreditCard')))){
							echo "hello1";
						if($pkey == $this->defaultpayment) {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked>&nbsp<b>'.$pval.'</b>   ';
						} else {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					}else if(WC()->cart->total > 0){
						echo "hello".$this->defaultpayment;
						if($pkey == $this->defaultpayment) {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'" checked>&nbsp<b>'.$pval.'</b>   ';
						} else {
							echo '<input type="radio" id="cnp_payment_method_selection_'.$pkey.'" name="cnp_payment_method_selection" class="cnp_payment_method_selection" onclick="displaysection(this.value);" style="margin: 0 0 0 0;" value="'.$pkey.'"> <b>'.$pval.'</b>   ';
						}
					}
				}*/
				echo '</span>';
			}
			?>
			<style>
		
		    .wc_payment_method input[type=radio]{ 
				display: inline-block;
    			vertical-align: middle;   
  			}
			.cnpflow {
				-webkit-appearance:menu; 
				-webkit-border-radius: 2px; 
				-webkit-box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1); 
				-webkit-padding-end: 20px; 
				-webkit-padding-start: 2px; 
				-webkit-user-select: none; 
				-webkit-linear-gradient(#FAFAFA, #F4F4F4 40%, #E5E5E5); 
				background-position: 97% center; 
				background-repeat: no-repeat; 
				border: 1px solid #AAA; 
				color: #555; 
				font-size: inherit; 
				overflow: hidden; 
				padding: 5px 10px;
				text-overflow: ellipsis; 
				white-space: nowrap; 
				width: 100%;
	       	} 
		   	.cnpccflow {
				-webkit-appearance:menu; 
				-webkit-border-radius: 2px; 
				-webkit-box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1); 
				-webkit-padding-end: 20px; 
				-webkit-padding-start: 2px; 
				-webkit-user-select: none; 
				-webkit-linear-gradient(#FAFAFA, #F4F4F4 40%, #E5E5E5); 
				background-position: 97% center; 
				background-repeat: no-repeat; 
				border: 1px solid #AAA; 
				color: #555; 
				font-size: inherit; 
				overflow: hidden; 
				padding: 5px 10px;
				text-overflow: ellipsis; 
				white-space: nowrap; 
				width: 150px;
	       	} 
			</style>
			<script>
				function displaysection(sec) {
					if(sec == 'CreditCard') {
						jQuery('#cnp_CreditCard_div').show();					
						jQuery('#cnp_eCheck_div').hide();
						jQuery('#cnp_Custom_div').hide();
						
					} else if(sec == 'eCheck') {
						jQuery('#cnp_CreditCard_div').hide();					
						jQuery('#cnp_eCheck_div').show();
						jQuery('#cnp_Custom_div').hide();
						
					}
					else if(sec == 'Free') {
						jQuery('#cnp_CreditCard_div').hide();					
						jQuery('#cnp_eCheck_div').hide();
						jQuery('#cnp_Custom_div').hide();
						
					} else {
						jQuery('#cnp_CreditCard_div').hide();					
						jQuery('#cnp_eCheck_div').hide();
						jQuery('#cnp_Custom_div').show();
						
					}
				}
			</script>
<?php 
$postdefaultpayment  = "";
$cnppstarry = explode("&",$_POST['post_data']); 
$m_array = preg_grep('/^cnp_payment_method_selection\=.*/', $cnppstarry);
//print_r($m_array);
foreach($m_array as $psxx => $psval) {
	 $postdefaultpaymentarr= explode("=",$psval);
	 $postdefaultpayment = $postdefaultpaymentarr[1];
  } 
if($this->defaultpayment != ""){ $postdefaultpayment =  $this->defaultpayment; } else { $postdefaultpayment =  $postdefaultpayment;}
//echo $postdefaultpayment;
?>
			<div style="display:<?php if($postdefaultpayment == 'CreditCard') echo 'block'; else echo 'none';?>;" id="cnp_CreditCard_div">
			<p class="" style="margin:0 0 10px"> </p>
			<?php
			
			if (count($available_cards) > 0) { ?>
				<p ><?php 
				if(in_array('Visa', $available_cards))
					echo "<img src='".WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . "/images/visa.jpg' title='Visa' alt='Visa' style='display: inline !important'/>";
				if(in_array('American Express', $available_cards))
					echo " <img src='".WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . "/images/amex.jpg' title='Visa' alt='Visa' style='display: inline !important'/>";
				if(in_array('Discover', $available_cards))
					echo " <img src='".WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . "/images/discover.jpg' title='American Express' alt='American Express' style='display: inline !important'/>";
				if(in_array('MasterCard', $available_cards))
					echo " <img src='".WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . "/images/mastercard.gif' title='MasterCard' alt='MasterCard' style='display: inline !important'/>";
				if(in_array('JCB', $available_cards))
					echo " <img src='".WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . "/images/JCB.jpg' title='JCB' alt='JCB' style='display: inline !important'/>";
				?></p>
			<?php } ?>
				<p >
					<label for="clickandpledge_cart_number"><?php echo __("Name on Card", 'woocommerce') ?> <span class="required" style="color:red;">*</span></label>
					<input type="text" class="input-text" name="clickandpledge_name_on_card" placeholder="Name on Card" maxlength="50"/>
				</p>
				<div class="clear"></div>
				
				<p class="form-row ">
					<label for="clickandpledge_cart_number"><?php echo __("Credit Card number", 'woocommerce') ?> <span class="required">*</span></label>
					<input type="text" class="input-text" name="clickandpledge_card_number"  id="clickandpledge_card_number" placeholder="Credit Card number" style="color:#141412; font-weight:normal;" maxlength="17"/>
				</p>
				<p class="form-row form-row-last">
					<label for="clickandpledge_card_csc"><?php _e("Card Verification (CVV)", 'woocommerce') ?> <span class="required">*</span></label>
					<input type="text" class="input-text" id="clickandpledge_card_csc" name="clickandpledge_card_csc" maxlength="4" style="width:69px" placeholder="cvv"/>					
					<span class="help clickandpledge_card_csc_description"></span>
				</p>
				
				
				
				<p class="form-row ">
					<label for="cc-expire-month"><?php echo __("Expiration Date", 'woocommerce') ?> <span class="required">*</span></label>
					<select name="clickandpledge_card_expiration_month" id="cc-expire-month" class="cnpccflow">
						<option value=""><?php _e('Month', 'woocommerce') ?></option>
						<?php
							$months = array();
							for ($i = 1; $i <= 12; $i++) {
							    $timestamp = mktime(0, 0, 0, $i, 1);
							    $months[date('m', $timestamp)] = date('F', $timestamp);
							}
							foreach ($months as $num => $name) {
					            printf('<option value="%s">%s</option>', $num, $name);
					        }
					        
						?>
					</select>
					<select name="clickandpledge_card_expiration_year" id="cc-expire-year" class="cnpccflow">
						<option value=""><?php _e('Year', 'woocommerce') ?></option>
						<?php
							$years = array();
							for ($i = date('Y'); $i <= date('Y') + 20; $i++) {
							    printf('<option value="%u">%u</option>', $i, $i);
							}
						?>
					</select>
				</p>
				
							
			</div> <!-- Credit Card Section End-->
			<div style="display:<?php if($postdefaultpayment == 'eCheck') echo 'block'; else echo 'none';?>;" id="cnp_eCheck_div">
				<p class="" style="margin:0 0 10px"> </p>
				<?php
				if($testmodedesc!="")
				{
					echo '<p class="" style="margin:0 0 10px;color:red;">eCheck does not support test transactions</p>';
				}
				echo "<p><img src='".WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . "/images/eCheck.png' title='eCheck' alt='eCheck'/></p>";
			 ?>
				
				<table  style="border-collapse:collapse;border: 0px solid rgba(0, 0, 0, 0.1) !important;">
				<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_AccountType"><?php echo __("Account Type", 'woocommerce') ?><span class="required" style="color:red;">*</span>                            </label>
					</td><td style="border:none;outline:none;">					
					<select class="cnpflow" name="clickandpledge_echeck_AccountType" id="clickandpledge_echeck_AccountType">						
						<option value="SavingsAccount">SavingsAccount</option>
						<option value="CheckingAccount">CheckingAccount</option>
					</select>
				</td></tr>
				<div class="clear"></div>
				<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_NameOnAccount"><?php echo __("Name On Account", 'woocommerce') ?><span class="required" style="color:red;">*</span>                  </label></td><td style="border:none;outline:none;">	
					<input type="text" class="input-text" name="clickandpledge_echeck_NameOnAccount" id="clickandpledge_echeck_NameOnAccount" placeholder="Name On Account" maxlength="17"/>
				</td></tr>
				<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_IdType"><?php echo __("Type of ID", 'woocommerce') ?>                                    </label></td><td style="border:none;outline:none;">	
					<select class="cnpflow" name="clickandpledge_echeck_IdType" id="clickandpledge_echeck_IdType">
						<option value="Driver">Driver</option>
						<option value="Military">Military</option>
						<option value="State">State</option>
					</select>
				</td></tr>
				<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_CheckType"><?php echo __("Check Type", 'woocommerce') ?> <span class="required" style="color:red;">*</span>                              </label></td><td style="border:none;outline:none;">	
					<select class="cnpflow" name="clickandpledge_echeck_CheckType" id="clickandpledge_echeck_CheckType">
						<option value="Company">Company</option>
						<option value="Personal">Personal</option>
					</select>
				</td></tr>
				<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_CheckNumber"><?php echo __("Check Number", 'woocommerce') ?> <span class="required" style="color:red;">*</span>                     </label></td><td style="border:none;outline:none;">	
					<input type="text" class="input-text" id="clickandpledge_echeck_CheckNumber" name="clickandpledge_echeck_CheckNumber" placeholder="Check Number" maxlength="17"/>
				</p>
                <?php   $cnpcur = $this->getwcCnPCurrency($this->AccountID);
                if($cnpcur != 124){?>
				<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_RoutingNumber"><?php echo __("Routing Number", 'woocommerce') ?> <span class="required" style="color:red;">*</span>                  </label></td><td style="border:none;outline:none;">	
					<input type="text" class="input-text" id="clickandpledge_echeck_RoutingNumber" name="clickandpledge_echeck_RoutingNumber" placeholder="Routing Number" maxlength="17"/>
				</td></tr>
                                   <?php } else
                {
                
                ?>	<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_BankNumber"><?php echo __("Bank Number", 'woocommerce') ?> <span class="required" style="color:red;">*</span>                  </label></td><td style="border:none;outline:none;">	
					<input type="text" class="input-text" id="clickandpledge_echeck_BankNumber" name="clickandpledge_echeck_BankNumber" placeholder="Bank Number" maxlength="3"/>
				</td></tr>	<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_TransitNumber"><?php echo __("Transit Number", 'woocommerce') ?> <span class="required" style="color:red;">*</span>                  </label></td><td style="border:none;outline:none;">	
					<input type="text" class="input-text" id="clickandpledge_echeck_TransitNumber" name="clickandpledge_echeck_TransitNumber" placeholder="Transit Number" maxlength="5"/>
				</td></tr>
                    <?php } ?>
				<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_AccountNumber"><?php echo __("Account Number", 'woocommerce') ?> <span class="required" style="color:red;">*</span>                  </label></td><td style="border:none;outline:none;">	
					<input type="text" class="input-text" id="clickandpledge_echeck_AccountNumber" name="clickandpledge_echeck_AccountNumber" placeholder="Account Number" maxlength="17"/>
				</p>
				<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_AccountNumber"><?php echo __("Re-Type Account Number", 'woocommerce') ?> <span class="required" style="color:red;">*</span></label></td><td style="border:none;outline:none;">	
					<input type="text" class="input-text" id="clickandpledge_echeck_retypeAccountNumber" name="clickandpledge_echeck_retypeAccountNumber" placeholder="Re-Type Account Number" maxlength="17"/>
				</td></tr>
				</table>
		
			</div>
			<div style="display:<?php if(($postdefaultpayment != 'CreditCard') && ($postdefaultpayment != 'eCheck') && ($postdefaultpayment != 'Free') ) echo 'block'; else echo 'none';?>;" id="cnp_Custom_div">
			<p class="" style="margin:0 0 10px"> </p>
			<?php if($this->ReferenceNumber_Label != "")
			{
			?><table style="border-collapse:collapse;border: 0px solid rgba(0, 0, 0, 0.1) !important;">
			<tr> <td style="border:none;outline:none;">
					<label for="clickandpledge_echeck_AccountNumber"><?php echo __($this->ReferenceNumber_Label, 'woocommerce') ?> </label></td><td style="border:none;outline:none;">	
					<input type="text"  id="clickandpledge_cp_ReferenceNumber" name="clickandpledge_cp_ReferenceNumber" placeholder="<?php echo $this->ReferenceNumber_Label;?>" maxlength="50"/>
				</td></tr>
				</table>
			<?php
			}
			
			?>
			</div>
			
			<?php 
		}
		
		/**
	     * Process the payment
	     */
		function process_payment($order_id) {
	//	print_r($order_id);exit;
		global $woocommerce;
	
			$order = new WC_Order( $order_id );
	// Validate plugin settings
			//print_r($order);exit;
			if (!$this->validate_settings()) :
				$cancelNote = __('Order was cancelled due to invalid settings (check your API credentials and make sure your currency is supported).', 'woothemes');
				$order->add_order_note( $cancelNote );
				wc_add_notice( __( 'Payment was rejected due to configuration error.', 'woocommerce' ), 'error' );
				return false;
			endif;
	
			// Send request to clickandpledge
			try {
				$url = $this->liveurl;
				if ($this->testmode == 'yes') :
					$url = $this->testurl;
				endif;
	
				$request = new clickandpledge_request($url);
				
				$posted_settings = array();
				$posted_settings['AccountID'] = $this->AccountID;
				$posted_settings['AccountGuid'] = $this->AccountGuid;
				$posted_settings['ConnectCampaignAlias'] = $this->ConnectCampaignAlias;
				$posted_settings['cnp_email_customer'] = $this->settings['cnp_email_customer'];
				$posted_settings['Total'] = $order->order_total;
				$posted_settings['OrderMode'] = $this->testmode;//$this->testmode
				$posted_settings['Preauthorization'] = isset($this->settings['Preauthorization']) ? $this->settings['Preauthorization'] : 'no';		
				$posted_settings['OrganizationInformation'] = $this->settings['OrganizationInformation'];			
				$posted_settings['TermsCondition'] = $this->settings['TermsCondition'];	
				$response = $request->send($posted_settings, $_POST, $order);
			
			} catch(Exception $e) {
				wc_add_notice( __( 'There was a connection error', 'woocommerce' ) . ': "' . $e->getMessage() . '"', 'error' );
				return;
			}
	
			if ($response['status'] == 'Success') {
				$order->add_order_note( __('Click & Pledge payment completed', 'woothemes') . ' (Transaction ID: ' . $response['TransactionNumber'] . ')' );
				$order->payment_complete();
				$woocommerce->cart->empty_cart();
				// Return thank you page redirect
				return array(
					'result' 	=> 'success',
					'redirect'	=> $this->get_return_url( $order )
				);
				}
			else {
				$cancelNote = __('Click & Pledge payment failed', 'woothemes') . ' (Transaction ID: ' . $response['TransactionNumber'] . '). ' . __('Payment was rejected due to an error', 'woothemes') . ': "' . $response['error'] . '". ';
	
				$order->add_order_note( $cancelNote );
				wc_add_notice( __( 'Payment error', 'woocommerce' ) . ': ' . $response['error'] . '('.$response['ResultCode'].')', 'error' );
			}

		}
	
	function cc_check($number) {

	  // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
	  $number=preg_replace('/\D/', '', $number);

	  // Set the string length and parity
	  $number_length=strlen($number);
	  $parity=$number_length % 2;

	  // Loop through each digit and do the maths
	  $total=0;
	  for ($i=0; $i<$number_length; $i++) {
		$digit=$number[$i];
		// Multiply alternate digits by two
		if ($i % 2 == $parity) {
		  $digit*=2;
		  // If the sum is two digits, add them together (in effect)
		  if ($digit > 9) {
			$digit-=9;
		  }
		}
		// Total up the digits
		$total+=$digit;
	  }

	  // If the total mod 10 equals 0, the number is valid
	  return ($total % 10 == 0) ? TRUE : FALSE;

	}
	
	function CreditCardCompany($ccNum)
	 {
			/*
				* mastercard: Must have a prefix of 51 to 55, and must be 16 digits in length.
				* Visa: Must have a prefix of 4, and must be either 13 or 16 digits in length.
				* American Express: Must have a prefix of 34 or 37, and must be 15 digits in length.
				* Diners Club: Must have a prefix of 300 to 305, 36, or 38, and must be 14 digits in length.
				* Discover: Must have a prefix of 6011, and must be 16 digits in length.
				* JCB: Must have a prefix of 3, 1800, or 2131, and must be either 15 or 16 digits in length.
			*/
	 
			if (preg_match("/^5[1-5][0-9]{14}$/", $ccNum))
					return "MasterCard";
	 
			if (preg_match("/^4[0-9]{12}([0-9]{3})?$/", $ccNum))
					return "Visa";
	 
			if (preg_match("/^3[47][0-9]{13}$/", $ccNum))
					return "American Express";
	 
			if (preg_match("/^3(0[0-5]|[68][0-9])[0-9]{11}$/", $ccNum))
					return "Diners Club";
	 
			if (preg_match("/^6011[0-9]{12}$/", $ccNum))
					return "Discover";
	 
			if (preg_match("/^(3[0-9]{4}|2131|1800)[0-9]{11}$/", $ccNum))
					return "JCB";
	 }
	/**
	     * Validate the payment form
	     */
		function validate_fields() {
			global $woocommerce;
			$name_on_card 	    = isset($_POST['clickandpledge_name_on_card']) ? $_POST['clickandpledge_name_on_card'] : '';
			$billing_country 	= isset($_POST['billing_country']) ? $_POST['billing_country'] : '';
			$card_type 			= isset($_POST['clickandpledge_card_type']) ? $_POST['clickandpledge_card_type'] : '';
			$card_number 		= isset($_POST['clickandpledge_card_number']) ? $_POST['clickandpledge_card_number'] : '';
			$card_csc 			= isset($_POST['clickandpledge_card_csc']) ? $_POST['clickandpledge_card_csc'] : '';
			$card_exp_month		= isset($_POST['clickandpledge_card_expiration_month']) ? $_POST['clickandpledge_card_expiration_month'] : '';
			$card_exp_year 		= isset($_POST['clickandpledge_card_expiration_year']) ? $_POST['clickandpledge_card_expiration_year'] : '';
			$isRecurring        = isset($_POST['clickandpledge_isRecurring']) ? $_POST['clickandpledge_isRecurring'] : '';
			
			$cnp_payment_method_selection = isset($_POST['cnp_payment_method_selection']) ? $_POST['cnp_payment_method_selection'] : 'CreditCard';
			$customerrors = array();
			
				if(isset($_POST['clickandpledge_isRecurring']) && $_POST['clickandpledge_isRecurring'] == 'Recurring') { 
					if(empty($_POST['clickandpledge_Periodicity'])) {
							array_push($customerrors, 'Please select Periodicity');
						}
					if($_POST['clickandpledge_RecurringMethod'] == 'Installment' && $_POST['clickandpledge_indefinite'] == 'on')
						{
						   array_push($customerrors, 'Recurring type Installment not allow indefinite number of payments');
						}			
					if($_POST['clickandpledge_indefinite'] =='no') {;
                  
						if(empty($_POST['clickandpledge_Installment']) || $_POST['clickandpledge_Installment'] == "") {
							if($_POST['clickandpledge_RecurringMethod'] == 'Subscription') { 
								if(!empty($this->recurring_details['maxrecurrings_Subscription']))
								{
									array_push($customerrors, 'Please enter a periodicity between 2-'.$this->recurring_details['maxrecurrings_Subscription']);									
								} else {
									array_push($customerrors, 'Please enter number of paymenys between 2-999');
								}
							} else {
								if(!empty($this->recurring_details['maxrecurrings_Subscription']) )
								{
									array_push($customerrors, 'Please enter a periodicity between 2-'.$this->recurring_details['maxrecurrings_Subscription']);
								} else {
								//array_push($customerrors, 'Please enter number of payments between 2-998');
								}
							}							
						}
						if(!ctype_digit($_POST['clickandpledge_Installment'])) {
							array_push($customerrors, 'Please enter Numbers only in instalments');
						}
						if($_POST['clickandpledge_Installment'] == 1) {
							if($_POST['clickandpledge_RecurringMethod'] == 'Subscription') {
								array_push($customerrors, 'Installments should be greater than 2');
							} else {
								array_push($customerrors, 'Installments should be greater than 2');
							}
						}
						if(strlen($_POST['clickandpledge_Installment']) > 3) {
							if($_POST['clickandpledge_RecurringMethod'] == 'Subscription') {
								array_push($customerrors, 'Please enter number of paymenys between 2-999');
							} else {
								array_push($customerrors, 'Please enter number of paymenys between 2-998');
							}
						}
						
						if($_POST['clickandpledge_RecurringMethod'] == 'Subscription')
						{						
						
							if(!empty($this->recurring_details['maxrecurrings_Subscription']) && $_POST['clickandpledge_Installment'] > $this->recurring_details['maxrecurrings_Subscription']  )
							{
								array_push($customerrors, 'Please enter number of paymenys between 2-'.$this->recurring_details['maxrecurrings_Subscription'].' only');
							}
						}
						
						if($_POST['clickandpledge_RecurringMethod'] == 'Installment')
						{
							if($_POST['clickandpledge_Installment'] == 999  )
							{
								array_push($customerrors, 'Please enter number of paymenys between 2-998');
							}
							
							if(!empty($this->recurring_details['maxrecurrings_Subscription']) && $_POST['clickandpledge_Installment'] > $this->recurring_details['maxrecurrings_Subscription']  )
							{
								array_push($customerrors, 'Please enter number of paymenys between 2-'.$this->recurring_details['maxrecurrings_Subscription'].' only');
							}
						}
					} 
					
				}
			if($cnp_payment_method_selection == 'CreditCard') { //echo $_POST['clickandpledge_isRecurring'];
			
				
				// Name on card
				if(empty($name_on_card)) {
					array_push($customerrors, 'Please enter Name on Card');
				}			
				/*if (!preg_match("/^([a-zA-Z0-9\.\,\#\-\ \']){2,50}$/", $name_on_card)) {
					array_push($customerrors, 'Please enter the only Alphanumeric and space for Name on Card');
				}*/
				//Card Number
				if(empty($card_number)) {
					array_push($customerrors, 'Please enter Credit Card Number');
				}
				if(!empty($card_number) && strlen($card_number) < 13) {
					array_push($customerrors, 'Invalid Credit Card Number');
				}
				if(!empty($card_number) && strlen($card_number) > 19) {
					array_push($customerrors, 'Invalid Credit Card Number');
				}
				if(!empty($card_number) && !$this->cc_check($card_number)) {
					wc_add_notice( __( 'Invalid Credit Card Number', 'woocommerce' ), 'error' );
					return false;
				}
				
				//CVV
				if(empty($card_csc)) {
					array_push($customerrors, 'Please enter CVV');
				}			
				else if(!ctype_digit($card_csc)) {
					array_push($customerrors, 'Please enter Numbers only in Card Verification(CVV)');
				}	
				else if(( strlen($card_csc) < 3 ) && !preg_match("/^3[47][0-9]{13}$/", $card_number)) {
					array_push($customerrors, 'Please enter a number at least 3 or 4 digits in card verification (CVV)');
				}
				
				if (!empty($card_csc) && preg_match("/^3[47][0-9]{13}$/", $card_number)) {
					 if(( strlen($card_csc) < 4 )) {
					array_push($customerrors, 'AmEx uses a 4-digit CVV code.  Please verify and try again');
				}
				}
				if($card_number != ""){
				//Credit Card Validation					
				$selected_card = $this->CreditCardCompany($card_number);
				
				if(!in_array($selected_card, $this->available_cards))
				{
					array_push($customerrors, 'We are not accepting <b>'.$selected_card.'</b> type cards');
				}
			}		
				// Check card expiration data
				if(!ctype_digit($card_exp_month) || !ctype_digit($card_exp_year) ||
					 $card_exp_month > 12 ||
					 $card_exp_month < 1 ||
					 $card_exp_year < date('Y') ||
					 $card_exp_year > date('Y') + 20
				) {
					array_push($customerrors, 'Card Expiration Date is invalid');
				}
			} else if($cnp_payment_method_selection == 'eCheck') {
			  $clickandpledge_echeck_AccountType 	     = isset($_POST['clickandpledge_echeck_AccountType']) ? $_POST['clickandpledge_echeck_AccountType'] : '';
			  $clickandpledge_echeck_NameOnAccount 	     = isset($_POST['clickandpledge_echeck_NameOnAccount']) ? $_POST['clickandpledge_echeck_NameOnAccount'] : '';
			  $clickandpledge_echeck_IdType 	         = isset($_POST['clickandpledge_echeck_IdType']) ? $_POST['clickandpledge_echeck_IdType'] : '';
			  $clickandpledge_echeck_CheckType 	         = isset($_POST['clickandpledge_echeck_CheckType']) ? $_POST['clickandpledge_echeck_CheckType'] : '';
             $cnpcur = $this->getwcCnPCurrency($this->AccountID);
                if($cnpcur != 124){
			  $clickandpledge_echeck_RoutingNumber 	     = isset($_POST['clickandpledge_echeck_RoutingNumber']) ? $_POST['clickandpledge_echeck_RoutingNumber'] : '';

                
                                   }
            else
            {
             $clickandpledge_echeck_BankNumber 	     = isset($_POST['clickandpledge_echeck_BankNumber']) ? $_POST['clickandpledge_echeck_BankNumber'] : '';
			  $clickandpledge_echeck_TransitNumber 	     = isset($_POST['clickandpledge_echeck_TransitNumber']) ? $_POST['clickandpledge_echeck_TransitNumber'] : '';

            }
			 $clickandpledge_echeck_CheckNumber 	     = isset($_POST['clickandpledge_echeck_CheckNumber']) ? $_POST['clickandpledge_echeck_CheckNumber'] : '';
			  $clickandpledge_echeck_AccountNumber 	     = isset($_POST['clickandpledge_echeck_AccountNumber']) ? $_POST['clickandpledge_echeck_AccountNumber'] : '';
			  $clickandpledge_echeck_retypeAccountNumber = isset($_POST['clickandpledge_echeck_retypeAccountNumber']) ? $_POST['clickandpledge_echeck_retypeAccountNumber'] : '';
				if(empty($clickandpledge_echeck_AccountType)) {
					array_push($customerrors, 'Please select Account Type');
				}
				
				$clickandpledge_echeck_NameOnAccount_regexp = "/^([a-zA-Z0-9 ]){0,100}$/";
				if(empty($clickandpledge_echeck_NameOnAccount)) {
					array_push($customerrors, 'Please enter Name On Account');
				}				
				else if(!preg_match($clickandpledge_echeck_NameOnAccount_regexp, $clickandpledge_echeck_NameOnAccount)) {
					array_push($customerrors, 'Invalid Name On Account.');
				}
				
				if(empty($clickandpledge_echeck_IdType)) {
					array_push($customerrors, 'Please select Type of ID');
				}
				if(empty($clickandpledge_echeck_CheckType)) {
					array_push($customerrors, 'Please select Check Type');
				}
				
				$clickandpledge_echeck_CheckNumber_regexp = "/^([a-zA-Z0-9]){1,10}$/";
				if(empty($clickandpledge_echeck_CheckNumber)) {
					array_push($customerrors, 'Please enter Check Number');
				}				
				else if(!preg_match($clickandpledge_echeck_CheckNumber_regexp, $clickandpledge_echeck_CheckNumber)) {
					array_push($customerrors, 'Invalid Check Number');
				}	
				 if($cnpcur != 124){

				$clickandpledge_echeck_RoutingNumber_regexp = "/^([a-zA-Z0-9]){9}$/";
				if(empty($clickandpledge_echeck_RoutingNumber)) {
					array_push($customerrors, 'Please enter Routing Number');
				}				
				else if(!preg_match($clickandpledge_echeck_RoutingNumber_regexp, $clickandpledge_echeck_RoutingNumber)) {
					array_push($customerrors, 'Invalid Routing Number');
				}
                                }
            else
            {
            $cnpnumber_validation_regex = "/^\\d{3}$/"; 

            if(empty($clickandpledge_echeck_BankNumber)) {
					array_push($customerrors, 'Please enter Bank Number');
				}				
				else if(!preg_match($cnpnumber_validation_regex, $clickandpledge_echeck_BankNumber)) {
					array_push($customerrors, 'Invalid Bank Number');
				}
                        $cnpnumbertrans_validation_regex = "/^\\d{3,5}$/"; 

            if(empty($clickandpledge_echeck_TransitNumber)) {
					array_push($customerrors, 'Please enter Transit Number');
				}				
				else if(!preg_match($cnpnumbertrans_validation_regex, $clickandpledge_echeck_TransitNumber)) {
					array_push($customerrors, 'Invalid Transit Number');
				}
            }
				$clickandpledge_echeck_AccountNumber_regexp = "/^([a-zA-Z0-9]){1,17}$/";
				if(empty($clickandpledge_echeck_AccountNumber)) {
					array_push($customerrors, 'Please enter Account Number');
				}				
				else if(!preg_match($clickandpledge_echeck_AccountNumber_regexp, $clickandpledge_echeck_AccountNumber)) {
					array_push($customerrors, 'Invalid Account Number');
				}
				
				if(empty($clickandpledge_echeck_retypeAccountNumber)) {
					array_push($customerrors, 'Please enter Account Number Again');
				}
				else if($clickandpledge_echeck_AccountNumber != $clickandpledge_echeck_retypeAccountNumber) {
					array_push($customerrors, 'Please enter same Account Number Again');
				}								
			}
			else if($cnp_payment_method_selection != 'CreditCard' && $cnp_payment_method_selection != 'eCheck')  { 
			    if($_POST['clickandpledge_isRecurring'] == 'Recurring') {
				  array_push($customerrors, 'Sorry but recurring payments are not supported with this payment method');
				}
			}
			if(count($customerrors) > 0) {
				foreach($customerrors as $err) {
					wc_add_notice( __( $err, 'woocommerce' ), 'error' );
				}
				return false;
			} else {
				return true;
			}
			
		}
		
		/**
	     * Validate plugin settings
	     */
		function validate_settings() {
			$currency = trim(get_option('woocommerce_currency'));
	
			if (!in_array($currency, array('USD', 'EUR', 'CAD', 'GBP'))) {
				return false;
			}
	
			if (!$this->AccountID ) {
				return false;
			}
	
			return true;
		}
		
		/**
	     * Get user's IP address
	     */
		function get_user_ip() {			
			 $ipaddress = '';
			 if (isset($_SERVER['HTTP_CLIENT_IP']))
				 $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			 else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				 $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			 else if(isset($_SERVER['HTTP_X_FORWARDED']))
				 $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			 else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
				 $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			 else if(isset($_SERVER['HTTP_FORWARDED']))
				 $ipaddress = $_SERVER['HTTP_FORWARDED'];
			 else
				 $ipaddress = $_SERVER['REMOTE_ADDR'];
			$parts = explode(',', $ipaddress);
			if(count($parts) > 1) $ipaddress = $parts[0];
			 return $ipaddress; 
		}

	} // end woocommerce_clickandpledge
	
	/**
 	* Add the Gateway to WooCommerce
 	**/
	function add_clickandpledge_gateway($methods) {
		$methods[] = 'WC_Gateway_ClickandPledge';
		return $methods;
	}	
	add_filter('woocommerce_payment_gateways', 'add_clickandpledge_gateway');
} 
