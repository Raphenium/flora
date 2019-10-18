<?php

function check_contract_restrictions($id, $hash)
{
    $CI = & get_instance();
    $CI->load->model('contracts_model');

    if (!$hash || !$id) {
        show_404();
    }

    if (!is_client_logged_in() && !is_staff_logged_in()) {
        if (get_option('view_contract_only_logged_in') == 1) {
            redirect_after_login_to_current_url();
            redirect(site_url('clients/login'));
        }
    }

    $contract = $CI->contracts_model->get($id);
    if (!$contract || ($contract->hash != $hash)) {
        show_404();
    }
    // Do one more check
    if (!is_staff_logged_in()) {
        if (get_option('view_contract_only_logged_in') == 1) {
            if ($contract->client != get_client_user_id()) {
                show_404();
            }
        }
    }
}

/**
 * Function that will search possible contracts templates in applicaion/views/admin/contracts/templates
 * Will return any found files and user will be able to add new template
 * @return array
 */
function get_contract_templates()
{
    $contract_templates = [];
    if (is_dir(VIEWPATH . 'admin/contracts/templates')) {
        foreach (list_files(VIEWPATH . 'admin/contracts/templates') as $template) {
            $contract_templates[] = $template;
        }
    }

    return $contract_templates;
}

function prepare_contracts_for_export($customer_id)
{
    $CI = &get_instance();

    if (!class_exists('contracts_model')) {
        $CI->load->model('contracts_model');
    }

    $CI->db->where('client', $customer_id);
    $contracts = $CI->db->get('tblcontracts')->result_array();

    $CI->db->where('show_on_client_portal', 1);
    $CI->db->where('fieldto', 'contracts');
    $CI->db->order_by('field_order', 'asc');
    $custom_fields = $CI->db->get('tblcustomfields')->result_array();

    $CI->load->model('currencies_model');
    foreach ($contracts as $contractsKey => $contract) {
        $contracts[$contractsKey]['comments']        = $CI->contracts_model->get_comments($contract['id']);
        $contracts[$contractsKey]['renewal_history'] = $CI->contracts_model->get_contract_renewal_history($contract['id']);
        $contracts[$contractsKey]['tracked_emails']  = get_tracked_emails($contract['id'], 'contract');

        $contracts[$contractsKey]['additional_fields'] = [];
        foreach ($custom_fields as $cf) {
            $contracts[$contractsKey]['additional_fields'][] = [
                    'name'  => $cf['name'],
                    'value' => get_custom_field_value($contract['id'], $cf['id'], 'contracts'),
                ];
        }
    }

    return $contracts;
}

function send_contract_signed_notification_to_staff($contract_id)
{
    $CI = &get_instance();
    $CI->db->where('id', $contract_id);
    $contract = $CI->db->get('tblcontracts')->row();

    if (!$contract) {
        return false;
    }

    // Get creator
    $CI->db->select('staffid, email');
    $CI->db->where('staffid', $contract->addedfrom);
    $staff_contract = $CI->db->get('tblstaff')->result_array();

    $CI->load->model('emails_model');

    $CI->emails_model->set_rel_id($contract->id);
    $CI->emails_model->set_rel_type('contract');
    $notifiedUsers = [];
    foreach ($staff_contract as $member) {
        $notified = add_notification([
                        'description'     => 'not_contract_signed',
                        'touserid'        => $member['staffid'],
                        'fromcompany'     => 1,
                        'fromuserid'      => null,
                        'link'            => 'contracts/contract/' . $contract->id,
                        'additional_data' => serialize([
                            '<b>' . $contract->subject . '</b>',
                        ]),
                    ]);

        if ($notified) {
            array_push($notifiedUsers, $member['staffid']);
        }

        $merge_fields = [];
        $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($contract->client));
        $merge_fields = array_merge($merge_fields, get_contract_merge_fields($contract->id));
        $merge_fields = array_merge($merge_fields, get_staff_merge_fields($member['staffid']));
        $CI->emails_model->send_email_template('contract-signed-to-staff', $member['email'], $merge_fields);
    }

    pusher_trigger_notification($notifiedUsers);
}
