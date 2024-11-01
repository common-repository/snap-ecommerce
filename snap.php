<?php
/*
  Plugin Name: Snap Ecommerce
  Plugin URI: http://snapecommerce.com/
  Description: Easily add a full ecommerce store to your Wordpress site
  Version: 1.2
  Author: Dextel.net
  Author URI: http://dextel.net
  License: Copyright 2014 Dextel.net
 */

register_activation_hook(__FILE__, 'snap_activation');
register_activation_hook(__FILE__, 'snap_deactivation');
add_action('admin_init', 'snap_redirect');

function snap_activation() {
    add_option('snap_do_activation_redirect', true);
    //create fields in DB for storing snap data

    add_option('snap_store_id', '', '', false);
    add_option('snap_page_id', '', '', false);
    add_option('snap_installation_finished', '', '', false);
}

function snap_deactivation() {
    delete_option('snap_store_id');
    delete_option('snap_page_id');
    delete_option('snap_installation_finished');
}

function snap_redirect() {
    if (get_option('snap_do_activation_redirect', false)) {
        delete_option('snap_do_activation_redirect');
        wp_redirect('admin.php?page=snap_settings&setup=1');
    }
}

add_action('admin_menu', 'snap_create_settings_page');


function snap_create_settings_page() {
    $snap_menu_settings_page = add_menu_page('Snap Settings', 'Snap Settings', 'manage_options', 'snap_settings', 'snap_render_settings_page');
    add_action('load-' . $snap_menu_settings_page, 'snap_enqueue_scripts');
}

function snap_enqueue_scripts() {
    add_action('admin_enqueue_scripts', 'snap_register_scripts');
}

function snap_register_scripts() {
    wp_register_style('snap_admin_css', plugins_url() . '/snap-ecommerce/css/en.css', false, '1.0.0');
    wp_enqueue_style('snap_admin_css');
    wp_register_style('snap_admin_font', 'http://fonts.googleapis.com/css?family=Open+Sans:400,300italic,600,400italic,600italic', false, '1.0.0');
    wp_enqueue_style('snap_admin_font');
}

