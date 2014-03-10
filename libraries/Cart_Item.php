<?php

/**
 * Cart_Item class.
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
 * Cart_Item class.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage libraries
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Cart_Item extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const CLASS_MISC = "misc";
    const CLASS_SERVICE = "service";
    const CLASS_DNS = "dns";
    const CLASS_APP = "app";
    const CLASS_SSL = "ssl";
    const UNIT_EACH = 0;
    const UNIT_MONTHLY = 100;
    const UNIT_1_YEAR = 1000;
    const UNIT_2_YEAR = 2000;
    const UNIT_3_YEAR = 3000;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $id;
    protected $item = Array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Cart_Item constructor.
     *
     * @param int $id a unique cart item ID
     *
     * @return void
     */

    function __construct($id = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($id === NULL)
            $id = rand();

        $this->id = preg_replace("/_/", "-", $id);
    }

    /**
     * Cart_Item wakeup call.
     *
     * @return void
     */

    /**
     * Sets the cart item using an array.
     *
     * @param array $info an array containing all information for a cart item
     *
     * @return void
     * @throws ValidationException
     */

    public function set($info)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->set_pid($info['pid']);
        $this->set_description($info['description']);
        $this->set_quantity($info['quantity']);
        $this->set_unit($info['unit']);
        $this->set_unit_price($info['unit_price']);
        $this->set_discount($info['discount']);
        $this->set_currency($info['currency']);

        if (isset($info['note']))
            $this->set_note($info['note']);
        else 
            $this->set_note('');

        $this->set_exempt($info['exempt']);
        $this->set_evaluation($info['evaluation']);

        if (isset($info['prorated']))
            $this->set_prorated($info['prorated']);
        else
            $this->set_prorated('');

        $this->set_pid_bitmask($info['pid_bitmask']);

        if (isset($info['eula']))
            $this->set_eula($info['eula']);
        else
            $this->set_eula(FALSE);

        if (isset($info['class']))
            $this->set_class($info['class']);
        else
            $this->set_class(self::CLASS_MISC);

        if (isset($info['group']))
            $this->set_group($info['group']);
        else
            $this->set_group('group');

        if (isset($info['upgrade']))
            $this->set_upgrade($info['upgrade']);
        else
            $this->set_upgrade(FALSE);
    }

    /**
     * Sets the cart ID.
     *
     * @param int $id the cart item ID
     *
     * @return void
     * @throws ValidationException
     */

    public function set_id($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->id = $id;
    }

    /**
     * Sets the cart item's product ID.
     *
     * @param int $pid a product ID from ClearSDN
     *
     * @return void
     * @throws ValidationException
     */

    public function set_pid($pid)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_pid($pid));

        $this->item[$this->id]['pid'] = $pid;
    }

    /**
     * Sets the cart item's product description.
     *
     * @param string $description a product description from ClearSDN
     *
     * @return void
     * @throws ValidationException
     */

    public function set_description($description)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['description'] = $description;
    }

    /**
     * Sets the cart quantity.
     *
     * @param int $quantity the product quantity
     *
     * @return void
     * @throws ValidationException
     */

    public function set_quantity($quantity)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['quantity'] = $quantity;
    }

    /**
     * Sets the cart item's unit.
     *
     * @param string $unit the product unit
     *
     * @return void
     * @throws ValidationException
     */

    public function set_unit($unit)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['unit'] = $unit;
    }

    /**
     * Sets the cart item's product discount.
     *
     * @param float $discount the product discount
     *
     * @return void
     * @throws ValidationException
     */

    public function set_discount($discount)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['discount'] = $discount;
    }

    /**
     * Sets the cart item's product unit price.
     *
     * @param float $unit_price the product unit price
     *
     * @return void
     * @throws ValidationException
     */

    public function set_unit_price($unit_price)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['unit_price'] = $unit_price;
    }

    /**
     * Sets the cart item's product currency.
     *
     * @param String $currency the unit price currency
     *
     * @return void
     * @throws ValidationException
     */

    public function set_currency($currency)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['currency'] = $currency;
    }

    /**
     * Sets the cart item's note.
     *
     * @param String $note any specific notes
     *
     * @return void
     * @throws ValidationException
     */

    public function set_note($note)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['note'] = $note;
    }

    /**
     * Sets the cart item's exemption.
     *
     * @param Boolean $exempt exempt from payment
     *
     * @return void
     * @throws ValidationException
     */

    public function set_exempt($exempt)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['exempt'] = $exempt;
    }

    /**
     * Sets the cart item's evaluation.
     *
     * @param Boolean $evaluation evaluation
     *
     * @return void
     * @throws ValidationException
     */

    public function set_evaluation($evaluation)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['evaluation'] = $evaluation;
    }

    /**
     * Sets the cart item's prorated flag.
     *
     * @param Boolean $prorated prorated
     *
     * @return void
     * @throws ValidationException
     */

    public function set_prorated($prorated)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['prorated'] = $prorated;
    }

    /**
     * Sets a product bitmask for changing the behavior.
     *
     * @param int $pid_bitmask bitmask
     *
     * @return void
     * @throws ValidationException
     */

    public function set_pid_bitmask($pid_bitmask)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['pid_bitmask'] = $pid_bitmask;
    }

    /**
     * Sets the cart class.
     *
     * @param String $class the class of the product
     *
     * @return void
     * @throws ValidationException
     */

    public function set_class($class)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['class'] = $class;
    }

    /**
     * Sets the cart group ID.
     *
     * @param String $group the group ID
     *
     * @return void
     * @throws ValidationException
     */

    public function set_group($group)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['group'] = $group;
    }

    /**
     * Sets the cart upgrade flag.
     *
     * @param boolean $upgrade the upgrade flag
     *
     * @return void
     * @throws ValidationException
     */

    public function set_upgrade($upgrade)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['upgrade'] = $upgrade;
    }

    /**
     * Sets the cart eula.
     *
     * @param Boolean $eula true/false
     *
     * @return void
     * @throws ValidationException
     */

    public function set_eula($eula)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->item[$this->id]['eula'] = $eula;
    }

    /**
     * Get a shopping cart item
     *
     * @return array  cart information
     * @throws EngineException
     */

    public function get()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item;
    }

    /**
     * Gets the cart item's ID.
     *
     * @return int ID
     */

    public function get_id()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->id;
    }

    /**
     * Gets the cart item's product ID.
     *
     * @return int product ID
     */

    public function get_pid()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['pid'];
    }

    /**
     * Gets the cart item's product description.
     *
     * @return string description
     */

    public function get_description()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['description'];
    }

    /**
     * Gets the cart product quantity.
     *
     * @return int quantity
     */

    public function get_quantity()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['quantity'];
    }

    /**
     * Gets the cart item's product unit price.
     *
     * @return int the unit
     */

    public function get_unit()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['unit'];
    }

    /**
     * Gets the cart item's product unit.
     *
     * @return string product's unit suitable for display to the end user
     */

    public function get_display_unit()
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->item[$this->id]['unit'] == self::UNIT_EACH)
            return lang('marketplace_each');
        else if ($this->item[$this->id]['unit'] == self::UNIT_MONTHLY)
            return lang('marketplace_monthly');
        else if ($this->item[$this->id]['unit'] == self::UNIT_1_YEAR)
            return lang('marketplace_1_year');
        else if ($this->item[$this->id]['unit'] == self::UNIT_2_YEAR)
            return lang('marketplace_2_year');
        else if ($this->item[$this->id]['unit'] == self::UNIT_3_YEAR)
            return lang('marketplace_3_year');
        else
            return "";
    }

    /**
     * Gets the cart item's product unit price.
     *
     * @return float product's unit price
     */

    public function get_unit_price()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['unit_price'];
    }

    /**
     * Gets the cart item's product discount.
     *
     * @return float product's discount
     */

    public function get_discount()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['discount'];
    }

    /**
     * Gets the cart item's currency.
     *
     * @return string currency
     */

    public function get_currency()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['currency'];
    }

    /**
     * Gets the cart item's note.
     *
     * @return string notes associated with this item
     */

    public function get_note()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['note'];
    }

    /**
     * Gets the cart item's exempt status.
     *
     * @return boolean flag indicating exempt from payment
     */

    public function get_exempt()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['exempt'];
    }

    /**
     * Gets the cart item's evaluation status.
     *
     * @return boolean flag indicating evaluation
     */

    public function get_evaluation()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['evaluation'];
    }

    /**
     * Gets the cart item's prorated status.
     *
     * @return boolean flag indicating prorated status
     */

    public function get_prorated()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['prorated'];
    }

    /**
     * Gets the cart item's product bitmask.
     *
     * @return int bitmask of product properties
     */

    public function get_pid_bitmask()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['pid_bitmask'];
    }

    /**
     * Gets the cart item's class.
     *
     * @return string item class
     */

    public function get_class()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['class'];
    }

    /**
     * Gets the cart item's group ID.
     *
     * @return string the item's group type
     */

    public function get_group()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['group'];
    }

    /**
     * Gets the cart item's upgrade flag.
     *
     * @return boolean the item's upgrade type
     */

    public function get_upgrade()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['upgrade'];
    }

    /**
     * Gets the cart item's EULA flag.
     *
     * @return boolean the item's eula flag
     */

    public function get_eula()
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->item[$this->id]['eula'];
    }

    /**
     * Serializes object and saves to file.
     *
     * @param String $id a unique SDN session ID
     *
     * @return void
     * @throws Engine_Exception
     */

    public function serialize($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            file_put_contents(CLEAROS_CACHE_DIR . '/' . $this->get_id() . '.' . $id, serialize($this));
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Unserializes an object from file.
     *
     * @param String $id a unique SDN session ID
     *
     * @return void
     * @throws Engine_Exception
     */

    public function unserialize($id)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            if (!file_exists(CLEAROS_CACHE_DIR . '/' . $this->get_id() . '.' . $id)) {
                $marketplace = new Marketplace();
                $basename = preg_replace('/^' . Marketplace::APP_PREFIX . '/', '', $this->get_id());
                $basename = preg_replace("/-/", "_", $basename);
                $response = json_decode($marketplace->get_app_details($basename, TRUE));
                if (!$response->details->exists)
                    throw new Engine_Exception(lang('marketplace_app_does_not_exist') . ' - ' . $this->get_id() . '.');
            }
            $newobj = unserialize(file_get_contents(CLEAROS_CACHE_DIR . '/' . $this->get_id() . '.' . $id));
            $this->set_pid($newobj->get_pid());
            $this->set_description($newobj->get_description());
            $this->set_quantity($newobj->get_quantity());
            $this->set_unit($newobj->get_unit());
            $this->set_unit_price($newobj->get_unit_price());
            $this->set_discount($newobj->get_discount());
            $this->set_currency($newobj->get_currency());
            $this->set_note($newobj->get_note());
            $this->set_exempt($newobj->get_exempt());
            $this->set_evaluation($newobj->get_evaluation());
            $this->set_prorated($newobj->get_prorated());
            $this->set_pid_bitmask($newobj->get_pid_bitmask());
            $this->set_class($newobj->get_class());
            $this->set_group($newobj->get_group());
            $this->set_upgrade($newobj->get_upgrade());
            $this->set_eula($newobj->get_eula());
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   R O U T I N E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for product ID.
     *
     * @param int $pid cart PID.
     *
     * @return string error message if pid is invalid
     */

    public function validate_pid($pid)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($pid <= 0)
            return lang('marketplace_pid_is_invalid');
    }

    /**
     * Validation routine for description.
     *
     * @param string $description cart item description.
     *
     * @return string error message if description is invalid
     */

    public function validate_description($description)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($description === NULL || $description === '')
            return lang('marketplace_description_is_invalid');
    }

    /**
     * Validation routine for quantity.
     *
     * @param string $quantity cart item quantity.
     *
     * @return string error message if quantity is invalid
     */

    public function validate_quantity($quantity)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($quantity < 0)
            return lang('marketplace_quantity_is_invalid');
    }

    /**
     * Validation routine for unit.
     *
     * @param string $unit cart item unit.
     *
     * @return string error message if unit is invalid
     */

    public function validate_unit($unit)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($unit < 0)
            return lang('marketplace_unit_is_invalid');
    }

    /**
     * Validation routine for unit price.
     *
     * @param string $unit_price cart item unit price.
     *
     * @return string error message if unit price is invalid
     */

    public function validate_unit_price($unit_price)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($unit_price < 0)
            return lang('marketplace_unit_price_is_invalid');
    }

    /**
     * Validation routine for discount.
     *
     * @param string $discount cart item discount.
     *
     * @return string error message if discount is invalid
     */

    public function validate_discount($discount)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($discount < 0 || $discount > 100)
            return lang('marketplace_discount_is_invalid');
    }

    /**
     * Validation routine for currency.
     *
     * @param string $currency cart item currency.
     *
     * @return string error message if currency is invalid
     */

    public function validate_currency($currency)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (FALSE)
            return lang('marketplace_currency_is_invalid');
    }

    /**
     * Validation routine for note.
     *
     * @param string $note cart item note.
     *
     * @return string error message if note is invalid
     */

    public function validate_note($note)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (FALSE)
            return lang('marketplace_note_is_invalid');
    }

    /**
     * Validation routine for exempt.
     *
     * @param string $exempt cart item exempt.
     *
     * @return string error message if exempt is invalid
     */

    public function validate_exempt($exempt)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_bool($exempt))
            return lang('marketplace_exempt_is_invalid');
    }

    /**
     * Validation routine for evaluation.
     *
     * @param string $evaluation cart item is under an evaluation.
     *
     * @return string error message if evaluation is invalid
     */

    public function validate_evaluation($evaluation)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_bool($evaluation))
            return lang('marketplace_evaluation_is_invalid');
    }

    /**
     * Validation routine for prorated.
     *
     * @param string $prorated cart item has prorated discount.
     *
     * @return string error message if prorated is invalid
     */

    public function validate_prorated($prorated)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_bool($prorated))
            return lang('marketplace_prorated_is_invalid');
    }

    /**
     * Validation routine for PID bitmask.
     *
     * @param int $pid_bitmask PID bitmask
     *
     * @return string error message if PID bitmask is invalid
     */

    public function validate_pid_bitmask($pid_bitmask)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($pid_bitmask < 0)
            return lang('marketplace_pid_bitmask_is_invalid');
    }

    /**
     * Validation routine for class.
     *
     * @param string $class cart item class.
     *
     * @return string error message if class is invalid
     */

    public function validate_class($class)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (FALSE)
            return lang('marketplace_class_is_invalid');
    }

    /**
     * Validation routine for group.
     *
     * @param string $group cart item group.
     *
     * @return string error message if group is invalid
     */

    public function validate_group($group)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (FALSE)
            return lang('marketplace_group_is_invalid');
    }

    /**
     * Validation routine for upgrade flag.
     *
     * @param string $upgrade cart item upgrade flag.
     *
     * @return string error message if upgrade is invalid
     */

    public function validate_upgrade($upgrade)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!is_bool($upgrade))
            return lang('marketplace_upgrade_is_invalid');
    }

    /**
     * Validation routine for eula.
     *
     * @param string $eula cart item eula.
     *
     * @return string error message if eula is invalid
     */

    public function validate_eula($eula)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (FALSE)
            return lang('marketplace_eula_is_invalid');
    }

}
