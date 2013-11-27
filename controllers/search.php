<?php

/**
 * Marketplace Search controller.
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
 * Marketplace Search controller.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Search extends ClearOS_Controller
{
    /**
     * Marketplace search controller
     *
     * @param int $page page
     *
     * @return view
     */

    function index($page = 0)
    {
        
        clearos_profile(__METHOD__, __LINE__);

        $this->load->library('marketplace/Marketplace');

        $data = array();

        if ($this->input->post('clear_search')) {
            $this->marketplace->clear_search_history();
            redirect('/marketplace');
            return;
        }
        
        // Set validation rules
        //---------------------
         
        $this->form_validation->set_policy('search', 'marketplace/Marketplace', 'validate_search', FALSE);
        $this->form_validation->set_policy('filter_category', 'marketplace/Marketplace', 'validate_filter_category', TRUE);
        $this->form_validation->set_policy('filter_price', 'marketplace/Marketplace', 'validate_filter_price', TRUE);
        $this->form_validation->set_policy('filter_intro', 'marketplace/Marketplace', 'validate_filter_intro', TRUE);
        $this->form_validation->set_policy('filter_status', 'marketplace/Marketplace', 'validate_filter_status', TRUE);
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if ($this->input->post('filter_category') && $form_ok) {
            try {
                $this->marketplace->set_search_criteria (
                    ($this->input->post('search_cancel') ? '' : $this->input->post('search')),
                    $this->input->post('filter_category'),
                    $this->input->post('filter_price'),
                    $this->input->post('filter_intro'),
                    $this->input->post('filter_status')
                );
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Search and filter history
        $data['filter'] = $this->marketplace->get_search_history();
        $data['display_format'] = $this->marketplace->get_display_format();
        $data['page'] = (int)$page;
        $data['number_of_apps_to_display'] = $this->marketplace->get_number_of_apps_to_display();

        $this->page->view_form('marketplace/marketplace', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }

    /**
     * Marketplace search controller
     *
     * @param int $page page
     *
     * @return view
     */

    function category($category)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->load->library('marketplace/Marketplace');

        try {
            $this->marketplace->set_search_criteria('', $category, 'all', 'all', 'all');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Search and filter history
        $data['filter'] = '';
        $data['display_format'] = $this->marketplace->get_display_format();
        $data['page'] = 0;
        $data['number_of_apps_to_display'] = 0;

        $this->page->view_form('marketplace/marketplace', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }

    /**
     * Marketplace reset filter controller
     *
     * @return view
     */

    function reset_filter()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->load->library('marketplace/Marketplace');

        $this->marketplace->reset_search_criteria();
        redirect('/marketplace');
    }
}