function snap_render_settings_page() {
    $html = ' <!-- CONTENT PLUGIN -->
    <div class="content_plugin">        

        <!-- HEADING -->
        <div id="heading_plugin">
            
            <!-- LOGO -->		
            <div class="logo_plugin"><a href="#">&nbsp;</a></div>
            <!-- END LOGO -->  

            <!-- TITLE -->       
            <div class="title">Welcome to Snap Ecommerce</div>
            <!-- END TITLE -->           
                         
            <!-- TEXT -->       
            <div class="tx_desc">Snap Ecommerce is the easiest way to quickly add a full online store to your Wordpress website. And without any coding or technical skills needed!</div>
            <!-- END TEXT -->    
                    
        </div>
        <!-- END HEADING--> ';
    if (get_option('snap_store_id', false)) {
        if (get_option('snap_page_id', false)) {
            if (get_option('snap_installation_finished', false)) {
                $html .= '<div id="snap_content">
                    <div class="title_lets">You\'re all set!</div>
                    <div id="steps">
                        <div class="title" style="margin-left:0">Start selling with Snap Ecommerce</div>
                        <div class="tx_desc" style="margin-left:0;clear:both;">
                         Manage your products, customers, and store settings by logging in.<br /><a href="http://admin.snapecommerce.com" class="bt_white">LOG IN</a><br />
                         <br />
                         Keep our <a href="http://kb.snapecommerce.com" target="_blank">FAQ</a> page handy to get the best out of Snap Ecommerce.<br />
                         Happy selling!
                        </div>
                    </div>
                </div>
                ';
            } else {
                $menus = array();
                $menus_html = '';

                $menu_locations = get_nav_menu_locations();
                
                $locations_empty = false;
                if (empty($menu_locations)) {
                    $locations_empty = true;
                }
                foreach ($menu_locations as $location => $menu_id) {
                    if (empty($location) || empty($menu_id)) {
                        $locations_empty = true;
                    }
                }
                
                if (!$locations_empty) {
                    foreach ($menu_locations as $location => $menu_id) {
                        $menu_object = wp_get_nav_menu_object($menu_id);
                        $menus_html .= '<input type="checkbox" name="snap_selected_menus[]" value="' . $menu_id . '" /> ' . $menu_object->name . '<br />';
                    }

                    $html .= '<div id="snap_content">
                        <div class="title_lets">Make your store visible</div>
                        <div id="steps">
                            <div class="title" style="margin-left:0">Add your shop page to your menus</div>
                            <div class="tx_desc" style="margin-left:0;clear:both;">
                            To make your new online store page visible, add it to your menus. They will be easily accessible that way.
                            <br /><br />
                            <span style="font-weight:bold">Select the menus</span><br />
                            <form action="/wp-admin/admin.php?page=snap_settings" id="frmAddToMenus" method="POST">' . 
                                $menus_html .
                                '<a class="bt_white" href="javascript:void(0);" onclick="jQuery.fn.addToMenus();">ADD TO MENUS</a>
                            </form>
                            </div>
                        </div>
                    </div>
                    <script type="text/javascript">
                    jQuery.fn.addToMenus = function () {
                        var data = jQuery("#frmAddToMenus").serialize() + "&action=snap_add_to_menus";
                            jQuery.post("/wp-admin/admin-ajax.php", data, function(response) {
                                if (response === "success") {
                                    jQuery("#frmAddToMenus").submit();
                                } else {
                                    alert("Oops! Something went wrong, please try again or contact us for help.");
                                }
                            });
                    };
                    </script>
                    ';
                } else {
                    $html .= '<div id="snap_content">
                        <div class="title_lets">Make your store visible</div>
                        <div id="steps">
                            <div class="title" style="margin-left:0">Add your shop page to your menus</div>
                            <div class="tx_desc" style="margin-left:0;clear:both;">
                            Set up your menus and assign them a location within your theme. You\'ll then be able to add your Snap Ecommerce page to your menus here.
                            </div>
                        </div>
                    </div>';
                    
                }
            }
            
        } else {
            $html .= '
            <div id="snap_content">
                <div class="title_lets">Congratulations, you\'re almost done!</div>
                <div id="steps">
                    <div class="title" style="margin-left:0">Create your Shop page</div>
                    <div class="tx_desc" style="margin-left:0;clear:both;">
                    Let\'s create a new page on your website where Snap Ecommerce will display your new online store, with its products, shopping cart, checkout, and other cool features.
                    <br /><br />
                    <span style="font-weight:bold">Choose a name for your store page</span><br />
                    <form action="/wp-admin/admin.php?page=snap_settings" id="frmCreatePage" method="POST">
                        <input name="snap_page_name" id="snap_page_name" type="text" class="input_plugin" value="Shop" onclick="this.value=\'\'"> &nbsp;
                        <a class="bt_white" href="javascript:void(0);" onclick="jQuery.fn.createPage();">CREATE</a>
                    </form>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
            jQuery.fn.createPage = function () {
                var data = "snap_page_name=" + jQuery("#snap_page_name").val() + "&action=snap_create_page";
                jQuery.post("/wp-admin/admin-ajax.php", data, function(response) {
                    if (response === "success") {
                        jQuery("#frmCreatePage").submit();
                    } else {
                        if (response === "error_page_exists") {
                            alert("Oops! That page already exists, please try another name.");
                        }
                        jQuery("#snap_page_name").css("border", "solid 1px red");
                    }
                });
            };
            </script>
            ';
        }
    } else {
        $html .= '
        <div id="snap_content">

         <!-- TITLE -->       
            <div class="title_lets">Let\'s get started</div>
          <!-- END TITLE --> 

          <!-- STEP -->
        <div id="steps" class="number1">
            <img border="0" class="graphic" src="' . plugins_url() . '/snap-ecommerce/images/monitor.jpg">
            <div class="title">Try Snap Ecommerce for free!</div>  
            <div class="tx_desc">Add a complete, full-featured store to your Wordpress website! It’s quick and easy, with no technical skills required. <b>Try our 14-day free trial</b>, no credit card required, and you will see just how simple it is to start selling through your website.
            <br/><br/>
            <span class="tx_black"><strong>Already have an account?</strong> Skip to Step 2 </span><br />
            <a class="bt_white" target="_blank" href="http://snapecommerce.com/signup">START NOW</a>
        </div>
        </div>
        <!-- END STEP-->  

                  <!-- STEP -->
        <div id="steps" class="number2">
            <img border="0" class="graphic" src="' . plugins_url() . '/snap-ecommerce/images/bg_cart.gif">
            <div class="title">Enter your store ID</div>  
            <div class="tx_desc">Enter your Store ID* here: <br/>
            <form action="/wp-admin/admin.php?page=snap_settings" id="frmConnect" method="POST">
            <input name="store_id" id="store_id" type="text" class="input_plugin" value="Store ID" onclick="this.value=\'\'"> &nbsp;
            <a class="bt_white" href="javascript:void(0);" onclick="jQuery.fn.connectStore();">CONNECT</a>
            </form>

            <br/><br/>

            * Your Store ID can be found by logging into your Snap Ecommerce control panel. The Store ID is located in your control panel, right above the control panel nagivation bar.

            <br/><br/><br/>
  
<div class="title2">Questions?</div> &nbsp;&nbsp; Visit <a class="blueln" href="http://snapecommerce.com/" target="_blank">www.SnapEcommerce.com</a> 

            </div>
        </div>
        <!-- END STEP-->
     </div>
            
   
    
    <script type="text/javascript">
    jQuery.fn.connectStore = function() {
        var data = "store_id=" + jQuery("#store_id").val() + "&action=snap_connect_store";
        jQuery.post("/wp-admin/admin-ajax.php", data, function(response) {
            if (response === "success") {
                jQuery("#frmConnect").submit();
            } else {
             jQuery("#store_id").css("border", "solid 1px red");
            }
        });
    };
    </script>
';
    }

    $html .= ' </div>
    <!-- END CONTENT PLUGIN -->';
    echo $html;
}

