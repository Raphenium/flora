<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'tblsubscriptions.id as id',
    'tblsubscriptions.name as name',
    get_sql_select_client_company(),
    'tblprojects.name as project_name',
    'tblsubscriptions.status as status',
    'next_billing_cycle',
    'date_subscribed',
];

$sIndexColumn = 'id';
$sTable       = 'tblsubscriptions';

$filter = [];
$where  = [];

if ($this->ci->input->get('project_id')) {
    array_push($where, 'AND project_id=' . $this->ci->input->get('project_id'));
}

if ($this->ci->input->get('client_id')) {
    array_push($where, 'AND tblsubscriptions.clientid=' . $this->ci->input->get('client_id'));
}

if (!has_permission('subscriptions', '', 'view')) {
    array_push($where, 'AND tblsubscriptions.created_from=' . get_staff_user_id());
}

$statusIds = [];

foreach (get_subscriptions_statuses() as $status) {
    if ($this->ci->input->post('subscription_status_' . $status['id'])) {
        array_push($statusIds, $status['id']);
    }
}

if (count($statusIds) > 0) {
    $whereStatus = '';
    foreach ($statusIds as $key => $status) {
        $whereStatus .= 'tblsubscriptions.status="' . $status . '" OR ';
    }
    $whereStatus = rtrim($whereStatus, ' OR ');

    if ($this->ci->input->post('not_subscribed')) {
        $whereStatus .= ' OR stripe_subscription_id IS NULL OR stripe_subscription_id = ""';
    }
    array_push($where, 'AND (' . $whereStatus . ')');
} else {
    if ($this->ci->input->post('not_subscribed')) {
        array_push($where, 'AND ( stripe_subscription_id IS NULL OR stripe_subscription_id = "" )');
    }
}

$join = [
    'LEFT JOIN tblclients ON tblclients.userid = tblsubscriptions.clientid',
    'LEFT JOIN tblprojects ON tblprojects.id = tblsubscriptions.project_id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblsubscriptions.id',
    'tblsubscriptions.clientid as clientid',
    'stripe_subscription_id',
    'project_id',
    'hash',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $link       = admin_url('subscriptions/edit/' . $aRow['id']);
    $outputName = '<a href="' . $link . '">' . $aRow['name'] . '</a>';

    $outputName .= '<div class="row-options">';

    $outputName .= '<a href="' . site_url('subscription/' . $aRow['hash']) . '" target="_blank">' . _l('view_subscription') . '</a>';

    if (has_permission('subscriptions', '', 'edit')) {
        $outputName .= ' | <a href="' . admin_url('subscriptions/edit/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    if (empty($aRow['stripe_subscription_id']) && has_permission('subscriptions', '', 'delete')) {
        $outputName .= ' | <a href="' . admin_url('subscriptions/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $outputName .= '</div>';

    $row[] = $outputName;

    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';

    $row[] = '<a href="' . admin_url('projects/view/' . $aRow['project_id']) . '">' . $aRow['project_name'] . '</a>';

    if (empty($aRow['status'])) {
        $row[] = _l('subscription_not_subscribed');
    } else {
        $row[] = _l('subscription_' . $aRow['status'], '', false);
    }

    if ($aRow['next_billing_cycle']) {
        $row[] = _d(date('Y-m-d', $aRow['next_billing_cycle']));
    } else {
        $row[] = '-';
    }

    if ($aRow['date_subscribed']) {
        $row[] = _dt($aRow['date_subscribed']);
    } else {
        $row[] = '-';
    }


    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
