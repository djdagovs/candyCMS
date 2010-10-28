{if $USER_RIGHT == 4}
  <p>
    <a href='/User/create'>
      <img src='%PATH_IMAGES%/spacer.gif' class="icon-create" alt='' />
      {$lang_create}
    </a>
  </p>
{/if}
<table>
  <tr>
    <th colspan='5'>{$lang_headline}</th>
  </tr>
  {foreach $user as $u}
    <tr class='{cycle values="row1,row2"}'>
      <td style='width:5%'>
        <img src='{$u.avatar_32}' width="18" height="18" alt='' />
      </td>
      <td style='width:35%' class="left">
        <a href='/User/{$u.id}/{$u.full_name_seo}'>{$u.full_name}</a>
      </td>
      <td style='width:25%' title='{$lang_registered_since}'>{$u.date}</td>
      <td style='width:25%' title='{$lang_last_login}'>{$u.last_login}</td>
      <td style='width:10%'>
        {if $USER_RIGHT == 4}
          <a href='/User/{$u.id}/update'>
            <img src='%PATH_IMAGES%/spacer.gif' class="icon-update" alt='{$lang_update}'
                 title='{$lang_update}' />
          </a>
          <img src='%PATH_IMAGES%/spacer.gif' class="icon-destroy" alt='{$lang_destroy}'
               title='{$lang_destroy}' class="pointer"
               onclick="confirmDelete('/User/{$u.id}/destroy')" />
        {/if}
      </td>
    </tr>
  {/foreach}
</table>