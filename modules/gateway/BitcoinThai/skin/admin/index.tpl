<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="Authorize" class="tab_content">
  		<h3>{$TITLE}</h3>
  		<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
			<div><label for="position">{$LANG.module.position}</label><span><input type="text" name="module[position]" id="position" class="textbox number" value="{$MODULE.position}" /></span></div>
			<div>
				<label for="scope">{$LANG.module.scope}</label>
				<span>
					<select name="module[scope]">
      						<option value="both" {$SELECT_scope_both}>{$LANG.module.both}</option>
      						<option value="main" {$SELECT_scope_main}>{$LANG.module.main}</option>
      						<option value="mobile" {$SELECT_scope_mobile}>{$LANG.module.mobile}</option>
    					</select>
				</span>
			</div>
			<div><label for="default">{$LANG.common.default}</label><span><input type="hidden" name="module[default]" id="default" class="toggle" value="{$MODULE.default}" /></span></div>
			<div><label for="description">{$LANG.common.description}</label><span><input name="module[desc]" id="desc" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>
            <div>{$LANG.bitcointhai.api_tip}</div>
			<div><label for="txnkey">{$LANG.bitcointhai.api_id}</label><span><input name="module[api_id]" id="api_id" class="textbox" type="text" value="{$MODULE.api_id}" /></span></div>
			<div><label for="txnkey">{$LANG.bitcointhai.api_key}</label><span><input name="module[api_key]" id="api_key" class="textbox" type="text" value="{$MODULE.api_key}" /></span></div>
			<div>
				<label for="debugging">{$LANG.bitcointhai.debugging}</label>
					<span>
						<select name="module[debugging]" id="debugging">
        					<option value="0" {$SELECT_debugging_0}>{$LANG.bitcointhai.debugging_off}</option>
        					<option value="1" {$SELECT_debugging_1}>{$LANG.bitcointhai.debugging_on}</option>
    					</select>
    				</span>
    			</div>
    		</fieldset>
  		</div>

  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>