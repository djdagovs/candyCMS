{if $USER_ROLE == 4}
  <p class="center">
    <a href='/user/create'>
      <img src='%PATH_IMAGES%/spacer.png' class="icon-create" alt='' width="16" height="16" />
      {$lang.user.title.create}
    </a>
  </p>
{/if}
<h1>{$lang.user.title.overview}</h1>
<table class="sortTable tablesorter zebra-striped"> 
  <thead>
    <tr>
      <th class="headerSortDown">#</th>
      <th></th>
      <th>{$lang.global.name}</th>
      <th>{$lang.user.label.registered_since}</th>
      <th>{$lang.user.label.last_login}</th>
      <th>{$lang.global.newsletter}</th>
      <th></th>
    </tr>
  </thead>
  {foreach $user as $u}
    <tr>
      <td style="widht:5%">{$u.id}</td>
      <td style='width:5%'>
        <img src='{$u.avatar_32}' width="20" height="20" alt='' />
      </td>
      <td style='width:30%' class="left">
        <a href='/user/{$u.id}/{$u.encoded_full_name}'>{$u.full_name}</a>
        <br />
        {if $u.role == 1}
          ({$lang.global.user.roles.1})
        {elseif $u.role == 2}
          ({$lang.global.user.roles.2})
        {elseif $u.role == 3}
          ({$lang.global.user.roles.3})
        {elseif $u.role == 4}
          ({$lang.global.user.roles.4})
        {/if}
      </td>
      <td style='width:20%'>
        {if $u.verification_code !== ''}
          <span style="text-decoration:line-through">{$u.date}</span>
        {else}
          {$u.date}
        {/if}
      </td>
      <td style='width:20%'>
        {$u.last_login}
      </td>
      <td style='width:10%'>
        <img src='%PATH_IMAGES%/spacer.png'
             class="icon-{if $u.receive_newsletter == 1}success{else}close{/if}"
             alt='{$u.receive_newsletter}' title="" width="16" height="16" />
      </td>
      <td style='width:10%'>
        {if $USER_ROLE == 4}
          <a href='/user/{$u.id}/update'>
            <img src='%PATH_IMAGES%/spacer.png' class="icon-update" alt='{$lang.global.update.update}'
                 title='{$lang.global.update.update}' width="16" height="16" />
          </a>
          <a href="#" onclick="candy.system.confirmDestroy('/user/{$u.id}/destroy')">
            <img src='%PATH_IMAGES%/spacer.png' class="icon-destroy" alt='{$lang.global.destroy.destroy}'
                 title='{$lang.global.destroy.destroy}' width="16" height="16"  />
          </a>
        {/if}
      </td>
    </tr>
  {/foreach}
</table>