<div class="panel_s">
  <div class="panel-body">
    <h4 class="no-margin"><?php echo _l('clients_tickets_heading'); ?></h4>
  </div></div>
  <div class="panel_s">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-12">
          <h3 class="text-success pull-left no-mtop"><?php echo _l('tickets_summary'); ?></h3>
          <a href="<?php echo site_url('clients/open_ticket'); ?>" class="btn btn-info pull-right">
            <?php echo _l('clients_ticket_open_subject'); ?>
          </a>
          <div class="clearfix"></div>
          <hr />
        </div>
        <?php foreach($ticket_statuses as $status){ ?>
        <div class="col-md-2 list-status ticket-status">
           <a href="<?php echo site_url('clients/tickets/'.$status['ticketstatusid']); ?>" class="<?php if(in_array($status['ticketstatusid'], $list_statuses)){echo 'active';} ?>">
         <h3 class="bold ticket-status-heading">
           <?php
           $where_tickets = array('userid'=>get_client_user_id(),'status'=>$status['ticketstatusid']);
           if (!is_primary_contact() && get_option('only_show_contact_tickets') == 1) {
            $where_tickets['tbltickets.contactid'] = get_contact_user_id();
          }
          ?>
          <?php echo total_rows('tbltickets',$where_tickets); ?>
          </h3>
          <span style="color:<?php echo $status['statuscolor']; ?>"><?php echo ticket_status_translate($status['ticketstatusid']); ?></span>
           </a>
        </div>
        <?php } ?>
      </div>
      <div class="clearfix"></div>
      <hr />
      <div class="clearfix"></div>
      <?php get_template_part('tickets_table'); ?>
    </div>
  </div>
</div>
