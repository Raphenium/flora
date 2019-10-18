    <?php
    if($invoice->status == 6){ ?>
    <div class="alert alert-info">
      <?php echo _l('invoice_draft_status_info'); ?>
   </div>
   <?php } ?>
  <div id="invoice-preview">
   <div class="row">
      <?php
      if($invoice->recurring > 0 || $invoice->is_recurring_from != NULL) {
        $recurring_invoice = $invoice;
        $next_recurring_date_compare = to_sql_date($recurring_invoice->date);
        if($recurring_invoice->last_recurring_date){
         $next_recurring_date_compare = $recurring_invoice->last_recurring_date;
      }
      if($invoice->is_recurring_from != NULL){
         $recurring_invoice = $this->invoices_model->get($invoice->is_recurring_from);
         $next_recurring_date_compare = $recurring_invoice->last_recurring_date;
      }
      if ($recurring_invoice->custom_recurring == 0) {
        $recurring_invoice->recurring_type = 'MONTH';
     }
     $next_date = date('Y-m-d', strtotime('+' . $recurring_invoice->recurring . ' ' . strtoupper($recurring_invoice->recurring_type),strtotime($next_recurring_date_compare)));
     ?>
     <div class="col-md-12">
      <div class="mbot10">
         <?php if($invoice->is_recurring_from == null && $recurring_invoice->cycles > 0 && $recurring_invoice->cycles == $recurring_invoice->total_cycles) { ?>
         <div class="alert alert-info no-mbot">
            <?php echo _l('recurring_has_ended', _l('invoice_lowercase')); ?>
         </div>
         <?php } else { ?>

         <span class="label label-default padding-5">
            <?php if($invoice->status == 6){
               echo '<i class="fa fa-exclamation-circle fa-fw text-warning" data-toggle="tooltip" title="'._l('recurring_invoice_draft_notice').'"></i>';
            } ?>
            <?php echo _l('cycles_remaining'); ?>:
            <b>
               <?php if($recurring_invoice->cycles == 0){
                  echo _l('cycles_infinity');
               } else {
                  echo $recurring_invoice->cycles - $recurring_invoice->total_cycles;
               } ?>
            </b>
         </span>
         <?php } ?>
         <?php if($recurring_invoice->cycles == 0 || $recurring_invoice->cycles != $recurring_invoice->total_cycles){ ?>
         <?php echo '<span class="label label-default padding-5 mleft5"><i class="fa fa-question-circle fa-fw" data-toggle="tooltip" data-title="'._l('recurring_recreate_hour_notice',_l('invoice')).'"></i> ' . _l('next_invoice_date','<b>'._d($next_date).'</b>') .'</span>'; ?>
         <?php } ?>
      </div>
      <?php if($invoice->is_recurring_from != NULL){ ?>
      <?php echo '<p class="text-muted mtop15">'._l('invoice_recurring_from','<a href="'.admin_url('invoices/list_invoices/'.$invoice->is_recurring_from).'" onclick="init_invoice('.$invoice->is_recurring_from.');return false;">'.format_invoice_number($invoice->is_recurring_from).'</a></p>'); ?>
      <?php } ?>
   </div>
   <div class="clearfix"></div>
   <hr class="hr-10" />
   <?php } ?>
   <?php if($invoice->project_id != 0){ ?>
   <div class="col-md-12">
      <h4 class="font-medium mtop15 mbot20"><?php echo _l('related_to_project',array(
         _l('invoice_lowercase'),
         _l('project_lowercase'),
         '<a href="'.admin_url('projects/view/'.$invoice->project_id).'" target="_blank">' . $invoice->project_data->name . '</a>',
         )); ?></h4>
      </div>
      <?php } ?>
      <div class="col-md-6 col-sm-6">
         <h4 class="bold">
            <?php
            $tags = get_tags_in($invoice->id,'invoice');
            if(count($tags) > 0){
               echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.implode(', ',$tags).'"></i>';
            }
            ?>
            <a href="<?php echo admin_url('invoices/invoice/'.$invoice->id); ?>">
               <span id="invoice-number">
                  <?php echo format_invoice_number($invoice->id); ?>
               </span>
            </a>
         </h4>
         <address>
            <?php echo format_organization_info(); ?>
         </address>
      </div>
      <div class="col-sm-6 text-right">
         <span class="bold"><?php echo _l('invoice_bill_to'); ?>:</span>
         <address>
            <?php echo format_customer_info($invoice, 'invoice', 'billing', true); ?>
         </address>
         <?php if($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1){ ?>
         <span class="bold"><?php echo _l('ship_to'); ?>:</span>
         <address>
            <?php echo format_customer_info($invoice, 'invoice', 'shipping'); ?>
         </address>
         <?php } ?>
         <p class="no-mbot">
            <span class="bold">
               <?php echo _l('invoice_data_date'); ?>
            </span>
            <?php echo $invoice->date; ?>
         </p>
         <?php if(!empty($invoice->duedate)){ ?>
         <p class="no-mbot">
            <span class="bold">
               <?php echo _l('invoice_data_duedate'); ?>
            </span>
            <?php echo $invoice->duedate; ?>
         </p>
         <?php } ?>
         <?php if($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1){ ?>
         <p class="no-mbot">
            <span class="bold"><?php echo _l('sale_agent_string'); ?>: </span>
            <?php echo get_staff_full_name($invoice->sale_agent); ?>
         </p>
         <?php } ?>
         <?php if($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1){ ?>
         <p class="no-mbot">
            <span class="bold"><?php echo _l('project'); ?>:</span>
            <?php echo get_project_name_by_id($invoice->project_id); ?>
         </p>
         <?php } ?>
         <?php $pdf_custom_fields = get_custom_fields('invoice',array('show_on_pdf'=>1));
         foreach($pdf_custom_fields as $field){
          $value = get_custom_field_value($invoice->id,$field['id'],'invoice');
          if($value == ''){continue;} ?>
          <p class="no-mbot">
            <span class="bold"><?php echo $field['name']; ?>: </span>
            <?php echo $value; ?>
         </p>
         <?php } ?>
      </div>
   </div>
   <div class="row">
      <div class="col-md-12">
         <div class="table-responsive">
            <table class="table items invoice-items-preview">
               <thead>
                  <tr>
                     <th align="center">#</th>
                     <th class="description" width="50%" align="left"><?php echo _l('invoice_table_item_heading'); ?></th>
                     <?php
                     $custom_fields = get_items_custom_fields_for_table_html($invoice->id,'invoice');
                     foreach($custom_fields as $cf){
                      echo '<th class="custom_field" align="left">' . $cf['name'] . '</th>';
                   }
                   $qty_heading = _l('invoice_table_quantity_heading');
                   if($invoice->show_quantity_as == 2){
                      $qty_heading = _l('invoice_table_hours_heading');
                   } else if($invoice->show_quantity_as == 3){
                      $qty_heading = _l('invoice_table_quantity_heading') .'/'._l('invoice_table_hours_heading');
                   }
                   ?>
                   <th align="right"><?php echo $qty_heading; ?></th>
                   <th align="right"><?php echo _l('invoice_table_rate_heading'); ?></th>
                   <?php if(get_option('show_tax_per_item') == 1){ ?>
                   <th align="right"><?php echo _l('invoice_table_tax_heading'); ?></th>
                   <?php } ?>
                   <th align="right"><?php echo _l('invoice_table_amount_heading'); ?></th>
                </tr>
             </thead>
             <tbody>
               <?php
               $items_data = get_table_items_and_taxes($invoice->items,'invoice',true);
               $taxes = $items_data['taxes'];
               echo $items_data['html'];
               ?>
            </tbody>
         </table>
      </div>
   </div>
   <div class="col-md-5 col-md-offset-7">
      <table class="table text-right">
         <tbody>
            <tr id="subtotal">
               <td><span class="bold"><?php echo _l('invoice_subtotal'); ?></span>
               </td>
               <td class="subtotal">
                  <?php echo format_money($invoice->subtotal,$invoice->symbol); ?>
               </td>
            </tr>
            <?php if(is_sale_discount_applied($invoice)){ ?>
            <tr>
               <td>
                  <span class="bold"><?php echo _l('invoice_discount'); ?>
                     <?php if(is_sale_discount($invoice,'percent')){ ?>
                     (<?php echo _format_number($invoice->discount_percent,true); ?>%)
                     <?php } ?></span>
                  </td>
                  <td class="discount">
                     <?php echo '-' . format_money($invoice->discount_total,$invoice->symbol); ?>
                  </td>
               </tr>
               <?php } ?>
               <?php
               foreach($taxes as $tax){
                  echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('._format_number($tax['taxrate']).'%)</td><td>'.format_money($tax['total_tax'], $invoice->symbol).'</td></tr>';
               }
               ?>
               <?php if((int)$invoice->adjustment != 0){ ?>
               <tr>
                  <td>
                     <span class="bold"><?php echo _l('invoice_adjustment'); ?></span>
                  </td>
                  <td class="adjustment">
                     <?php echo format_money($invoice->adjustment,$invoice->symbol); ?>
                  </td>
               </tr>
               <?php } ?>
               <tr>
                  <td><span class="bold"><?php echo _l('invoice_total'); ?></span>
                  </td>
                  <td class="total">
                     <?php echo format_money($invoice->total,$invoice->symbol); ?>
                  </td>
               </tr>
               <?php if(count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) { ?>
               <tr>
                  <td><span class="bold"><?php echo _l('invoice_total_paid'); ?></span></td>
                  <td>
                     <?php echo '-' . format_money(sum_from_table('tblinvoicepaymentrecords',array('field'=>'amount','where'=>array('invoiceid'=>$invoice->id))),$invoice->symbol); ?>
                  </td>
               </tr>
               <?php } ?>
               <?php if(get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)){ ?>
               <tr>
                  <td><span class="bold"><?php echo _l('applied_credits'); ?></span></td>
                  <td>
                     <?php echo '-' . format_money($credits_applied,$invoice->symbol); ?>
                  </td>
               </tr>
               <?php } ?>
               <?php if(get_option('show_amount_due_on_invoice') == 1 && $invoice->status != 5) { ?>
               <tr>
                  <td><span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger ';} ?>bold"><?php echo _l('invoice_amount_due'); ?></span></td>
                  <td>
                     <span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger';} ?>">
                        <?php echo format_money($invoice->total_left_to_pay, $invoice->symbol); ?>
                     </span>
                  </td>
               </tr>
               <?php } ?>
            </tbody>
         </table>
      </div>
   </div>
   <?php if(count($invoice->attachments) > 0){ ?>
   <div class="clearfix"></div>
   <hr />
   <p class="bold text-muted"><?php echo _l('invoice_files'); ?></p>
   <?php foreach($invoice->attachments as $attachment){
      $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
      if(!empty($attachment['external'])){
        $attachment_url = $attachment['external_link'];
     }
     ?>
     <div class="mbot15 row inline-block full-width" data-attachment-id="<?php echo $attachment['id']; ?>">
      <div class="col-md-8">
         <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
         <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
         <br />
         <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
      </div>
      <div class="col-md-4 text-right">
         <?php if($attachment['visible_to_customer'] == 0){
            $icon = 'fa-toggle-off';
            $tooltip = _l('show_to_customer');
         } else {
            $icon = 'fa-toggle-on';
            $tooltip = _l('hide_from_customer');
         }
         ?>
         <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $invoice->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?>" aria-hidden="true"></i></a>
         <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
         <a href="#" class="text-danger" onclick="delete_invoice_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
         <?php } ?>
      </div>
   </div>
   <?php } ?>
   <?php } ?>
   <hr />
   <?php if($invoice->clientnote != ''){ ?>
   <div class="col-md-12 row mtop15">
      <p class="bold text-muted"><?php echo _l('invoice_note'); ?></p>
      <p><?php echo $invoice->clientnote; ?></p>
   </div>
   <?php } ?>
   <?php if($invoice->terms != ''){ ?>
   <div class="col-md-12 row mtop15">
      <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
      <p><?php echo $invoice->terms; ?></p>
   </div>
   <?php } ?>
</div>
