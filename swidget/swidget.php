<?php
/******************************************************************************
Plugin Name: Swidget
Plugin URI: http://sales.carnegiemuseums.org
Description: Siriusware Widget
Version: 0.1
Author: Carnegie Museums of Pittsburgh
Author URI: http://www.carnegiemuseums.org
License: GPLv2 or later
******************************************************************************/

//Initialize
if( ! function_exists('swidget_shortcodes_init') ){
  function swidget_scripts_init()
  {
    wp_enqueue_script('swidget-script',"https://sales.carnegiemuseums.org/widget/ecommerce-widget.js",array( 'jquery' ));
  }
  function swidget_shortcodes_init()
  {
    init_checkout();
    init_cart();
  }
  add_action('init', 'swidget_shortcodes_init');
  add_action('wp_enqueue_scripts', 'swidget_scripts_init');
}

//The checkout widget
function init_checkout()
{
  function swidget_checkout($atts = [], $content = null, $tag='')
  {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $co_atts = shortcode_atts([
      "site" => null,
      "item" => null
    ], $atts, $tag);
    //Start Output
    $site = intval($co_atts["site"]);
    $item = intval($co_atts["item"]);
    $class = "swidget_$site_$item";

    $out = <<<EOT
  <script>
    jQuery( document ).ready(function(){
      jQuery(".$class").swQuickCheckout($site, $item);
    });
  </script>
  <div class="swidget-holder $class"></div>
EOT;

    return $out;
  }



  add_shortcode('swcheckout', 'swidget_checkout');
}

//Cart based functions
function init_cart()
{


  function getCart($site)
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
    
    $name = "swidget_cart_$site";
    if(isset($_SESSION[$name]))
    {
      return $_SESSION[$name];
    }
    else
    {
      $url = "https://sales.carnegiemuseums.org/api/v1/cart/create?site=$site";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $result = curl_exec($ch);
      $json = json_decode($result);

      if($json['success'])
      {
        $cart = intval($json['cart']);
        $_SESSION[$name] = $cart;
        return $cart;
      }
    }
    return null;
  }

  function swidget_cart($atts = [], $content = null, $tag='')
  {
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $co_atts = shortcode_atts([
      "site" => null
    ], $atts, $tag);

    $site = intval($co_atts["site"]);

    $cart = getCart($site);

    if(!isset($cart)) return "";

    $class = "swidget_cart_$cart";

    $out = <<<EOT
  <script>
    jQuery( document ).ready(function(){
      jQuery(".$class").swCart($cart);
    });
  </script>
  <div class="swidget-cart-holder $class"></div>
EOT;

  return $out;
  }

  function swidget_addtocart($atts = [], $content = null, $tag='')
  {
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $co_atts = shortcode_atts([
      "site" => null,
      "item" => null
    ], $atts, $tag);
    //Start Output
    $site = intval($co_atts["site"]);
    $item = intval($co_atts["item"]);
    $cart = getCart($site);

    if(!isset($cart)) return "";

    $class = "swidget_$site_$item";

    $out = <<<EOT
  <script>
    jQuery( document ).ready(function(){
      jQuery(".$class").swAddToCart($cart, $site, $item);
    });
  </script>
  <div class="swidget-holder $class"></div>
EOT;

    return $out;
  }

  add_shortcode('swcart', 'swidget_cart');
  add_shortcode('swaddtocart', 'swidget_addtocart');

}