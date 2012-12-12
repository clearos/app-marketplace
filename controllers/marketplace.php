<?php

/**
 * Marketplace controller.
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

use \clearos\apps\marketplace\Marketplace as Market;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Marketplace controller.
 *
 * @category   Apps
 * @package    Marketplace
 * @subpackage Controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Marketplace extends ClearOS_Controller
{
    /**
     * Marketplace default controller
     *
     * @return view
     */

    function index()
    {
        
        clearos_profile(__METHOD__, __LINE__);

        clearos_load_language('marketplace');
        $this->load->library('marketplace/Marketplace');

        // If yum is running, show progress
        $this->load->library('base/Yum');
        if ($this->yum->is_busy()) {
            redirect('marketplace/progress');
            return;
        }
 
        $data['number_of_apps_to_display'] = $this->marketplace->get_number_of_apps_to_display();

        // Search and filter history
        $data['filter'] = $this->marketplace->get_search_history();

        $this->page->view_form('marketplace', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }

    /**
     * Marketplace default controller
     *
     * @param String $basename app basename
     * @return view
     */

    function view($basename)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->load->library('marketplace/Marketplace');

        $this->load->library('base/Software', Market::APP_PREFIX . preg_replace("/_/", "-", $basename));
        $data = array();;
        $data['basename'] = $basename;
        $data['is_installed'] = $this->software->is_installed();
        $data['pseudonym'] = $this->marketplace->get_pseudonym();
        $this->page->view_form('marketplace/app', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }

    /**
     * Marketplace install controller
     *
     * @param String $action delete or install
     * @param String $id     if action is to delete, this represents the ID in the cart
     *
     * @return view
     */

    function install($action, $id)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->load->library('marketplace/Marketplace');
        $this->load->library('marketplace/Cart');
        clearos_load_language('marketplace');

        $data = array();
        try {
            if ($action == 'delete')
                $this->cart->remove_item($id);
        } catch (Exception $e) {
            $data['itemnotfound'] = clearos_exception_message($e);
        }

        // Search and filter history
        $data['filter'] = $this->marketplace->get_search_history();

        // Get items in cart
        $data['items'] = $this->cart->get_items();
        $this->page->view_form('marketplace/install', $data, lang('marketplace_install'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }

    /**
     * Marketplace install controller
     *
     * @param String $basename    app basename
     * @param String $confirm_key confirmation key
     *
     * @return view
     */

    function uninstall($basename, $confirm_key = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        clearos_load_language('marketplace');
        $this->load->library('marketplace/Marketplace');

        if ($confirm_key == $this->session->userdata('app_delete_key')) {
            redirect('/marketplace');
            // TODO
            return;
        }

        $data = array(
            'prefix' => Market::APP_PREFIX,
            'basename' => $basename
        );
    
        $data['app_delete_key'] = rand(0, 10000);
        $this->session->set_userdata(array('app_delete_key' => $data['app_delete_key']));
        $data['apps'] = $this->marketplace->get_app_deletion_dependancies($basename);

        $this->page->view_form('marketplace/uninstall', $data, lang('marketplace_uninstall'));
    }

    /**
     * Marketplace settings controller
     *
     * @return view
     */

    function settings()
    {
        clearos_profile(__METHOD__, __LINE__);

        $data = array();

        $this->load->library('marketplace/Marketplace');

        // Delete/clear Cache
        if ($this->input->post('delete_cache')) {
            try {
                $this->marketplace->delete_cache(NULL, TRUE);
                $this->page->set_message(lang('marketplace_cache_confirm'), 'info');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Handle form submit
        // Set validation rules
        //---------------------
         
        $this->form_validation->set_policy('number_of_apps_to_display', 'marketplace/Marketplace', 'validate_number_of_apps_to_display', TRUE);
        $this->form_validation->set_policy('pseudonym', 'marketplace/Marketplace', 'validate_pseudonym', TRUE);
        $this->form_validation->set_policy('hide_support_policy', 'marketplace/Marketplace', 'validate_hide_support_policy', FALSE);
        $this->form_validation->set_policy('hide_recommended_apps', 'marketplace/Marketplace', 'validate_hide_recommended_apps', FALSE);
        $form_ok = $this->form_validation->run();

        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->marketplace->set_number_of_apps_to_display($this->input->post('number_of_apps_to_display'));
                $this->marketplace->set_pseudonym($this->input->post('pseudonym'));
                $this->marketplace->set_hide_support_policy($this->input->post('hide_support_policy'));
                $this->marketplace->set_hide_recommended_apps($this->input->post('hide_recommended_apps'));
                redirect('/marketplace');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        try {
            $data['pseudonym'] = $this->marketplace->get_pseudonym();
            $data['number_of_apps_to_display'] = $this->marketplace->get_number_of_apps_to_display();
            $data['cache_size'] = $this->marketplace->get_cache_size();
            $data['hide_support_policy'] = $this->marketplace->get_hide_support_policy();
            $data['hide_recommended_apps'] = $this->marketplace->get_hide_recommended_apps();
        } catch (Exception $e) {
            $data['number_of_apps_to_display'] = 9;
        }

        // Load views
        //-----------

        $this->page->view_form('marketplace/settings', $data, lang('base_settings'));//, array('type' => MY_Page::TYPE_SPOTLIGHT));
    }

    /**
     * Marketplace purchase with no corresponding RPMs to install controller
     *
     * @return view
     */

    function no_rpms()
    {
        clearos_profile(__METHOD__, __LINE__);

        clearos_load_language('marketplace');

        $this->page->set_message(lang('marketplace_purchase_complete_no_rpms'), 'info');
        redirect('/marketplace/install');
    }
}
