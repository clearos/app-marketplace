<?php

/**
 * Marketplace Quick File Selector controller.
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
 * Marketplace QSF controller.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

class Qsf extends ClearOS_Controller
{
    /**
     * Marketplace qsf default controller
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('marketplace');
        $this->load->library('marketplace/Marketplace');
        $this->load->library('marketplace/Cart');
        $this->load->helper('number');

        // Load view
        //----------

        $data = array();

        $this->marketplace->reset_search_criteria();
        $data['number_of_apps_to_display'] = '0';
        $data['hide_banner'] = TRUE;
        $data['display_format'] = $this->marketplace->get_display_format();
        $data['mode'] = 'qsf-tool';

        // Handle form submit
        //-------------------

        if ($this->input->post('reset')) {
            try {
                $this->marketplace->delete_qsf();
                $this->cart->clear();
                redirect('/marketplace/qsf');
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
                $this->page->set_message($this->upload->display_errors(), 'warning');
                redirect('/marketplace/qsf');
                return;
            } else {
                try {
                    $upload = $this->upload->data();
                    $this->marketplace->set_qsf($upload['file_name']);
                    $data['filename'] = $upload['file_name'];
                    $data['size'] = byte_format($this->marketplace->get_qsf_size(), 1);
                    $data['qsf'] = $this->marketplace->get_qsf_info();
                    $data['qsf_ready'] = TRUE;
                    $data['wizard'] = FALSE;
                } catch (Exception $e) {
                    $this->page->set_message(clearos_exception_message($e), 'warning');
                    $this->marketplace->delete_qsf();
                    redirect('/marketplace/qsf');
                    return;
                }
            }
        }

        $this->page->view_form(
            'marketplace/quick_select', $data, lang('marketplace_marketplace'), array('type' => MY_Page::TYPE_SPOTLIGHT)
        );
    }
}
