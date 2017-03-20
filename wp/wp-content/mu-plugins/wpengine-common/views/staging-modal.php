<!-- Modal -->
<div id="stagingModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <h3><span class="icon wpe-icon  icon-warning-sign"></span>Warning:</h3>
  </div>
  <div class="modal-body"><p style="font-size:1.2em;">Copying to staging will overwrite any changes made recently to your staging site. Are you sure you want to do this?
You can backup or restore your staging area in the <?php printf('<a href="https://my.wpengine.com/installs/%s/backup_points?environment=staging" target="_blank">User Portal</a>', PWP_NAME); ?></p></div>
  <div class="modal-footer">
    <button id="dismiss" class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
    <button id="staging-submit" class="btn btn-primary" data-confirm="true" aria-hidden="false" >Deploy to Staging</button>
  </div>
</div>
