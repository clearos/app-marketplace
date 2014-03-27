<?php

/**
 * Marketplace class.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage libraries
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\marketplace;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('marketplace');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\base\Yum as Yum;
use \clearos\apps\accounts\Accounts_Configuration as Accounts_Configuration;
use \clearos\apps\mode\Mode_Engine as Mode_Engine;
use \clearos\apps\mode\Mode_Factory as Mode_Factory;
use \clearos\apps\clearcenter\Rest as Rest;
use \clearos\apps\clearcenter\Subscription_Engine as Subscription_Engine;
use \clearos\apps\Marketplace\Cart as Cart;
use \clearos\apps\Marketplace\Cart_Item as Cart_Item;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('base/Yum');
clearos_load_library('accounts/Accounts_Configuration');
clearos_load_library('mode/Mode_Engine');
clearos_load_library('mode/Mode_Factory');
clearos_load_library('clearcenter/Rest');
clearos_load_library('clearcenter/Subscription_Engine');
clearos_load_library('marketplace/Cart');
clearos_load_library('marketplace/Cart_Item');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;
use \clearos\apps\base\Yum_Busy_Exception as Yum_Busy_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Marketplace for ClearCenter class.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage libraries
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Marketplace extends Rest
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/etc/clearos/marketplace.conf';
    const FILE_SEARCH_HISTORY = 'search_history';
    const FILE_INSTALLED_APPS = 'installed_apps';
    const FILE_CUSTOM_REPOS = '/var/clearos/marketplace/customs_repos';
    const FILE_QSF = 'qsf.txt';
    const COMMAND_RPM = '/bin/rpm';
    const FOLDER_MARKETPLACE = '/var/clearos/marketplace';
    const MAX_RECORDS = 10;
    const APP_PREFIX = 'app-';
    const PID_MASK_DEVICE_ASSIGN = 1;
    const PID_MASK_FUTURE_2 = 2;
    const PID_MASK_FUTURE_4 = 4;
    const PID_MASK_FUTURE_8 = 8;
    const PID_MASK_FUTURE_16 = 16;
    const PID_MASK_COMMUNITY = 32;
    const PID_MASK_ENTERPRISE = 64;
    const PID_MASK_MARKETPLACE_APP = 128;
    const PID_MASK_RPM_AVAIL = 256;
    const PID_MASK_MULTI_BUY = 512;
    const REGISTER_NEW = 0;
    const REGISTER_EXISTING = 1;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $applist = array();
    protected $filter_category = array('all', 'cloud', 'server', 'network', 'gateway', 'system', 'reports');
    protected $filter_price = array('all', 'free', 'paid');
    protected $filter_intro = array('all', '7', '30', '180', '365');
    protected $filter_status = array('all', 'installed', 'upgrade_available', 'new');
    protected $filter_default = array(
        'category' => 'all',
        'price' => 'all',
        'intro' => 'all',
        'status' => 'all'
    );

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Marketplace constructor.
     */

    function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
        parent::__construct();
    }

    /**
     * Set the number of apps to display per page.
     *
     * @param int $number number of apps
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_number_of_apps_to_display($number)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_number_of_apps_to_display($number));

        $this->_set_parameter('number_of_apps_to_display', $number);
    }

    /**
     * Set the search/filter criteria.
     *
     * @param string $search   search
     * @param string $category category
     * @param string $price    price
     * @param string $intro    intro
     * @param string $status   status
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_search_criteria($search, $category, $price, $intro, $status, $active = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_search($search));
        Validation_Exception::is_valid($this->validate_filter_category($category));
        Validation_Exception::is_valid($this->validate_filter_price($price));
        Validation_Exception::is_valid($this->validate_filter_intro($intro));
        Validation_Exception::is_valid($this->validate_filter_status($status));

        try {
            $extras = array(
                'search' => $search,
                'category' => $category,
                'price' => $price,
                'intro' => $intro,
                'status' => $status,
                'active' => $active,
                'time' => time()
            );

            $file = new File(
                self::FOLDER_MARKETPLACE . '/' . self::FILE_SEARCH_HISTORY . '.' .
                $this->CI->session->userdata['username']
            );
            if (!$file->exists())
                $file->create('webconfig', 'webconfig', '0644');
            $contents = $file->get_contents_as_array();

            $index = 0;
            foreach ($contents as $line) {
                $info = (array)json_decode($line);
                // Remove duplicate search strings or non-search (filter only)
                if ($info['search'] == $search || $info['search'] == '') {
                    unset($contents[$index]);
                    $index++;
                    continue;
                }
                $info['active'] = FALSE;
                $contents[$index] = json_encode($info);
                $index++;
            }

            // Keep last 20 entries
            if (count($contents) >= 20)
                array_pop($contents);

            array_unshift($contents, json_encode($extras));

            $file->dump_contents_from_array($contents);

            try {
                // Don't send request for searches on tags starting with a number (eg. 10_google_apps)
                if ($search != '' && !preg_match('/^\d\d.*$/', $search))
                    $this->request('marketplace', 'search', $extras);
            } catch (Exception $ignore) {
                // No need to bail
            }

        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Clear the search/filter history.
     *
     * @return void
     * @throws Engine_Exception
     */

    function clear_search_history()
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            $file = new File(self::FOLDER_MARKETPLACE . '/' . self::FILE_SEARCH_HISTORY . '.' . $this->CI->session->userdata['username']);
            if (!$file->exists())
                $file->create('webconfig', 'webconfig', '0644');

            $file->dump_contents_from_array(json_encode($this->filter_default));
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Reset the search/filter criteria to default.
     *
     * @return void
     * @throws Engine_Exception
     */

    function reset_search_criteria()
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            $this->set_search_criteria(
                '',
                $this->filter_default['category'],
                $this->filter_default['price'],
                $this->filter_default['intro'],
                $this->filter_default['status'],
                FALSE
            );
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get the search/filter criteria.
     *
     * @return mixed
     * @throws Engine_Exception
     */

    function get_search_criteria()
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            $file = new File(self::FOLDER_MARKETPLACE . '/' . self::FILE_SEARCH_HISTORY . '.' . $this->CI->session->userdata['username']);

            // If we haven't yet initialized, set default
            if (!$file->exists())
                $this->reset_search_criteria();

            $contents = $file->get_contents_as_array();

            foreach ($contents as $search) {
                $keys = (array)json_decode($search);
                if ($keys['active'])
                    return $keys;
            }

        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get the search/filter history.
     *
     * @return array
     * @throws Engine_Exception
     */

    function get_search_history()
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            $file = new File(self::FOLDER_MARKETPLACE . '/' . self::FILE_SEARCH_HISTORY . '.' . $this->CI->session->userdata['username']);
            $history = array();

            // If we haven't yet initialized, set default
            if (!$file->exists())
                $this->reset_search_criteria();

            $contents = $file->get_contents_as_array();

            foreach ($contents as $search) {
                $info = (array)json_decode($search);
                array_push($history, $info);
            }
            return $history;

        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Set the display format.
     *
     * @param string $display_format display format
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_display_format($display_format)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_display_format($display_format));

        $this->_set_parameter('display_format', $display_format);
    }

    /**
     * Set the pseudonym used to identify reviews.
     *
     * @param string $pseudonym pseudonym
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_pseudonym($pseudonym)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_pseudonym($pseudonym));

        $this->_set_parameter('pseudonym', $pseudonym);
    }

    /**
     * Set the hide support policy setting.
     *
     * @param boolean $hide hide
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_hide_support_policy($hide)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_hide_support_policy($hide));

        if ($hide === 'on' || $hide == 1 || $hide == TRUE)
            $this->_set_parameter('hide_support_policy', 0);
        else
            $this->_set_parameter('hide_support_policy', 1);
    }

    /**
     * Set the hide recommended apps setting.
     *
     * @param boolean $hide hide
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_hide_recommended_apps($hide)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_hide_recommended_apps($hide));

        if ($hide === 'on' || $hide == 1 || $hide == TRUE)
            $this->_set_parameter('hide_recommended_apps', 0);
        else
            $this->_set_parameter('hide_recommended_apps', 1);
    }

    /**
     * Get no-auth mods status.
     *
     * @return Object JSON-encoded response
     *
     * @throws Webservice_Exception
     */

    public function get_noauth_mods()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {

            $result = $this->request('marketplace', 'get_noauth_mods');

            return $result;
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get Account information.
     *
     * @param String $password account password
     *
     * @return Object JSON-encoded response
     *
     * @throws Webservice_Exception
     */

    public function get_account_info($password = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {

            $extras = array();
            if ($password != NULL)
                $extras['password'] = $password;

            $result = $this->request('marketplace', 'get_account_info', $extras);

            return $result;
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get the number of apps to display.
     *
     * @return int
     */

    function get_number_of_apps_to_display()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['number_of_apps_to_display'];
    }

    /**
     * Get the default display format.
     *
     * @return String
     */

    function get_display_format()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        $display_format = $this->config['display_format'];
        if ($display_format == NULL || !$display_format) {
            if ($this->CI->session->userdata('sdn_username'))
                $display_format = $this->CI->session->userdata('sdn_username');
            else
                $display_format = lang('marketplace_anonymous');
        }
        return $display_format;
    }

    /**
     * Get the default review pseudonym.
     *
     * @return String
     */

    function get_pseudonym()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        $pseudonym = $this->config['pseudonym'];
        if ($pseudonym == NULL || !$pseudonym) {
            if ($this->CI->session->userdata('sdn_username'))
                $pseudonym = $this->CI->session->userdata('sdn_username');
            else
                $pseudonym = lang('marketplace_anonymous');
        }
        return $pseudonym;
    }

    /**
     * Get the hide support policy setting
     *
     * @return Boolean
     */

    function get_hide_support_policy()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        $hide = isset($this->config['hide_support_policy']) ? $this->config['hide_support_policy'] : NULL;

        if ($hide == NULL || !$hide)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * Get the hide recommended app setting
     *
     * @return Boolean
     */

    function get_hide_recommended_apps()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        $hide = isset($this->config['hide_recommended_apps']) ? $this->config['hide_recommended_apps'] : NULL;

        if ($hide == NULL || !$hide)
            return FALSE;
        else
            return TRUE;
    }

    /**
     * Get app summary list.
     *
     * @param boolean  &$realtime set realtime to TRUE to fetch real-time data
     * @param interger $max       maximum records
     * @param interger $offset    offset
     *
     * @return Object  JSON-encoded response
     *
     * @throws Webservice_Exception
     */

    public function get_apps(&$realtime = FALSE, $max = self::MAX_RECORDS, $offset = 0)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $cachekey = __CLASS__ . '-' . __FUNCTION__ . '-' . $max . '-' . $offset; 

            if (!$realtime && $this->_check_cache($cachekey))
                return $this->cache;
    
            // Tell caller that we had no cache, had to use realtime
            $realtime = TRUE;

            $extras = array('max' => $max, 'offset' => $offset);

            $extras = array_merge($extras, $this->_get_common_extras());

            $result = $this->request('marketplace', 'get_apps', $extras);

            $this->_save_to_cache($cachekey, $result);
        
            return $result;
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get app details.
     *
     * @param string  $basename app basename
     * @param boolean $realtime set realtime to TRUE to fetch real-time data
     *
     * @return Object  JSON-encoded response
     *
     * @throws Webservice_Exception
     */

    public function get_app_details($basename, $realtime = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $cachekey = __CLASS__ . '-' . __FUNCTION__ . '-' . $basename; 

            if (!$realtime && $this->_check_cache($cachekey))
                return $this->cache;
    
            $extras = array('basename' => $basename);

            $extras = array_merge($extras, $this->_get_common_extras());

            $result = $this->request('marketplace', 'get_app_details', $extras);

            $this->_save_to_cache($cachekey, $result);

            $response = json_decode($result);
        
            // TODO: map these response codes to the following
            // CLEAROS_INFO, CLEAROS_WARNING or CLEAROS_ERROR
            if ($response->code != 0)
                throw new Engine_Exception($response->errmsg, $response->code);

            $details = $response->details;

            $cart_item = new Cart_Item(Marketplace::APP_PREFIX . preg_replace("/_/", "-", $basename)); 
            $cart_item->set(get_object_vars($details->pricing));
            // Whether an item has an EULA or not is not in the pricing object
            $cart_item->set_eula($details->eula);
            $cart_item->serialize($this->CI->session->userdata['sdn_rest_id']);

            return $result;
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Returns an array of information related around the purchase.
     *
     * @param string $payment the purchase method
     * @param array  $pid     an array of product IDs in cart
     * @param string $po      a purchase order (if applicable)
     *
     * @return Object  JSON-encoded response
     *
     * @throws Webservice_Exception
     */

    public function app_store_purchase($payment, $pid, $po = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $extras = array();

            $extras['payment'] = $payment;
            $extras['pid'] = implode("|", $pid);
            if ($po != NULL)
                $extras['po'] = $po;

            $result = $this->request('marketplace', 'app_store_purchase', $extras);

            // If a purchase was made, force subscription update
            $subscriptions = new Subscription_Engine();
            $subscriptions->get_subscription_updates(TRUE);
            return $result;
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Submits an APP review.
     *
     * @param string $basename  the APP basename
     * @param int    $rating    rating
     * @param string $comment   comment
     * @param string $pseudonym pseudonym 
     * @param int    $update    confirm update of existing review
     *
     * @return Object JSON-encoded response
     *
     * @throws Webservice_Exception
     */

    public function add_review($basename, $rating, $comment, $pseudonym, $update)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {

            $extras = array("basename" => $basename, "rating" => $rating, "comment" => $comment, "pseudonym" => $pseudonym);
            if ($update)
                $extras["update"] = TRUE;

            $result = $this->request('marketplace', 'add_review', $extras);

            // Update pseudonym
            try {
                if ($this->get_pseudonym() != $pseudonym)
                    $this->set_pseudonym($pseudonym);
            } catch (Validation_Exception $val_err) {
                // ignore errors
            }

            // Delete the cached file for this app
            // FIXME...this will work, but its ugly.
            $cachekey = __CLASS__ . '-get_app_details-' . $basename; 
            $filename = md5($cachekey) . "." . $this->CI->session->userdata['sdn_rest_id']; 
            $this->delete_cache($filename);

            return $result;
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Submits a +/- on an APP review.
     *
     * @param String  $id      the APP rating ID
     * @param boolean $approve approval of the review
     *
     * @return Object  JSON-encoded response
     *
     * @throws Webservice_Exception
     */

    public function peer_review($id, $approve)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $extras = array("id" => $id, "approve" => ($approve ? "TRUE" : "FALSE"));

            $result = $this->request('marketplace', 'peer_review', $extras);

            return $result;
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get an APP EULA.
     *
     * @param int     $id       EULA ID on SDN
     * @param boolean $realtime set realtime to TRUE to fetch real-time data
     *
     * @return Object  JSON-encoded response
     *
     * @throws Webservice_Exception
     */

    public function get_eula($id, $realtime = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {

            $cachekey = __CLASS__ . '-' . __FUNCTION__ . '-' . $id; 

            if (!$realtime && $this->_check_cache($cachekey))
                return $this->cache;

            $extras = array("id" => $id);

            $result = $this->request('marketplace', 'get_eula', $extras);

            return $result;
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Set custom repos (from QSF file).
     *
     * @param String $repo repo name
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_custom_repos($repo)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CUSTOM_REPOS . '.' . $this->CI->session->userdata['sdn_rest_id']);
            if (!$file->exists())
                $file->create('webconfig', 'webconfig', '640');

            $lines = $file->get_contents_as_array();
            if (!in_array($repo, $lines))
                $file->add_lines(trim($repo) . "\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Delete custom repos (from QSF file).
     *
     * @param $string $repo repo name
     *
     * @return void
     * @throws Engine_Exception
     */

    public function delete_custom_repos($repo = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CUSTOM_REPOS . '.' . $this->CI->session->userdata['sdn_rest_id']);
            if (!$file->exists()) {
                return;
            } else if ($file->exists() && $repo == NULL) {
                $file->delete();
                return;
            }

            $file->delete_lines("/^$repo$/");

        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get custom repos (from QSF file).
     *
     * @return array list of custom repos 
     *
     * @throws Engine_Exception
     */

    public function get_custom_repos()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CUSTOM_REPOS . '.' . $this->CI->session->userdata['sdn_rest_id']);
            if (!$file->exists())
                return array();

            return $file->get_contents_as_array();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Fetches a screenshot or logo.
     *
     * @param String $type the type of image (logo, screenshot etc.)
     * @param int    $id   the image ID
     *
     * @return Object  JSON-encoded response
     *
     * @throws Webservice_Exception
     */

    public function get_image($type, $id)
    {
        clearos_profile(__METHOD__, __LINE__, $type . ' - ' . $id);

        try {

            $cache_time = 2592000; // 30 days
            $filename = CLEAROS_CACHE_DIR . "/" . $type . "-" . $id . ".png";
            $lastmod = @filemtime($filename);
            if ($lastmod && (time() - $lastmod < $cache_time)) {
                // Use cached file.
                return json_encode(array("code" => 0, "location" => "/cache/" . $type . "-" . $id . ".png"));
            }
            
            $extras = array("type" => $type, "id" => $id);

            $result = $this->request('marketplace', 'get_image', $extras);

            file_put_contents($filename, base64_decode(json_decode($result)->image));
        
            return json_encode(array("code" => 0, "location" => "/cache/" . $type . "-" . $id . ".png"));
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get a list of installed Marketplace apps.
     *
     * @param boolean $realtime realtime force flag
     *
     * @return array a list of installed apps
     *
     * @throws Engine_Exception
     */

    public function get_installed_apps($realtime = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            // 5 minute cache time
            $cache_time = 300;
            $filename = CLEAROS_CACHE_DIR . "/" . self::FILE_INSTALLED_APPS;
            $lastmod = @filemtime($filename);
            if ($lastmod && (time() - $lastmod < $cache_time)) {
                // Use cached file.
                return unserialize(file_get_contents($filename));
            }

            $list = array();
            $shell = new Shell();
            $exitcode = $shell->execute(
                self::COMMAND_RPM,
                "-qa --queryformat \"%{NAME}|%{VERSION}|%{RELEASE}|%{SUMMARY}|%{SIZE}\\n\"| grep ^" . self::APP_PREFIX,
                FALSE
            );
            if ($exitcode != 0)
                throw new Engine_Exception(lang('marketplace_unable_to_get_installed_app_list'), CLEAROS_WARNING);
            $rows = $shell->get_output();
            foreach ($rows as $row) {
                $parts = explode("|", $row); 
                $list[$parts[0]] = array(
                    'version' => $parts[1],
                    'release' => $parts[2],
                    'summary' => $parts[3],
                    'size' => $parts[4]
                );
            }
            file_put_contents($filename, serialize($list));
            return $list;
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }

    }

    /**
     * Delete app 
     *
     * @param String $basename basename
     *
     * @return array
     */

    function delete_app($basename)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $dependencies = $this->get_app_deletion_dependancies($basename);
            $apps = implode(' ', array_keys($dependencies));
            $options = array('validate_exit_code' => FALSE);
            $shell = new Shell();
            $exitcode = $shell->execute(self::COMMAND_RPM, "-e $apps", TRUE, $options);
            if ($exitcode != 0) {
                $err = $shell->get_last_output_line();
                throw new Engine_Exception(lang('marketplace_unable_to_delete_app') . ': ' . $err . '.', CLEAROS_WARNING);
            }
            $this->delete_cached_app_install_list();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }

    /**
     * Delete cached app install list
     *
     * @return void
     */

    function delete_cached_app_install_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(CLEAROS_CACHE_DIR . "/" . self::FILE_INSTALLED_APPS, TRUE);
            if ($file->exists())
                $file->delete();
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }

    /**
     * Get app dependancies for delete
     *
     * @param String $basename basename
     *
     * @return array
     */

    function get_app_deletion_dependancies($basename)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            // Always include app...do not include core...it may have dependencies to other apps
            $list = array(Marketplace::APP_PREFIX . preg_replace("/_/", "-", $basename) => array());
            $app_base = clearos_app_base($basename);

            $info_file = $app_base . '/deploy/info.php';

            if (file_exists($info_file)) {

                // Load metadata file
                include $info_file;

                if (isset($app['delete_dependency']))
                    $list = array_merge($list, array_flip($app['delete_dependency']));
                else
                    throw new Engine_Exception(lang('marketplace_core_app_cannot_delete'), CLEAROS_WARNING);
            }
            $installed_apps = $this->get_installed_apps();
            $result = array_intersect_key($installed_apps, $list);
            return $result;

        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }

    /**
     * Put the quick setup file in the cache directory
     *
     * @param string $filename string QSF filename
     *
     * @return void
     * @throws Engine_Exception, File_Not_Found_Exception
     */

    function set_qsf($filename)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(CLEAROS_TEMP_DIR . '/' . $filename, TRUE);
            if (!$file->exists())
                throw new File_Not_Found_Exception(clearos_exception_message($e));

            $installed_apps = $this->get_installed_apps();

            // Move uploaded file to cache
            $file->move_to(self::FOLDER_MARKETPLACE . '/' . self::FILE_QSF);
            $file->chown('root', 'root'); 
            $file->chmod(600);
            $lines = $file->get_contents_as_array();
            $cart = new Cart();
            $no_paid_apps = FALSE;
            if (in_array('flag_no_paid'))
                $no_paid_apps = TRUE;
            foreach ($lines as $line_number => $line) {
                if ($line_number == 0 && !preg_match('/# Quick Select File - Version \d+\.\d$/', $line)) {
                    throw new Validation_Exception(lang('marketplace_invalid_quick_select_file'));
                } else if (preg_match('/^\s*#.*/', $line)) {
                    continue;
                } else if (array_key_exists(preg_replace("/_/", "-", $line), $installed_apps)) {
                    // Already installed..nothing to do.
                    continue;
                } else if (preg_match('/^' . self::APP_PREFIX . '.*/', $line)) {
                    $cart_obj = new Cart_Item($line);
                    $cart_obj->unserialize($this->CI->session->userdata['sdn_rest_id']);
                    // No paid apps flag on paid (non exempt) app
                    if ($no_paid_apps && $cart_obj->get_unit_price() > 0 && !$cart_obj->get_exempt())
                        continue;
                    $cart->add_item($cart_obj);
                } else if (preg_match('/^enable_repo=\s*(.*)\s*/', $line, $match)) {
                    $this->set_custom_repos($match[1]);
                } else {
                    $cart_obj = new Cart_Item($line);
                    $cart->add_item($cart_obj);
                }
            }
        } catch (File_Not_Found_Exception $e) {
            throw new File_Not_Found_Exception(clearos_exception_message($e));
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e));
        }
    }

    /**
     * Is QSF file uploaded.
     *
     * @return boolean TRUE/FALSE
     * @throws Engine_Exception, File_Not_Found_Exception
     */

    function is_csv_file_uploaded()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FOLDER_MARKETPLACE . '/' . self::FILE_QSF, TRUE);
            if (!$file->exists())
                return FALSE;
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Deletes the QSF file.
     *
     * @return void
     * @throws Engine_Exception, File_Not_Found_Exception
     */

    function delete_qsf()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FOLDER_MARKETPLACE . '/' . self::FILE_QSF, TRUE);
            if (!$file->exists())
                return;
            $lines = $file->get_contents_as_array();
            $cart = new Cart();
            foreach ($lines as $line) {
                if (preg_match('/^qsf_flag.*/', $line)) {
                    continue;
                } else if (preg_match('/^enable_repo=\s*(.*)\s*/', $line, $match)) {
                    $this->delete_custom_repos($match[1]);
                    continue;
                }

                $cart->remove_item($line);
            }
            $file->delete();
        } catch (File_Not_Found_Exception $e) {
            throw new File_Not_Found_Exception(clearos_exception_message($e));
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e));
        }
    }

    /**
     * Get the size of the QSF file.
     *
     * @return integer size 
     * @throws Engine_Exception, File_Not_Found_Exception
     */

    function get_qsf_size()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FOLDER_MARKETPLACE . '/' . self::FILE_QSF, TRUE);
            if (!$file->exists())
                throw new File_Not_Found_Exception(lang('marketplace_qsf_file_not_found'));
            return $file->get_size();
        } catch (File_Not_Found_Exception $e) {
            throw new File_Not_Found_Exception(clearos_exception_message($e));
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e));
        }
    }

    /**
     * Get information from the QSF file.
     *
     * @return array data
     * @throws Engine_Exception, File_Not_Found_Exception
     */

    function get_qsf_info()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FOLDER_MARKETPLACE . '/' . self::FILE_QSF, TRUE);
            if (!$file->exists())
                throw new File_Not_Found_Exception(lang('marketplace_qsf_file_not_found'));
            $lines = $file->get_contents_as_array();
            $data = array(
                'apps' => 0,
                'packages' => 0,
                'flag_no_paid' => FALSE,
                'repos' => array() 
            );
            foreach ($lines as $line) {
                if (preg_match('/^\s*#.*/', $line))
                    continue;
                else if (preg_match('/^' . self::APP_PREFIX . '.*/', $line))
                    $data['apps']++;
                else if ($line == 'qsf_flag_no_paid')
                    $data['flag_no_paid'] = TRUE;
                else if (preg_match('/^enable_repo=\s*(.*)\s*/', $line, $match))
                    $data['repos'][] = $match[1];
                else 
                    $data['packages']++;
            }
            return $data;
        } catch (File_Not_Found_Exception $e) {
            throw new File_Not_Found_Exception(clearos_exception_message($e));
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e));
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Loads configuration files.
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        $configfile = new Configuration_File(self::FILE_CONFIG);

        try {
            $this->config = $configfile->load();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = TRUE;
    }

    /**
     * Generic set routine.
     *
     * @param string $key   key name
     * @param string $value value for the key
     *
     * @return  void
     * @throws Engine_Exception
     */

    function _set_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG, TRUE);

            if (!$file->exists())
                $file->create('webconfig', 'webconfig', '0644');

            $match = $file->replace_lines("/^$key\s*=\s*/", "$key=$value\n");

            if (!$match)
                $file->add_lines("$key=$value\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = FALSE;
    }

    /**
     * Get repo list.
     *
     * @param string $type repo list
     *
     * @return array
     * @throws Engine_Exception
     */

    function _get_repository_list($type = Yum::REPO_ACTIVE)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $yum = new Yum();
            $counter = 0;
            if ($yum->is_busy()) {
                sleep(3);
                $counter++;
                if ($counter > 3)
                    throw new Yum_Busy_Exception();
            }
            $repos = $yum->get_repo_list();
            $list = array();
            foreach ($repos as $repo) {
                if ($repo['enabled'] == 1 && ($type & Yum::REPO_ACTIVE) == Yum::REPO_ACTIVE)
                    $list[] = $repo['id'];
                else if ($repo['enabled'] == 0 && ($type & Yum::REPO_DISABLED) == Yum::REPO_DISABLED)
                    $list[] = $repo['id'];
            }
            return $list;
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get common extras to add to web service calls.
     *
     * @return array
     * @throws Engine_Exception
     */

    function _get_common_extras()
    {
        clearos_profile(__METHOD__, __LINE__);

        $extras = array();
        try {
            // Repository list
            // Depending on what repos are enabled, certain and versions will be displayed
            $extras['repos'] = implode('|', $this->_get_repository_list());

            // Account driver
            // Some apps are not compatible with different account drivers (i.e. active directory)
            // We need to know which driver is in use to filter out non-applicable apps
            $driver = 'unknown';
            if (clearos_library_installed('accounts/Accounts_Configuration')) {
                try {
                    $accounts = new Accounts_Configuration();
                    $driver = $accounts->get_driver();
                } catch (\Exception $e) {
                    // Not really worried about
                }
            }
            $extras['account_driver'] = $driver;

            // Mode
            // We can simplify the Marketplace to show only plugins when master mode is enabled...slave mode, we show disabled apps.
            // In standalone mode, there is no need to show any plugin...just noise.
            if (clearos_library_installed('mode/Mode_Engine')) {
                try {
                    $mode_object = Mode_Factory::create();
                    $mode = $mode_object->get_mode();
                } catch (\Exception $e) {
                    // Not really worried about
                }
            }

            if (empty($mode))
                $mode = 'standalone';

            $extras['mode'] = $mode;

            return $extras;
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   R O U T I N E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for sdn_username
     *
     * @param string $sdn_username SDN Username
     *
     * @return boolean TRUE if sdn_username is valid
     */

    public function validate_sdn_username($sdn_username)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^[A-Za-z0-9\.\- ]+$/", $sdn_username))
            return lang('marketplace_sdn_username_is_invalid');
        if (strlen($sdn_username) < 4)
            return lang('marketplace_sdn_username_min_length') . ' (4).';
    }

    /**
     * Validation routine for sdn_password
     *
     * @param string $sdn_password max instances
     *
     * @return boolean TRUE if sdn_password is valid
     */

    public function validate_sdn_password($sdn_password)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^[A-Za-z0-9\\!\\@\\#\\$\\%\\^\\*\\(\\)-_\\.\\?]+$/", $sdn_password))
            return lang('marketplace_sdn_password_is_invalid');
        if (strlen($sdn_password) < 4)
            return lang('marketplace_sdn_password_min_length') . ' (4).';
    }

    /**
     * Validation routine for system_name
     *
     * @param string $system_name system_name
     *
     * @return boolean TRUE if system_name is valid
     */

    public function validate_system_name($system_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^[A-Za-z0-9\\ \\-\\_\\(\\)\\#\\.\\@]+$/", $system_name))
            return lang('marketplace_system_name_is_invalid');
    }

    /**
     * Validation routine for number of apps to display.
     *
     * @param string $number_of_apps number of apps
     *
     * @return mixed void if number_of_apps is valid, errmsg otherwise
     */

    public function validate_number_of_apps_to_display($number_of_apps)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (is_nan($number_of_apps) || $number_of_apps < 0)
            return lang('marketplace_number_of_apps_is_invalid');
    }

    /**
     * Validation routine for display format.
     *
     * @param string $display_format display format
     *
     * @return mixed void if display_format is valid, errmsg otherwise
     */

    public function validate_display_format($display_format)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^(list|tile|table)$/", $display_format))
            return lang('marketplace_invalid_display_type');
    }

    /**
     * Validation routine for review pseudonym.
     *
     * @param string $pseudonym pseudonym
     *
     * @return mixed void if pseudonym is valid, errmsg otherwise
     */

    public function validate_pseudonym($pseudonym)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^[A-Za-z0-9\\ \\-\\_]+$/", $pseudonym))
            return lang('marketplace_pseudonym_is_invalid');
    }

    /**
     * Validation routine for hide support policy info.
     *
     * @param boolean $hide hide
     *
     * @return mixed void if support policy is valid, errmsg otherwise
     */

    public function validate_hide_support_policy($hide)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for hide recommended apps.
     *
     * @param boolean $hide hide
     *
     * @return mixed void if recommended apps is valid, errmsg otherwise
     */

    public function validate_hide_recommended_apps($hide)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for search query.
     *
     * @param string $search search
     *
     * @return mixed void if search is valid, errmsg otherwise
     */

    public function validate_search($search)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! preg_match("/^[\w\W\s]*$/", $search))
            return lang('marketplace_search_is_invalid');
    }

    /**
     * Validation routine for category query.
     *
     * @param string $category category
     *
     * @return mixed void if category is valid, errmsg otherwise
     */

    public function validate_filter_category($category)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! in_array($category, $this->filter_category))
            return lang('marketplace_filter_category_is_invalid');
    }

    /**
     * Validation routine for price query.
     *
     * @param string $price price
     *
     * @return mixed void if price is valid, errmsg otherwise
     */

    public function validate_filter_price($price)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! in_array($price, $this->filter_price))
            return lang('marketplace_filter_price_is_invalid');
    }
    /**
     * Validation routine for filter intro query.
     *
     * @param string $intro filter intro
     *
     * @return mixed void if intro is valid, errmsg otherwise
     */

    public function validate_filter_intro($intro)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! in_array($intro, $this->filter_intro))
            return lang('marketplace_filter_intro_is_invalid');
    }
    /**
     * Validation routine for status query.
     *
     * @param string $status status
     *
     * @return mixed void if status is valid, errmsg otherwise
     */

    public function validate_filter_status($status)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! in_array($status, $this->filter_status))
            return lang('marketplace_filter_status_is_invalid');
    }
}
