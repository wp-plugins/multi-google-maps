<?php
/**
 * Plugin Name: Multi Google Maps
 * Plugin URI : http://wordpress.org
 * Description: This plugin supports to insert Multi Google Map V.3 Objects into your post.
 * Version    : 0.1
 * Author     : Siripol Noikajana
 * Author URI : http://wordpress.org
 * License    : GPL2
 */

$theGMPs = new GMP();

add_action('admin_menu', array($theGMPs, 'gmp_setting_menu_admin'));
add_action('save_post' , array($theGMPs, 'gmp_save_postdata'),     1);
add_filter('the_posts' , array($theGMPs, 'gmp_init'));
add_shortcode('GMP-Map', array($theGMPs, 'gmp_generate_map'));

class GMP
{
    private $noPosts;    
    private $curMap;
    private $mapData;
    private $googleMapApi = "http://maps.google.com/maps/api/js?sensor=false";
    private $width        = 500;
    private $height       = 500;

    public function __construct()
    {
        $this->curMap  = array();        
        $this->mapData = array();
    }

    public function gmp_generate_map($attr, $content)
    {
        global $post, $wpdb;
        $thePost = $post;

        $html = '';

        if($this->noPosts > 0)
        {            
            if(!isset($this->mapData[$thePost->ID]))
            {
                $this->mapData[$thePost->ID] = $this->extractData($thePost->ID);
                $this->curMap[$thePost->ID]  = 0;
            }

            if($this->curMap[$thePost->ID] < count($this->mapData[$thePost->ID]))
            {              
                $keys     = array_keys($this->mapData[$thePost->ID]);
                $key      = $keys[$this->curMap[$thePost->ID]];

                $marker  = $this->mapData[$thePost->ID][$key]['gmp_marker'];
                $desc    = $this->mapData[$thePost->ID][$key]['gmp_description'];
                $address = $this->mapData[$thePost->ID][$key]['gmp_address'];

                $width   = isset($this->mapData[$thePost->ID][$key]['gmp_width']) ?$this->mapData[$thePost->ID][$key]['gmp_width'] :$this->width;
                $height  = isset($this->mapData[$thePost->ID][$key]['gmp_height'])?$this->mapData[$thePost->ID][$key]['gmp_height']:$this->height;
                $width  .= 'px';
                $height .= 'px';
                $this->curMap[$thePost->ID]++;
                $mapId   = 'GMPmap_'.$thePost->ID.'_'.$this->curMap[$thePost->ID];                

                if($address !== false)
                {
                    $html = "
                    <div id='$mapId' 
                        style='position: relative; 
                               background-color: 
                               rgb(229, 227, 223); 
                               overflow: hidden;
                               width: $width;
                               height: $height; 
                               z-index: 0;'>
                    </div>
                    <script>drawMap('$mapId', '$marker', '$desc', '$address', 8);</script>";
                }
            }
        }

        return $html;
    }

    public function gmp_init($thePosts)
    {
        $this->noPosts = count($thePosts);
        wp_register_script('GmpAdminLib' , get_option('siteurl').'/wp-content/plugins/multi-google-maps/js/gmp_admin.js');
        wp_enqueue_script('GmpAdminLib');

        if($this->noPosts > 0)
        {
            wp_register_script('GoogleMapLib', $this->googleMapApi);

            wp_register_script('GmpLib'      , get_option('siteurl').'/wp-content/plugins/multi-google-maps/js/gmp.js',
                                array('GoogleMapLib'));
           
            wp_enqueue_script('GmpLib');

        }

        return $thePosts;
    }

    public function gmp_setting_menu_admin()
    {
        if( function_exists( 'add_menu_page' )) 
            add_menu_page(
                'GMP_Menu_1', 
                __('Google Map'), 
                'manage_options', 
                'GMP_Mainmenu_Handle', 
                array($this,'gmp_options'));

        if( function_exists( 'add_meta_box' )) {
            add_meta_box( 
                'GMP_post_section', 
                __( 'Google Map'), 
                array($this,'gmp_map_box'), 
                'post', 
                'advanced' );
            
            add_meta_box( 
                'GMP_post_section', 
                __( 'Google Map'), 
                array($this,'gmp_map_box'), 
                'page', 
                'advanced' );
        }
    }

