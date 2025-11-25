<?php
if (isset($all_configs['wpforms']) && class_exists( 'WPForms' ) && wpforms()->form->get($all_configs['wpforms'])) {

?>
<!-- Lead Modal Starts -->
<div class="a-modal a-modal-main asl-lead-modal fade" id="asl-lead-form-modal" role="dialog" aria-labelledby="asl-lead-modal" aria-hidden="true">
  <div class="a-modal-dialog a-modal-dialog-centered" role="document">
    <div class="a-modal-content">
      <div class="a-modal-header asl-lead-modal-head">
        <h5 class="a-modal-title" id="asl-lead-modal-title"><?php echo asl_esc_lbl('lead_form_title') ?></h5>
        <button type="button" class="asl-mdl-close" data-dismiss="modal" aria-label="<?php echo asl_esc_lbl('close') ?>">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="a-modal-body">
        <div class="sl-lead-form-title"></div>
        <div class="sl-lead-form-cont">
          <?php echo do_shortcode('[wpforms id="'.intval($all_configs['wpforms']).'"]'); ?>
        </div>
      </div>
      <div class="a-modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo asl_esc_lbl('close') ?></button>
      </div>
    </div>
  </div>
</div>
<!-- Lead Modal Ends -->
<?php
}
?>