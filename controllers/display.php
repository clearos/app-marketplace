<?php

/**
 * Marketplace display controller.
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

use \clearos\apps\marketplace\Marketplace as Market;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Marketplace display controller.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Display extends ClearOS_Controller
{
    /**
     * Marketplace default controller
     *
     * @param String type type of display
     *
     * @return null
     */

    function index($type)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->load->library('marketplace/Marketplace');

        try {
            $this->marketplace->set_display_format($type);
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
        redirect('/marketplace');
    }
}
