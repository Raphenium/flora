<div class="col-md-12 page-pdf-html-logo">
   <?php get_company_logo('','pull-left'); ?>
   <?php if(is_client_logged_in() && is_primary_contact() && get_option('show_subscriptions_in_customers_area') == '1'){ ?>
     <a href="<?php echo site_url('clients/subscriptions/'); ?>" class="btn btn-default pull-right">
      <?php echo _l('client_go_to_dashboard'); ?>
   </a>
   <?php } ?>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
   <div class="panel-body">
      <div class="col-md-10 col-md-offset-1">
         <div class="row">
            <div class="col-md-12 text-right _buttons">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <?php
               // Already subscribed
               if(empty($subscription->stripe_subscription_id) && !$stripe_customer
                  || empty($subscription->stripe_subscription_id) && empty($stripe_customer->default_source)){
                  echo form_open(site_url('subscription/subscribe/'.$hash)).'
               <script
               src="https://checkout.stripe.com/checkout.js" class="stripe-button"
               data-key="'.$this->stripe_subscriptions->get_publishable_key().'"
               data-amount="'.$total.'"
               data-name="' . $subscription->name . '"
               data-label="'._l('subscribe').'"
               data-billing-address="true"
               data-description="'.$subscription->description.'"
               data-currency="'.$plan->currency.'"
               data-locale="auto">
               </script>
               </form>';
            } else if(empty($subscription->stripe_subscription_id) && $stripe_customer && !empty($stripe_customer->default_source)) {
               echo form_open(site_url('subscription/subscribe/'.$hash));
               echo '<button type="submit" name="source_subscribe" value="true" class="btn btn-success">';
               echo _l('subscribe') . ' ('.$stripe_customer->default_source->brand . ' ' . $stripe_customer->default_source->last4.')';
               echo '</button>';
               echo form_close();
            }
            ?>
         </div>
      </div>
      <div class="row mtop40">
         <div class="col-md-6 col-sm-6">
            <address>
               <?php echo format_organization_info(); ?>
            </address>
         </div>
         <div class="col-sm-6 text-right">
            <span class="bold"><?php echo _l('invoice_bill_to'); ?>:</span>
            <address>
               <?php echo format_customer_info($invoice, 'invoice', 'billing'); ?>
            </address>
            <!-- shipping details -->
            <?php if(isset($invoice->include_shipping) && $invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1){ ?>
               <span class="bold"><?php echo _l('ship_to'); ?>:</span>
               <address>
                  <?php echo format_customer_info($invoice, 'invoice', 'shipping'); ?>
               </address>
               <?php } ?>
                     <p class="no-mbot subscription-number">
                 <span class="bold">
                <?php echo _l('subscription'); ?> #:
               </span>
               <?php
               echo $subscription->id;
               ?>
            </p>
               <p class="no-mbot subscription-date">
                 <span class="bold">
                  <?php echo _l('subscription_date'); ?>:
               </span>
               <?php
               echo !empty($subscription->stripe_subscription_id) ? _d(date('Y-m-d', strtotime($subscription->date_subscribed))) : _d(date('Y-m-d'));
               ?>
            </p>
            <?php if(!empty($subscription->date)) { ?>
              <p class="no-mbot subscription-first-billing-date">
                 <span class="bold">
                  <?php echo _l('first_billing_date'); ?>:
               </span>
               <?php if(!empty($subscription->stripe_subscription_id)) {
                  echo _d($subscription->date);
               } else {
                 if($subscription->date <= date('Y-m-d')) {
                  echo _d(date('Y-m-d'));
               } else {
                  echo _d($subscription->date);
               }
            }
            ?>
         </p>
         <?php } ?>
         <?php if($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1){ ?>
            <p class="no-mbot subscription-project">
               <span class="bold"><?php echo _l('project'); ?>:</span>
               <?php echo get_project_name_by_id($invoice->project_id); ?>
            </p>
            <?php } ?>
         </div>
      </div>
      <div class="row">
         <div class="col-md-12">
            <div class="table-responsive">
               <table class="table items">
                  <thead>
                     <tr>
                        <th align="center">#</th>
                        <th class="description" width="50%" align="left"><?php echo _l('invoice_table_item_heading'); ?></th>
                        <th align="right"><?php echo _l('invoice_table_quantity_heading'); ?></th>
                        <th align="right"><?php echo _l('invoice_table_rate_heading'); ?></th>
                        <?php if(get_option('show_tax_per_item') == 1){ ?>
                           <th align="right"><?php echo _l('invoice_table_tax_heading'); ?></th>
                           <?php } ?>
                           <th align="right"><?php echo _l('invoice_table_amount_heading'); ?></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                        $items_data = get_table_items_and_taxes($invoice->items,'invoice');
                        $taxes = $items_data['taxes'];
                        echo $items_data['html'];
                        ?>
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="col-md-6 col-md-offset-6">
               <table class="table text-right">
                  <tbody>
                     <tr id="subtotal">
                        <td><span class="bold"><?php echo _l('invoice_subtotal'); ?></span>
                        </td>
                        <td class="subtotal">
                           <?php echo format_money($invoice->subtotal,$invoice->symbol); ?>
                        </td>
                     </tr>
                     <?php
                     foreach($taxes as $tax){
                      echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('._format_number($tax['taxrate']).'%)</td><td>'.format_money($tax['total_tax'], $invoice->symbol).'</td></tr>';
                   }
                   ?>
                   <tr>
                     <td><span class="bold"><?php echo _l('invoice_total'); ?></span>
                     </td>
                     <td class="total">
                        <?php echo format_money($invoice->total, $invoice->symbol); ?>
                     </td>
                  </tr>
                  <?php if(get_option('show_amount_due_on_invoice') == 1 && $invoice->status != 5 && empty($subscription->stripe_subscription_id)) { ?>
                     <tr>
                        <td><span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger ';} ?>bold"><?php echo _l('invoice_amount_due'); ?></span></td>
                        <td>
                           <span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger';} ?>">
                              <?php echo format_money($invoice->total_left_to_pay,$invoice->symbol); ?>
                           </span>
                        </td>
                     </tr>
                     <?php } ?>
                  </tbody>
               </table>
            </div>
            <?php if(!empty($invoice->clientnote)){ ?>
               <div class="col-md-12">
                  <b><?php echo _l('invoice_note'); ?></b><br /><br /><?php echo $invoice->clientnote; ?>
               </div>
               <?php } ?>
               <?php if(!empty($invoice->terms)){ ?>
                  <div class="col-md-12">
                     <hr />
                     <b><?php echo _l('terms_and_conditions'); ?></b><br /><br /><?php echo $invoice->terms; ?>
                  </div>
                  <?php } ?>
                  <?php if(count($child_invoices) > 0) { ?>
                     <div class="col-md-12 subscription-child-invoices">
                       <hr />
                       <h4><?php echo _l('invoices'); ?></h4>
                       <table class="table">
                          <thead>
                             <tr>
                                <th><?php echo _l('invoice_add_edit_number'); ?></th>
                                <th><?php echo _l('invoice_dt_table_heading_date'); ?></th>
                                <th><?php echo _l('invoice_total'); ?></th>
                             </tr>
                          </thead>
                          <tbody>
                             <?php foreach($child_invoices as $child_invoice) { ?>
                              <tr>
                                 <td><a href="<?php echo site_url('invoice/'.$child_invoice->id.'/'.$child_invoice->hash); ?>" target="_blank"><?php echo format_invoice_number($child_invoice->id); ?></a></td>
                                 <td><?php echo _d($child_invoice->date); ?></td>
                                 <td><?php echo format_money($child_invoice->total, $child_invoice->symbol); ?></td>
                              </tr>
                              <?php } ?>
                          </tbody>
                       </table>
                    </div>
                    <?php } ?>
                 </div>
              </div>
           </div>
        </div>
