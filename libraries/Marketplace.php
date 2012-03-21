<?php

/**
 * Marketplace class.
 *
 * @category   Apps
 * @package    Marketplace
 * @subpackage Libraries
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

clearos_load_library('base/Configuration_File');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('base/Yum');
clearos_load_library('accounts/Accounts_Configuration');
clearos_load_library('mode/Mode_Engine');
clearos_load_library('mode/Mode_Factory');
clearos_load_library('clearcenter/Rest');

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
 * @category   Apps
 * @package    Marketplace
 * @subpackage Libraries
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

            $this->_save_to_cache($cachekey, $result);
        
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

            if (!$realtime && $this->_check_cache($cachekey)) {
                return $this->cache;
            }

            $extras = array("id" => $id);

            $result = $this->request('marketplace', 'get_eula', $extras);

            return $result;
        } catch (Exception $e) {
            throw new Webservice_Exception(clearos_exception_message($e), CLEAROS_ERROR);
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

            $cache_time = 604800; // 1 week in seconds
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
     * @return array a list of installed apps
     *
     * @throws Engine_Exception
     */

    public function get_installed_apps()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $list = array();
            $shell = new Shell();
            $exitcode = $shell->execute(
                self::COMMAND_RPM,
                "-qa --queryformat \"%{NAME}|%{VERSION}|%{RELEASE}\\n\"| grep ^" . self::APP_PREFIX,
                FALSE
            );
            if ($exitcode != 0)
                throw new Engine_Exception(lang('marketplace_unable_to_get_installed_app_list'), CLEAROS_WARNING);
            $rows = $shell->get_output();
            foreach ($rows as $row) {
                $parts = explode("|", $row); 
                $list[$parts[0]] = array('version' => $parts[1], 'release' => $parts[2]);
            }
            return $list;
        } catch (Engine_Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
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
     * @return string
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
            // Some apps are nto compatible with different account drivers (i.e. active directory)
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
            $mode = 'standalone';
            if (clearos_library_installed('mode/Mode_Engine')) {
                try {
                    $mode_object = Mode_Factory::create();
                    $mode = $mode_object->get_mode();
                } catch (\Exception $e) {
                    // Not really worried about
                }
            }

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
}
