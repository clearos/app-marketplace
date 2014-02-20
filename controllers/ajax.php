<?php

/**
 * AJAX controller for Marketplace.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\marketplace\Marketplace as Marketplace;
use \clearos\apps\marketplace\Cart_Item as Cart_Item;
use \clearos\apps\base\Yum as Yum;
use \clearos\apps\base\Engine_Exception as Engine_Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * JSON controller.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Ajax extends ClearOS_Controller
{
    /**
     * Ajax default controller
     *
     * @return string
     */

    function index()
    {
        echo "These aren't the droids you're looking for...";
    }

    /**
     * Ajax get app details controller
     *
     * @param String  $basename app basename
     * @param boolean $realtime force realtime
     *
     * @return JSON
     */

    function get_app_details($basename = NULL, $realtime = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        try {

            include_once clearos_app_base('marketplace') . '/libraries/Cart_Item.php';

            if ($this->input->post('realtime'))
                $realtime = TRUE;

            if ($this->input->post('id'))
                $basename = $this->input->post('id');

            // Load dependencies
            $this->load->library('marketplace/Marketplace');

            $result = $this->marketplace->get_app_details($basename, $realtime);

            $response = json_decode($result);
            $details = $response->details;

            // Add flag to display recommended apps in RHS bar
            $details->hide_recommended_apps = $this->marketplace->get_hide_recommended_apps();

            // Add flag to hide support policy
            $details->hide_support_policy = $this->marketplace->get_hide_support_policy();

            // Save some installation and version info...in packaging world, RPM names use hyphen separator, not underscore
            $this->load->library('base/Software', Marketplace::APP_PREFIX . preg_replace("/_/", "-", $details->basename));
            $details->installed = $this->software->is_installed();

            if ($this->software->is_installed()) {
                $metadata = $this->_get_metadata($details->basename);
                $details->installed_version = $metadata['version'] . '-' . $metadata['release'];
                $details->up2date = $this->_is_up2date($details->installed_version, $details->latest_version);
                if (!isset($metadata['delete_dependency']))
                    $details->no_uninstall = TRUE;
                else
                    $details->no_uninstall = FALSE;
            }

            // Re-encode to JSON and return
            echo json_encode($details);
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax get apps controller
     *
     * @return JSON
     */

    function get_apps()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        include_once clearos_app_base('marketplace') . '/libraries/Cart_Item.php';

        try {
            $this->load->library('marketplace/Marketplace');
            $this->load->library('marketplace/Cart');

            $cart_items = $this->cart->get_items();
            $realtime = FALSE;
            if ($this->input->post('realtime'))
                $realtime = TRUE;

            // Get installed apps
            $installed_apps = $this->marketplace->get_installed_apps(); 

            $max = $this->input->post('max');
            $offset = $this->input->post('offset');
            $search_reset = $this->input->post('search_reset');

            // On searches or filtering, we grab all apps, so override max, offset will be ignored 
            $filter = $this->marketplace->get_search_criteria();
            if (is_array($filter))
                $response = json_decode($this->marketplace->get_apps($realtime, 0, 0)); 
            else
                $response = json_decode($this->marketplace->get_apps($realtime, $max, $offset)); 

            if (!is_object($response)) {
                throw new Engine_Exception(lang('marketplace_expecting_json_reply'), CLEAROS_WARNING);
            } else if ($response->code !== 0) {
                if ($response->code == 3)
                    $this->session->set_userdata(array('sdn_org' => $response->sdn_org));
                throw new Engine_Exception($response->errmsg, $response->code);
            }

            $app_counter = 0;
            $total_apps = 0;
            $search_counter = 0;
            $applist = $response->list;
            foreach ($applist as $app) {

                $cart_item = new \clearos\apps\marketplace\Cart_Item(Marketplace::APP_PREFIX . preg_replace("/_/", "-", $app->basename)); 
                $cart_item->set(get_object_vars($app->pricing));
                // Whether an item has an EULA or not is not in the pricing object
                $cart_item->set_eula($app->eula);
                $cart_item->serialize($this->session->userdata('sdn_rest_id'));

                // Save some installation and version info - replace underscore with hyphen for RPM name
                $rpm = Marketplace::APP_PREFIX . preg_replace("/_/", "-", $app->basename);
                if (array_key_exists($rpm, $installed_apps)) {
                    $app->installed = TRUE;
                    $app->up2date = $this->_is_up2date($installed_apps[$rpm]['version'] . '-' . $installed_apps[$rpm]['release'], $app->latest_version);
                }

                if (is_array($filter)) {
                    if (!$this->_search($filter['search'], $app)) {
                        unset($applist[$app_counter]);
                        $app_counter++;
                        continue;
                    } else if (!$this->_filter($filter['category'], $app->category_en_US)) {
                        unset($applist[$app_counter]);
                        $app_counter++;
                        continue;
                    } else if (!$this->_filter($filter['price'], $app->pricing->unit_price, $filter['price'])) {
                        unset($applist[$app_counter]);
                        $app_counter++;
                        continue;
                    } else if (!$this->_filter($filter['intro'], $app->introduced, 'date')) {
                        unset($applist[$app_counter]);
                        $app_counter++;
                        continue;
                    } else if (!$this->_filter($filter['status'], array('installed' => $app->installed, 'up2date' => $app->up2date), 'status')) {
                        unset($applist[$app_counter]);
                        $app_counter++;
                        continue;
                    } else if ($search_counter < $offset || ($search_counter >= ($max + $offset) && $max != 0)) {
                        unset($applist[$app_counter]);
                        $total_apps++;
                        $app_counter++;
                        $search_counter++;
                        continue;
                    } else {
                        $search_counter++;
                    }
                }
                if (array_key_exists(Marketplace::APP_PREFIX . preg_replace("/_/", "-", $app->basename), $cart_items))
                    $app->incart = TRUE;

                $app_counter++;
                $total_apps++;
            }
            $applist = array_values($applist);

            // Re-encode to JSON and return

            // If we searched on a term, use total_apps...otherwise, use total_apps coming back from SDN
            if (is_array($filter))
                echo json_encode(array('list' => $applist, 'total' => $total_apps));
            else
                echo json_encode(array('list' => $applist, 'total' => $response->total));
            
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax bulk update cart controller
     *
     * @return JSON
     */

    function bulk_cart_update()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        $apps = array();
        try {
            $this->load->library('marketplace/Marketplace');
            $this->load->library('marketplace/Cart');
            include_once clearos_app_base('marketplace') . '/libraries/Cart_Item.php';
            $apps = json_decode($this->input->post('apps')); 
            // Reverse the array so dependent apps (eg. Kaspersky 50 user) gets removed first
            if ($this->input->post('toggle') == 'none') {
                $apps = array_reverse($apps);
            } else {
                $state = array();
                foreach ($apps as $index => $app)
                    $state[$index] = $app->state;
                array_multisort($state, SORT_ASC, $apps);
            }
            foreach ($apps as $index => $app) {
                if ($app->state == 1) {
                    $cart_obj = new Cart_Item(Marketplace::APP_PREFIX . preg_replace("/_/", "-", $app->id));
                    $cart_obj->unserialize($this->session->userdata('sdn_rest_id'));
                    $this->cart->add_item($cart_obj);
                } else {
                    $this->cart->remove_item(Marketplace::APP_PREFIX . preg_replace("/_/", "-", $app->id));
                }
                unset($apps[$index]);
            }
            echo json_encode(Array('code' => 0));
        } catch (Exception $e) {
            echo json_encode(
                Array(
                    'code' => clearos_exception_code($e) + 100,
                    'errmsg' => clearos_exception_message($e),
                    'apps' => array_values($apps)
                )
            );
        }
    }

    /**
     * Ajax update cart controller
     *
     * @return JSON
     */

    function update_cart()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        try {
            $this->load->library('marketplace/Marketplace');
            $this->load->library('marketplace/Cart');
            $this->load->library('marketplace/Cart_Item', Marketplace::APP_PREFIX . preg_replace("/_/", "-", $this->input->post('id')));
            if ($this->input->post('add')) {
                $this->cart_item->unserialize($this->session->userdata('sdn_rest_id'));
                $this->cart->add_item($this->cart_item);
            } else {
                $this->cart->remove_item(Marketplace::APP_PREFIX . preg_replace("/_/", "-", $this->input->post('id')));
            }
            echo json_encode(Array('code' => 0));
        } catch (Exception $e) {
            // Make sure code != 0
            echo json_encode(
                Array(
                    'code' => clearos_exception_code($e) + 100,
                    'errmsg' => clearos_exception_message($e)
                )
            );
        }
    }

    /**
     * Ajax get image controller
     *
     * @return JSON
     */

    function get_image()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        try {
            $this->load->library('marketplace/Marketplace');
            echo $this->marketplace->get_image($_REQUEST['type'], $_REQUEST['id']);
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax get non-auth mods (install/delete) controller
     *
     * @return JSON
     */

    function allow_noauth_mods()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        try {
            $this->load->library('marketplace/Marketplace');
            echo $this->marketplace->get_noauth_mods();
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax get account information controller
     *
     * @return JSON
     */

    function get_account_info()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        try {
            $this->load->library('marketplace/Marketplace');
            echo $this->marketplace->get_account_info($this->input->post('password') ? $this->input->post('password') : NULL);
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax check for authentication controller
     *
     * @return JSON
     */

    function is_authenticated()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        try {
            $this->load->library('marketplace/Marketplace');
            echo $this->marketplace->is_authenticated(
                ($this->input->post('username')) ? $this->input->post('username') : NULL,
                ($this->input->post('password')) ? $this->input->post('password') : NULL,
                ($this->input->post('email')) ? $this->input->post('email') : NULL
            );
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax get SDN information controller
     *
     * @return JSON
     */

    function get_sdn_info()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        try {
            $this->load->library('marketplace/Marketplace');
            echo $this->marketplace->get_sdn_info();
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax add review controller
     *
     * @return JSON
     */

    function add_review()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load dependencies
        clearos_load_language('marketplace');

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        try {
            $this->load->library('marketplace/Marketplace');
            $this->load->library('base/Software', Marketplace::APP_PREFIX . preg_replace("/_/", "-", $this->input->post('basename')));
            if (!$this->software->is_installed())
                throw new Engine_Exception(lang('marketplace_no_install_no_review'), CLEAROS_WARNING);

            $this->load->library('marketplace/Marketplace');
            echo $this->marketplace->add_review(
                $this->input->post('basename'), $this->input->post('rating'), $this->input->post('comment'), $this->input->post('pseudonym'), $this->input->post('update')
            );
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax peer review controller
     *
     * @return JSON
     */

    function peer_review()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load dependencies
        clearos_load_language('marketplace');

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        try {
            $this->load->library('marketplace/Marketplace');
            $this->load->library('base/Software', Marketplace::APP_PREFIX . preg_replace("/_/", "-", $this->input->post('basename')));
            if (!$this->software->is_installed())
                throw new Engine_Exception(lang('marketplace_no_modding_review'), CLEAROS_WARNING);

            $this->load->library('marketplace/Marketplace');
            echo $this->marketplace->peer_review($this->input->post('dbid'), $this->input->post('approve'));
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax get EULA controller
     *
     * @return JSON
     */

    function get_eula()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        try {
            $realtime = FALSE;
            if ($this->input->post('realtime'))
                $realtime = TRUE;

            $this->load->library('marketplace/Marketplace');
            echo $this->marketplace->get_eula($this->input->post('id'), $realtime);
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax checkout controller
     *
     * @return JSON
     */

    function checkout()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        try {
            $this->load->library('marketplace/Marketplace');
            $this->load->library('marketplace/Cart');
            $list = $this->cart->get_items();

            if (empty($list)) {
                echo json_encode(Array('code' => 1, 'errmsg' => lang('marketplace_no_apps_selected')));
                return;
            }
                
            // Just send non-upgrades to Marketplace webservice engine
            $pids_to_process = $this->cart->get_list('pid', TRUE);

            if (!empty($pids_to_process)) {
                // Complete the purchase (if req'd).
                $response = json_decode(
                    $this->marketplace->app_store_purchase(
                        $this->input->post('payment'), $pids_to_process, ($this->input->post('po')) ? $this->input->post('po') : NULL
                    )
                ); 

                // Check if everything went OK
                if (!is_object($response))
                    throw new Engine_Exception(lang('marketplace_expecting_json_reply'), CLEAROS_WARNING);
                if ($response->code !== 0)
                    throw new Engine_Exception($response->errmsg, CLEAROS_WARNING);

                // Before we send to YUM, better do a registration sync check of the local flag
                $this->load->library('registration/Registration');
                $this->registration->set_local_registration_status(TRUE);
            }

            // Initiate Yum install
            $this->load->library('base/Yum');

            // Check for cart items that do not have an RPM
            $install_list = array();
            foreach ($list as $item) {
                if (($item->get_pid_bitmask() & Marketplace::PID_MASK_RPM_AVAIL) == Marketplace::PID_MASK_RPM_AVAIL)
                    $install_list[] = preg_replace("/_/", "-", $item->get_id());
            }
            if (empty($install_list)) {
                // If empty, clear cart items, and return message
                $this->cart->clear();
                echo json_encode(Array('code' => 0, 'no_rpms_to_install' => TRUE));
                return;
            }

            $this->yum->install($install_list);

            // If yum starts to install OK, delete cart contents
            $this->cart->clear();

            // Clear cache to force fetching new status
            $this->marketplace->delete_cache();

            // Clear app cache install list
            $this->marketplace->delete_cached_app_install_list();

            // Return all OK - FYI - Invoice # (if applicable) is available here as $response->invoice
            echo json_encode(Array('code' => 0));
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax progress controller
     *
     * @return JSON
     */

    function progress()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load dependencies
        clearos_load_language('marketplace');

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        try {
            $this->load->library('base/Yum');
            $logs = $this->yum->get_logs();
            $logs = array_reverse($logs);
            foreach ($logs as $log) {
                $last = json_decode($log);
                // Make sure we're getting valid JSON
                if (!is_object($last))
                    continue;
                echo json_encode(
                    Array(
                        'code' => $last->code, 'details' => $last->details,
                        'progress' => $last->progress, 'overall' => $last->overall,
                        'errmsg' => $last->errmsg, 'busy' => $this->yum->is_busy(),
                        'wc_busy' => $this->yum->is_wc_busy() 
                    )
                );
                return;
            }
            echo json_encode(
                Array(
                    'code' => -999,
                    'wc_busy' => $this->yum->is_wc_busy(),
                    'busy' => $this->yum->is_busy(),
                    'errmsg' => lang('marketplace_no_data')
                )
            );
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }


    /**
     * Marketplace search controller
     *
     * @return view
     */

    function set_search()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        // Load dependencies
        //------------------

        clearos_load_language('marketplace');
        $this->lang->load('marketplace');
        $this->load->library('marketplace/Marketplace');

        // Load view
        //----------
        $query = array(
            'search' => '',
            'category' => 'all',
            'price' => 'all',
            'intro' => 'all',
            'status' => 'all'
        );

        if ($this->input->post('search'))
            $query['search'] = $this->input->post('search');
        if ($this->input->post('category'))
            $query['category'] = $this->input->post('category');
        if ($this->input->post('price'))
            $query['price'] = $this->input->post('price');
        if ($this->input->post('intro'))
            $query['intro'] = $this->input->post('intro');
        if ($this->input->post('status'))
            $query['status'] = $this->input->post('status');

        try {
            $this->marketplace->set_search_criteria (
                $query['search'],
                $query['category'],
                $query['price'],
                $query['intro'],
                $query['status']
            );
            echo json_encode(array('code' => 0));
        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }

    /**
     * Ajax search function
     *
     * @param String $search the search string
     * @param String $app    the app object
     *
     * @access private
     * @return boolean
     */

    private function _search($search, $app)
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            if (!isset($search) || $search === '')
                return TRUE;
            $needle = "/" . str_replace(" ", "|", $search) . "/i";
            // I was just serializing the object and searching the string, but it turned up
            // false positives (i.e. 'anti' in 'quantity' field)
            if (preg_match($needle, $app->name))
                return TRUE;
            if (preg_match($needle, $app->description))
                return TRUE;
            if (preg_match($needle, $app->description_en_US))
                return TRUE;
            if (preg_match($needle, $app->tags))
                return TRUE;
            if (preg_match($needle, $app->tags_en_US))
                return TRUE;
            if (preg_match($needle, $app->category))
                return TRUE;
            if (preg_match($needle, $app->category_en_US))
                return TRUE;
            if (preg_match($needle, $app->sub_category))
                return TRUE;
            if (preg_match($needle, $app->sub_category_en_US))
                return TRUE;
            if (preg_match($needle, $app->vendor))
                return TRUE;
            return FALSE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Ajax filter function
     *
     * @param String $compare1 value 1
     * @param mixed  $compare2 value 2
     * @param String $type     type of search
     *
     * @access private
     * @return boolean
     */

    private function _filter($compare1, $compare2, $type = 'string')
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            if (!isset($compare1) || $compare1 === '' || $compare1 == 'all')
                return TRUE;

            if ($type == 'free' && $compare2 == 0) {
                return TRUE;
            } else if ($type == 'paid' && $compare2 > 0) {
                return TRUE;
            } else if ($type == 'date') {
                // First compare is coming in as 'days ago'.
                // Second is epoch timestamp in millis
                $convert = (time() - $compare1 * 24 * 60 * 60) * 1000;
                if ($compare2 > $convert)
                    return TRUE;
            } else if ($type == 'status') {
                if ($compare1 == 'installed' && $compare2['installed'] && $compare2['up2date'])
                    return TRUE;
                else if ($compare1 == 'upgrade_available' && $compare2['installed'] && !$compare2['up2date'])
                    return TRUE;
                else if ($compare1 == 'new' && !$compare2['installed'])
                    return TRUE;
            } else {
                if (strtolower($compare1) == strtolower($compare2))
                    return TRUE;
            }
            
            return FALSE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Compare versions and determine if up2date
     *
     * @param String $current current version
     * @param String $compare compare version
     *
     * @access private
     * @return array
     */

    private function _is_up2date($current, $compare)
    {
        clearos_profile(__METHOD__, __LINE__);

        // May not need to go through any regex if strings are identical
        if ($current == $compare)
            return TRUE;

        if (preg_match("/(\d+)\.(\d+)\.(\d+)-(.*)$/", $current, $match_current)) {
            if (preg_match("/(\d+)\.(\d+)\.(\d+)-(\d+)$/", $compare, $match_compare)) {
                // Start with most significant version
                if ((int)$match_current[1] > (int)$match_compare[1]) {
                    return TRUE;
                } else if ((int)$match_current[1] < (int)$match_compare[1]) {
                    return FALSE;
                } else {
                    if ((int)$match_current[2] > (int)$match_compare[2]) {
                        return TRUE;
                    } else if ((int)$match_current[2] < (int)$match_compare[2]) {
                        return FALSE;
                    } else {
                        if ((int)$match_current[3] > (int)$match_compare[3]) {
                            return TRUE;
                        } else if ((int)$match_current[3] < (int)$match_compare[3]) {
                            return FALSE;
                        } else {
                            if ($match_current[4] >= $match_compare[4])
                                return TRUE;
                            else
                                return FALSE;
                        }
                    }
                }
            }
        }
        // Hmmm...weren't able to match...just return true so nothing is displayed
        return TRUE;
    }
 
    /**
     * Get app metadata function
     *
     * @param String $basename basename
     *
     * @access private
     * @return array
     */

    function _get_metadata($basename)
    {
        clearos_profile(__METHOD__, __LINE__);

        $app = array();
        $app_base = clearos_app_base($basename);

        $info_file = $app_base . '/deploy/info.php';

        if (file_exists($info_file)) {

            // Load metadata file
            include $info_file;

            // Add timestamp
            $stat = stat($info_file);
            $metadata['modified'] = $stat['ctime'];
        }
        return $app;
    }
}
