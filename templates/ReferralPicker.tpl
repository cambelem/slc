<div class="form-group">
    <label for="referral_picker" class="col-sm-4 control-label">Referred By:</label>
    <div class="col-sm-8">
      <select class="form-control" id="referral_picker">
        <option value="-1">[ choose referral type ]</option>
        <!-- BEGIN referrals -->
        <option value="{REFERRAL_ID}">{NAME}</option>
        <!-- END referrals -->
      </select>
    </div>
</div>
