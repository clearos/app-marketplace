<?php

/**
 * Marketplace wizard controller.
 *
 * @category   Apps
 * @package    Marketplace
 * @subpackage Controllers
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
 * Marketplace wizard controller.
 *
 * @category   Apps
 * @package    Marketplace
 * @subpackage Controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Wizard extends ClearOS_Controller
{
    /**
     * Marketplace wizard default controller
     *
     * @param String $category category
     *
     * @return view
     */

    function index($category)
    {
        // Load dependencies
        //------------------

        $this->lang->load('marketplace');
        $this->load->library('marketplace/Marketplace');

        // Load view
        //----------

        $data = array();

        // Note: commenting out the next line is handy for testing
        $data['install']['select']['new'] = ' SELECTED';
        $data['category']['select'][$category] = ' SELECTED';
        $data['number_of_apps_to_display'] = '30';
        $data['hide_banner'] = TRUE;

        $this->page->view_form('marketplace/marketplace_wizard', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }

    /**
     * Wizard introduction.
     *
     * @return view
     */

    function intro()
    {
        // Load dependencies
        //------------------

        $this->lang->load('marketplace');
        $this->load->library('base/OS');

        // Load view data
        //---------------

        try {
            $os_name = $this->os->get_name();

            $data['is_professional'] = (preg_match('/ClearOS Professional/', $os_name)) ? TRUE : FALSE;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load view
        //----------

        $this->page->view_form('marketplace/wizard_intro', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }
}