    public function gmp_options() 
    {
        if (!current_user_can('manage_options'))  
        {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }
        
        $html = '<br/><br/><br/>
        <div>
            <div>
            </div>
            <div>
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHNwYJKoZIhvcNAQcEoIIHKDCCByQCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYCWqoNzwPU0IQmOGsE5bOIEjOYoZUkevfKvYRzXGvazGMyh+g+CMbWCsu1w4Z0fKNX96kWi/+EhVuQ8oIk7Gpl0eSA4XXM38qoqBGFzoI3chN5JtrjQjqH/uA0FGnHW2tEtQBF5uNYRCUjjWFiWzcbA1mgCI/XzHcBuzGA4QgzrOzELMAkGBSsOAwIaBQAwgbQGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIW59UrLUq5xWAgZCJCDUqZao9CpJtrnrQ0V8LKkriT7wb4A0aPt6Vy6ZzUukDwOqwLT1rIWD/DyM+iVbfmix7UjhivaNL86DymxMbu0JD00GK2M0JJ6+Q1YlfHVwoQAkAuLJoa9r72tO7kkG1PcLZYRlZh3ZRK+Kux9ltQZp6Bsv87MZD5Birfj6N4r9zXslNg92dS+lpS2/TeDegggOHMIIDgzCCAuygAwIBAgIBADANBgkqhkiG9w0BAQUFADCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wHhcNMDQwMjEzMTAxMzE1WhcNMzUwMjEzMTAxMzE1WjCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBAMFHTt38RMxLXJyO2SmS+Ndl72T7oKJ4u4uw+6awntALWh03PewmIJuzbALScsTS4sZoS1fKciBGoh11gIfHzylvkdNe/hJl66/RGqrj5rFb08sAABNTzDTiqqNpJeBsYs/c2aiGozptX2RlnBktH+SUNpAajW724Nv2Wvhif6sFAgMBAAGjge4wgeswHQYDVR0OBBYEFJaffLvGbxe9WT9S1wob7BDWZJRrMIG7BgNVHSMEgbMwgbCAFJaffLvGbxe9WT9S1wob7BDWZJRroYGUpIGRMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbYIBADAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAIFfOlaagFrl71+jq6OKidbWFSE+Q4FqROvdgIONth+8kSK//Y/4ihuE4Ymvzn5ceE3S/iBSQQMjyvb+s2TWbQYDwcp129OPIbD9epdr4tJOUNiSojw7BHwYRiPh58S1xGlFgHFXwrEBb3dgNbMUa+u4qectsMAXpVHnD9wIyfmHMYIBmjCCAZYCAQEwgZQwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tAgEAMAkGBSsOAwIaBQCgXTAYBgkqhkiG9w0BCQMxCwYJKoZIhvcNAQcBMBwGCSqGSIb3DQEJBTEPFw0xMDA2MjcwMDQzMDhaMCMGCSqGSIb3DQEJBDEWBBSAM/AdBvTAK9JOnLPpXJXvgGLBSjANBgkqhkiG9w0BAQEFAASBgBOcBcVIN+g4jED6/RjoiHcrSWOqi4vJjnJI8+cL8vN+qJDyvSBGMgvBtK2g5TQBVRh/zoB6fMmokUZVy/r+MZPUrCcCjvz+fE5ExSVf2X/JLBCZYWGWQRMTYj6KzTr032kjtAdWJ+2/i/3cpmx4Gys89WsJoRuZXlfBu/I0qrTD-----END PKCS7-----
                    ">
                    <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                </form>
            </div>
         </div>';

        echo $html;
    }

