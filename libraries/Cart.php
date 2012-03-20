<?php

/**
 * Cart class.
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

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\marketplace\Marketplace as Marketplace;

clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('marketplace/Marketplace');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Cart class.
 *
 * @category   Apps
 * @package    Marketplace
 * @subpackage Libraries
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Cart extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $is_loaded = FALSE;
    protected $contents = array();
    protected $CI = NULL;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Cart constructor.
     *
     */

    function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->CI =& get_instance();
    }

    /**
     * Returns an array of items in the shopping cart.
     *
     * @param boolean $saved flag to retrieve current or saved items (default is all items in the current cart)
     *
     * @return array an array of Cart_Item objects
     * @throws Engine_Exception
     */

    public function get_items($saved = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_from_file();

        return $this->contents;
    }

    /**
     * Adds an item to the shopping cart
     *
     * @param obj $item a Cart_Item
     *
     * @return void
     * @throws Engine_Exception
     */

    public function add_item($item)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_from_file();

        $counter = 0;
        $found = FALSE;
        foreach ($this->contents as $cart_item) {
            if ($cart_item->get_id() == $item->get_id()) {
                $this->contents[$counter] = $item;
                $found = TRUE;
                break;
            }
            $counter++;
        }

        if (!$found)
            $this->contents[] = $item;

        $this->_save_to_file();
    }

    /**
     * Removes an item from the shopping cart
     *
     * @param string $key a key representing a Cart_Item in the cart
     *
     * @return void
     * @throws Engine_Exception
     */

    public function remove_item($key)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_from_file();

        if (array_key_exists($key, $this->contents))
            unset($this->contents[$key]);
        else
            throw new Engine_Exception(lang('marketplace_item_not_found'), CLEAROS_ERROR);

        $this->_save_to_file();
    }

    /**
     * Returns a list of item fields in cart
     *
     * @param String $field specifies what Cart_Item field you want to return
     *  - id
     *  - pid
     *  - description
     *
     * @return array an array
     *
     * @throws Engine_Exception
     */

    public function get_list($field = 'id')
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->is_loaded)
            $this->_load_from_file();

        $list = Array();

        foreach ($this->contents as $item) {
            if ($field == 'id')
                $list[] = $item->get_id();
            else if ($field == 'package_name')
                $list[] = preg_replace("/_/", "-", $item->get_id());
            else if ($field == 'pid')
                $list[] = $item->get_pid();
            else if ($field == 'description')
                $list[] = $item->get_description();
        }

        return $list;
    }

    /**
     * Clears the shopping cart
     *
     * @return void
     * @throws Engine_Exception
     */

    public function clear()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(CLEAROS_CACHE_DIR . "/cart." . $this->CI->session->userdata['sdn_rest_id']);

            if ($file->exists())
                $file->delete();
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }

    /**
     * Saves cart contents to file
     *
     * @access private
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _save_to_file()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(CLEAROS_CACHE_DIR . "/cart." . $this->CI->session->userdata['sdn_rest_id']);

            if ($file->exists())
                $file->delete();

            $file->create('webconfig', 'webconfig', 600);
            foreach ($this->contents as $lineitem) {
                $file->add_lines(serialize($lineitem));
            }
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }

    /**
     * Loads cart contents from file
     *
     * @access private
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load_from_file()
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(CLEAROS_CACHE_DIR . "/cart." . $this->CI->session->userdata['sdn_rest_id']);

            if (!$file->exists()) {
                $this->is_loaded = TRUE;
                return;
            }

            $contents = $file->get_contents_as_array();

            include_once clearos_app_base('marketplace') . '/libraries/Cart_Item.php';

            foreach ($contents as $item) {
                $cart_item = unserialize($item);
                if (is_object($cart_item))
                    $this->contents[$cart_item->get_id()] = $cart_item;
            }

            $this->is_loaded = TRUE;
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_WARNING);
        }
    }

}
