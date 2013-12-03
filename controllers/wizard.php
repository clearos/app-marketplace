<?php

/**
 * Marketplace wizard controller.
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
 * Marketplace wizard controller.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage controllers
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
        $this->load->library('base/OS');

        // Don't want to show this page if we are not in wizard mode
        if (!$this->session->userdata('wizard'))
            redirect('/marketplace');
            
        // Load view data
        //---------------

        try {
            $os_name = $this->os->get_name();

            $data['is_professional'] = (preg_match('/ClearOS Professional/', $os_name)) ? TRUE : FALSE;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        $mode = $this->session->userdata('wizard_marketplace_mode');
        if (isset($mode) && $mode !== FALSE)
            $data['mode'] = $mode;
        else
            $data['mode'] = 'mode1';

        $data['hide_banner'] = TRUE;

        // Load view
        //----------

        $this->page->view_form('marketplace/wizard_intro', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT));
    }

    /**
     * Marketplace wizard selection controller
     *
     * @return view
     */

    function selection()
    {
        // Load dependencies
        //------------------

        $this->lang->load('marketplace');
        $this->load->library('marketplace/Marketplace');
        $this->load->library('marketplace/Cart');
        $this->load->helper('number');

        // Don't want to show this page if we are not in wizard mode
        if (!$this->session->userdata('wizard'))
            redirect('/marketplace');

        $mode = $this->session->userdata('wizard_marketplace_mode');
        $category = 'all';
        if ($mode === FALSE) {
            $mode = 'mode1';
            $category = 'cloud';
        } else if ($mode == 'mode2') {
            $category = 'cloud';
        } else if ($mode == 'mode4') {
            // Exit Wizard
            $this->stop();
            return;
        }

        // Load view
        //----------

        $data = array();

        // Note: setting 'new' to 'all' below is handy for testing
        $this->marketplace->set_search_criteria (
            '',
            $category,
            'all',
            'all',
            'new'
        );
        $data['number_of_apps_to_display'] = '0';
        $data['hide_banner'] = TRUE;
        $data['display_format'] = 'tile';
        $data['wizard'] = TRUE;
        $data['mode'] = $mode;
        $data['os_name'] = $this->session->userdata('os_name');

        // Handle form submit
        //-------------------

        if ($this->input->post('reset')) {
            try {
                $this->marketplace->delete_qsf();
                $this->cart->clear();
                redirect('/marketplace/wizard/selection');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }
        $config['upload_path'] = CLEAROS_TEMP_DIR;
        $config['allowed_types'] = 'txt';
        $config['overwrite'] = TRUE;
        $config['file_name'] = Marketplace::FILE_QSF;

        $this->load->library('upload', $config);

        if ($this->input->post('upload')) {
            if (!$this->upload->do_upload('qsf')) {
                $this->page->set_message($this->upload->display_errors());
                redirect('/marketplace/wizard/selection');
                return;
            } else {
                try {
                    $upload = $this->upload->data();
                    $this->marketplace->set_qsf($upload['file_name']);
                    $data['filename'] = $upload['file_name'];
                    $data['size'] = byte_format($this->marketplace->get_qsf_size(), 1);
                    $data['qsf'] = $this->marketplace->get_qsf_info();
                    $data['qsf_ready'] = TRUE;
                } catch (Exception $e) {
                    $this->page->set_message(clearos_exception_message($e), 'warning');
                    $this->marketplace->delete_qsf();
                    redirect('/marketplace/wizard/selection');
                    return;
                }
            }

            $this->session->set_userdata(array('wizard_redirect' => 'marketplace/wizard/install'));
        }

        if ($mode == 'mode3')
            $this->page->view_form('marketplace/quick_select', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT));
        else if ($mode == 'mode1')
            $this->page->view_form('marketplace/novice', $data, lang('marketplace_marketplace'));
        else
            $this->page->view_form('marketplace/category', $data, lang('marketplace_marketplace'));
    }

    /**
     * Wizard exit.
     *
     * @return void
     */

    function stop()
    {
        // Load dependencies
        //------------------

        $this->load->library('marketplace/Marketplace');

        // Reset our Marketplace search so the last category is not displayed by default
        $this->marketplace->reset_search_criteria();

        redirect('base/wizard/stop');
        return;

    }

    /**
     * Set wizard mode.
     *
     * @return void
     */

    function set_mode()
    {
        // Load dependencies
        //------------------

        $this->session->set_userdata(array('wizard_redirect' => 'marketplace/wizard/selection'));
        $this->session->set_userdata(array('wizard_marketplace_mode' => $this->input->post('mode')));

        return;
    }

}