    private function extractData($post_ID)
    {
        global $wpdb;

        $data     = array();

        $metadata = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post_ID));

        foreach($metadata as $entry)
        {
            $meta_key   = esc_attr($entry->meta_key);
            $meta_value = htmlspecialchars($entry->meta_value); // using a <textarea />
            $meta_id    = (int) $entry->meta_id;

            if(strrpos($meta_key,'gmp_marker') !== false)
            {
                $index = substr($meta_key,  strlen('gmp_marker_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_marker'] = get_post_meta($post_ID, $meta_key, true) === false?'':get_post_meta($post_ID, $meta_key, true);                
            }
            elseif(strrpos($meta_key,'gmp_description') !== false)
            {
                $index = substr($meta_key,  strlen('gmp_description_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_description'] = get_post_meta($post_ID, $meta_key, true) === false?'':get_post_meta($post_ID, $meta_key, true);
            }
            elseif(strrpos($meta_key,'gmp_address') !== false)
            {
                $index = substr($meta_key,  strlen('gmp_address_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_address'] = get_post_meta($post_ID, $meta_key, true);
            }         
            elseif(strrpos($meta_key,'gmp_width') !== false)
            {
                $index = substr($meta_key,  strlen('gmp_width_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_width'] = get_post_meta($post_ID, $meta_key, true);
            }
            elseif(strrpos($meta_key,'gmp_height') !== false)
            {
                $index = substr($meta_key,  strlen('gmp_height_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_height'] = get_post_meta($post_ID, $meta_key, true);
            }
        }

        return $data;
    }

    public function gmp_map_box() 
    {
        if ( isset($_GET['post']) )
            $post_id = (int) $_GET['post'];
        elseif ( isset($_POST['post_ID']) )
            $post_id = (int) $_POST['post_ID'];
        else
            $post_id = 0;
        
        $post_ID  = $post_id;
        $data     = $this->extractData($post_ID);

        $html .= '';
        $count = 0;
        
        foreach($data as $item)
        {
            $count++;

            $marker  = $item['gmp_marker'];
            $desc    = $item['gmp_description'];
            $address = $item['gmp_address'];

            //New Feature on 0.3
            $width  = isset($item['gmp_width'])?$item['gmp_width']:$this->width;
            $height = isset($item['gmp_width'])?$item['gmp_width']:$this->height;

            $html .= "
            <div name='gmpObj' id='gmpObj_$count'>
                <table>
                    <tr style='vertical-align:top'>
                        <td style='width:100px'>
                            Name
                        </td>
                        <td style='width:30px'>:</td>
                        <td>
                            <input type='text' id='gmp_marker_$count' name='gmp_marker_$count' 
                                value='$marker' style='width:400px'> 
                        </td>
                    </tr>
                    <tr style='vertical-align:top'>
                        <td>
                            Description
                        </td>
                        <td>:</td>
                        <td>
                            <textarea id='gmp_description_$count' name='gmp_description_$count' 
                                style='width:400px'>$desc</textarea>
                        </td>
                    </tr>
                    <tr style='vertical-align:top'>
                        <td>
                            Address
                        </td>
                        <td>:</td>
                        <td>
                            <textarea id='gmp_address_$count' name='gmp_address_$count' 
                                style='width:400px;height:100px'>$address</textarea>
                        </td>
                    </tr>";
            
            //New Feature on 0.3
            $html .= "
                    <tr style='vertical-align:top'>
                        <td>
                            Width
                        </td>
                        <td>:</td>
                        <td>
                            <input type='text' id='gmp_width_$count' name='gmp_width_$count' 
                                value='$width' style='width:400px'> 
                        </td>
                    </tr>
                    <tr style='vertical-align:top'>
                        <td>
                            Height
                        </td>
                        <td>:</td>
                        <td>
                            <input type='text' id='gmp_height_$count' name='gmp_height_$count' 
                                value='$height' style='width:400px'> 
                        </td>
                    </tr>";

            $html .= "
                </table>
                <div style='text-align:right'>
                    <input type='button' onclick='send_to_editor(\"[GMP-Map]\");' 
                            value='Add this Map into Post' />
                    <input type='button' onclick='removeMap(\"gmpObj_$count\");' value='Delete this Map'/>
                </div>
            </div>";
        }

        if(count($data) == 0)
        {
            $count++;

            $marker = '';
            $desc    = '';
            $address = '';

            $html .= "
            <div name='gmpObj' id='gmpObj_$count'>
                <table>
                    <tr style='vertical-align:top'>
                        <td style='width:100px'>
                            Name
                        </td>
                        <td style='width:30px'>:</td>
                        <td>
                            <input type='text' id='gmp_marker_$count' name='gmp_marker_$count' 
                                value='$marker' style='width:400px'> 
                        </td>
                    </tr>
                    <tr style='vertical-align:top'>
                        <td>
                            Description
                        </td>
                        <td>:</td>
                        <td>
                            <textarea id='gmp_description_$count' name='gmp_description_$count' 
                                style='width:400px'>$desc</textarea>
                        </td>
                    </tr>
                    <tr style='vertical-align:top'>
                        <td>
                            Address
                        </td>
                        <td>:</td>
                        <td>
                            <textarea id='gmp_address_$count' name='gmp_address_$count' 
                                style='width:400px;height:100px'>$address</textarea>
                        </td>
                    </tr>
                    <tr style='vertical-align:top'>
                        <td style='width:100px'>
                            Width
                        </td>
                        <td style='width:30px'>:</td>
                        <td>
                            <input type='text' id='gmp_width_$count' name='gmp_width_$count' 
                                value='$width' style='width:400px'> 
                        </td>
                    </tr>
                    <tr style='vertical-align:top'>
                        <td style='width:100px'>
                            Height
                        </td>
                        <td style='width:30px'>:</td>
                        <td>
                            <input type='text' id='gmp_height_$count' name='gmp_height_$count' 
                                value='$height' style='width:400px'> 
                        </td>
                    </tr>
                </table>
                <div style='text-align:right'>
                    <input type='button' onclick='send_to_editor(\"[GMP-Map]\");' value='Add this Map into Post' />
                    <input type='button' onclick='removeMap(\"gmpObj_$count\");' value='Delete this Map' />                
                </div>
            </div>";
        }
        
        $html .= '
            <p class="submit">
                <input type="button" onclick="addNewMap()" 
                    value="Add New Map"/>
            </p>
            <p style="padding: 0.5em; height: 10px; background-color: #DFDFDF;"></p>
            <input type="hidden" name="gmp_noncename" id="gmp_noncename" value="'. 
                wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

        echo $html;
    }

    public function gmp_save_postdata($post_id) 
    {
        if ( !wp_verify_nonce( $_POST['gmp_noncename'], plugin_basename(__FILE__) )) {
            return $post_id;
        }
        
        // Check permissions
        if ( 'page' == $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $post_id ) )
                return $post_id;
        }
        else 
        {
            if ( !current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        $data     = array();

        foreach($_POST as $key => $value)
        {            
            if(strrpos($key,'gmp_marker') !== false)
            {
                $index = substr($key,  strlen('gmp_marker_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_marker'] = $value;
            }
            elseif(strrpos($key,'gmp_description') !== false)
            {
                $index = substr($key,  strlen('gmp_description_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_description'] = $value;
            }
            elseif(strrpos($key,'gmp_address') !== false)
            {
                $index = substr($key,  strlen('gmp_address_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_address'] = $value;
            }
            //New Feature on 0.3
            elseif(strrpos($key,'gmp_width') !== false)
            {
                $index = substr($key,  strlen('gmp_width_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_width'] = $value;
            }
            elseif(strrpos($key,'gmp_height') !== false)
            {
                $index = substr($key,  strlen('gmp_height_'));

                if(!isset($data[$index]))
                    $data[$index] = array();

                $data[$index]['gmp_height'] = $value;
            }
        }

        $metadata = has_meta($post_id);        

        foreach($metadata as $entry)
        {
            $meta_key = esc_attr($entry->meta_key);

            if(strrpos($meta_key,'gmp_marker') !== false)
            {
                delete_post_meta($post_id, $meta_key);
            }
            elseif(strrpos($meta_key,'gmp_description') !== false)
            {
                delete_post_meta($post_id, $meta_key);
            }
            elseif(strrpos($meta_key,'gmp_address') !== false)
            {
                delete_post_meta($post_id, $meta_key);
            }
            //New Feature on 0.3
            elseif(strrpos($meta_key,'gmp_width') !== false)
            {
                delete_post_meta($post_id, $meta_key);
            }
            elseif(strrpos($meta_key,'gmp_height') !== false)
            {
                delete_post_meta($post_id, $meta_key);
            }
        }

        $count = 0;

        foreach($data as $item)
        {
            $count++;

            $marker  = $item['gmp_marker'];
            $desc    = $item['gmp_description'];
            $address = $item['gmp_address'];

            //New Feature on 0.3
            $width   = isset($item['gmp_width']) ?$item['gmp_width']:$this->width;
            $height  = isset($item['gmp_height'])?$item['gmp_height']:$this->height;
            
            add_post_meta($post_id, "gmp_marker_$count"     , $marker, true);
            add_post_meta($post_id, "gmp_description_$count", $desc, true);
            add_post_meta($post_id, "gmp_address_$count"    , $address, true);

            //New Feature on 0.3
            add_post_meta($post_id, "gmp_height_$count"     , $height, true);
            add_post_meta($post_id, "gmp_width_$count"      , $width, true);
        }
    }
}
?>