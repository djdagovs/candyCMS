<table>
  <tr>
    <th colspan='3'>
      <h1>
        {if $USER_RIGHT == 4}
          <a href='/user/{$uid}/update'>
            <img src="%PATH_IMAGES%/spacer.png" class="icon-update" alt='{$lang_update}' style='vertical-align:baseline' />
          </a>
        {/if}
        {$user.name} {$user.surname}
      </h1>
    </th>
  </tr>
  <tr class='row1'>
    <td class='td_left'>
      {$lang_registered_since}
    </td>
    <td class='td_right'>
      {$date}
    </td>
    <td rowspan='4' style='vertical-align:top'>
      <a href='{$avatar_popup}' rel='lightbox' title='{$user.name} {$user.surname}'>
        <img class='image' alt='{$user.name}' src="{$avatar_100}" />
      </a>
    </td>
  </tr>
  <tr class='row2'>
    <td class='td_left'>
      {$lang_last_login}
    </td>
    <td class='td_right'>
      {$last_login}
    </td>
  </tr>
  <tr class='row1'>
    <td class='td_left'>
      {$lang_about_himself}
    </td>
    <td class='td_right'>
      {$user.description}
    </td>
  </tr>
  <tr class='row2'>
    <td class='td_left'>
      {$lang_contact}
    </td>
    <td class='td_right'>
      <a href='/Mail/{$uid}'>{$lang_contact_via_mail}</a>
    </td>
  </tr>
</table>
<script type="text/javascript">
    window.addEvent('domready', function() {
      new Asset.javascript('%PATH_PUBLIC%/js/core/slimbox{$_compress_files_suffix_}.js');
    });
</script>