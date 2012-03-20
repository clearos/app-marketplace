<?php

/**
 * Marketplace Search controller.
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
 * Marketplace Search controller.
 *
 * @category   Apps
 * @package    Marketplace
 * @subpackage Controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Search extends ClearOS_Controller
{
    /**
     * Marketplace default controller
     *
     * @param int    $page     page
     * @param String $search   search string
     * @param String $category category
     * @param String $price    price
     * @param String $intro    intro
     * @param String $install  install
     *
     * @return view
     */

    function index($page = 0, $search = '', $category = 'category_all', $price = 'price_all', $intro = 'intro_all', $install = 'install_all')
    {
        
        clearos_profile(__METHOD__, __LINE__);

        $this->load->library('marketplace/Marketplace');

        $data = array();

        // Initialize category
        $categories = array('category_all', 'server', 'network', 'gateway');
        foreach ($categories as $category_option)
            $data['category']['select'][$category_option] = '';

        // Initialize pricing
        $pricing = array('price_all', 'free', 'paid');
        foreach ($pricing as $price_option)
            $data['price']['select'][$price_option] = '';

        // Initialize intro date
        $intros = array('intro_all', '7', '30', '180', '365');
        foreach ($intros as $intro_option)
            $data['intro']['select'][$intro_option] = '';

        // Initialize install status
        $installs = array('install_all', 'installed', 'upgrades', 'new');
        foreach ($installs as $install_option)
            $data['install']['select'][$install_option] = '';

        $data['page'] = (int)$page;

        if ($this->input->post('search'))
            $data['search'] = $this->input->post('search');
        else
            $data['search'] = $search;

        if ($data['search'] == 'search_all')
            unset($data['search']);

        if ($this->input->post('filter_category'))
            $data['category']['select'][$this->input->post('filter_category')] = ' SELECTED';
        else
            $data['category']['select'][$category] = ' SELECTED';

        if ($this->input->post('filter_price'))
            $data['price']['select'][$this->input->post('filter_price')] = ' SELECTED';
        else
            $data['price']['select'][$price] = ' SELECTED';

        if ($this->input->post('filter_intro'))
            $data['intro']['select'][$this->input->post('filter_intro')] = ' SELECTED';
        else
            $data['intro']['select'][$intro] = ' SELECTED';

        if ($this->input->post('filter_install'))
            $data['install']['select'][$this->input->post('filter_install')] = ' SELECTED';
        else
            $data['install']['select'][$install] = ' SELECTED';

        $data['number_of_apps_to_display'] = $this->marketplace->get_number_of_apps_to_display();

        $this->page->view_form('marketplace/marketplace', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }
}
