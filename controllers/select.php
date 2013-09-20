<?php

/**
 * Marketplace select controller.
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

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Marketplace select controller.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Select extends ClearOS_Controller
{
    /**
     * Marketplace select controller
     *
     * @return view
     */

    function index()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load dependencies
        //------------------

        $this->lang->load('marketplace');
        $this->load->library('marketplace/Marketplace');

        // Load view
        //----------

        $data = array();

        $data['number_of_apps_to_display'] = '0';

        $data['display_format'] = $this->marketplace->get_display_format();
        $data['mode'] = 'feature-wizard';
        $data['os_name'] = $this->session->userdata('os_name');

        $this->page->view_form(
            'marketplace/novice', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT)
        );
    }

    /**
     * Marketplace cancel select controller
     *
     * @return view
     */

    function cancel()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Load dependencies
        //------------------

        $this->lang->load('marketplace');
        $this->load->library('marketplace/Marketplace');

        // Reset our Marketplace search so the last category is not displayed by default
        $this->marketplace->reset_search_criteria();
        redirect('marketplace');
    }
}