register_uninstall_hook(__FILE__, 'snap_uninstall');

function snap_uninstall() {
    //delete fields from DB
    delete_option('snap_store_id');
}

add_action("wp_ajax_snap_connect_store", "snap_connect_store");
add_action("wp_ajax_nopriv_snap_connect_store", "snap_connect_store");

function snap_connect_store() {
    if (!empty($_REQUEST['store_id']) && is_numeric($_REQUEST['store_id'])) {
        $store_id = $_REQUEST['store_id'];
        update_option('snap_store_id', $store_id);
        echo 'success';
        exit(0);
    } else {
        die('error_invalid_store_id');
    }
}

add_action("wp_ajax_snap_create_page", "snap_create_page");
add_action("wp_ajax_nopriv_snap_create_page", "snap_create_page");

function snap_create_page() {
    if (!empty($_REQUEST['snap_page_name'])) {
        $snap_page_name = htmlentities(addslashes($_REQUEST['snap_page_name']), ENT_QUOTES);

        $the_page = get_page_by_title($snap_page_name);
        if (!$the_page) {
            // Create post object
            $_p = array();
            $_p['post_title'] = $snap_page_name;
            $_p['post_content'] = "<div id=\"dextelsnap\"></div><script type=\"text/javascript\" src=\"http://fb.snapecommerce.com/External/Init.js?id=" . get_option('snap_store_id', false) . "\"></script>";
            $_p['post_status'] = 'publish';
            $_p['post_type'] = 'page';
            $_p['comment_status'] = 'closed';
            $_p['ping_status'] = 'closed';
            $_p['post_category'] = array(1); // the default 'Uncatrgorised'

            // Insert the post into the database
            $the_page_id = wp_insert_post( $_p );
        } else {
            die('error_page_exists');
        }

        update_option('snap_page_id', $the_page_id);
        echo 'success';
        exit(0);
    } else {
        die('error_invalid_page_name');
    }
}

add_action("wp_ajax_snap_add_to_menus", "snap_add_to_menus");
add_action("wp_ajax_nopriv_snap_add_to_menus", "snap_add_to_menus");

function snap_add_to_menus() {
    if (!empty($_REQUEST['snap_selected_menus'])) {
        foreach ($_REQUEST['snap_selected_menus'] as $menu_id) {
            if (!is_numeric($menu_id)) {die('error');}
            $page = get_post(get_option('snap_page_id', false));
            wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => $page->post_title,
                'menu-item-object' => 'page',
                'menu-item-object-id' => $page->ID,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish')
            );
        }
        update_option('snap_installation_finished', true);
        echo 'success';
        exit(0);
    } else {
        die('error_invalid_store_id');
    }
}

?>