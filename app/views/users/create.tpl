<div class='page-header'>
  <h1>
    {$lang.global.registration}
    {if !$USER_FACEBOOK_ID && !$USER_NAME && $_facebook_plugin_ == true}
      <fb:login-button scope="email" onlogin="window.location='{$CURRENT_URL}"></fb:login-button>
    {/if}
  </h1>
</div>
<form method='post' class='form-horizontal'>
  <div class="control-group {if isset($error.name)}error{/if}">
    <label for='input-name' class='control-label'>
      {$lang.global.name} <span title="{$lang.global.required}">*</span>
    </label>
    <div class='controls'>
      <input class='focused span4' name='name' value='{$name}' type='name' id="input-name" autofocus required />
      {if isset($error.name)}<span class="help-inline">{$error.name}</span>{/if}
    </div>
  </div>
  <div class="control-group{if isset($error.surname)} error{/if}">
    <label for='input-surname' class='control-label'>
      {$lang.global.surname}</span>
    </label>
    <div class="controls">
      <input class='span4' name='surname' value='{$surname}' id="input-surname" type='text' />
    </div>
  </div>
  <div class="control-group{if isset($error.email)} error{/if}">
    <label for='input-email' class='control-label'>
      {$lang.global.email.email} <span title="{$lang.global.required}">*</span>
    </label>
    <div class="controls">
      <input class='span4' name='email' value='{$email}' type='email' id="input-email" required />
      {if isset($error.email)}<span class="help-inline">{$error.email}</span>{/if}
    </div>
  </div>
  <div class="control-group{if isset($error.password)} error{/if}">
    <label for='input-password' class='control-label'>
      {$lang.global.password.password} <span title="{$lang.global.required}">*</span>
    </label>
    <div class="controls">
      <input class='span4' name='password' type='password' id="input-password" required />
      {if isset($error.password)}<span class="help-inline">{$error.password}</span>{/if}
    </div>
  </div>
  <div class="control-group" id='js-password'>
    <label for='input-password2' class='control-label'>
      {$lang.global.password.repeat} <span title="{$lang.global.required}">*</span>
    </label>
    <div class="controls">
      <input class='span4' name='password2' type='password' id="input-password2" required />
    </div>
  </div>
  {if $USER_ROLE < 4}
    <div id='js-modal' class='modal hide fade'>
      <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h3>{$lang.global.terms.terms}</h3>
      </div>
      <div class="modal-body">
        <p>{$lang.user.info.terms}</p>
      </div>
    </div>
    <div class="control-group{if isset($error.disclaimer)} error{/if}">
      <label for='input-terms'>{$lang.global.terms.terms} <span title="{$lang.global.required}">*</span></label>
        {* Absolute URL due to fancybox bug *}
        <div class='controls'>
          <label class='checkbox'>
            <input name='disclaimer' value='' type='checkbox' id='input-terms' required />
            <a href='#js-modal' data-toggle="modal">
              {$lang.global.terms.read}
            </a>
          </label>
        </div>
    </div>
  {/if}
  <div class="form-actions">
    <input type='submit' class="btn btn-primary" value='{$lang.global.register}' />
    <input type='reset' class='btn' />
    <input type='hidden' value='formdata' name='create_user' />
  </div>
</form>

<script type='text/javascript' src='%PATH_JS%/core/jquery.bootstrap.modal{$_compress_files_suffix_}.js'></script>
<script type='text/javascript' src='%PATH_JS%/core/jquery.fancybox{$_compress_files_suffix_}.js'></script>
<script type="text/javascript">
  $(document).ready(function(){
    $("input[name='password2']").keyup(function(){
      if ($("input[name='password']").val() == $("input[name='password2']").val()) {
        $('#js-password').removeClass('error');
      } else {
        $('#js-password').addClass('error');
      }
    });
  });
</script>