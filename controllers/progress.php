<?php

/**
 * Marketplace progress controller.
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Marketplace progress controller.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Progress extends ClearOS_Controller
{
    /**
     * Marketplace progress controller
     *
     * @param boolean $alt_in_use other user or service is using package manager
     * @return view
     */

    function index($alt_in_use = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $data = array('alt_in_use' => $alt_in_use);

        $this->load->library('marketplace/Marketplace');

        $this->page->view_form('marketplace/progress', $data, lang('marketplace_install_progress'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }

    function busy()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->index(TRUE);
    }
}
