{if $USER_ROLE >= 3}
  <p class="center">
    <a href='/download/create'>
      <img src='%PATH_IMAGES%/spacer.png' class="icon-create" alt='' width="16" height="16" />
      {$lang.global.create.entry}
    </a>
  </p>
{/if}
<h1>{$lang.global.downloads}</h1>
{if !$download}
  <div class='error' title='{$lang.error.missing.entries}'>
    <h4>{$lang.error.missing.entries}</h4>
  </div>
{else}
  {foreach $download as $d}
    <h2>{$d.category}</h2>
    <table class="sortTable tablesorter zebra-striped">
      <thead>
        <tr>
          <th width='10%'></th>
          <th width='40%' class="headerSortDown">{$lang.global.title}</th>
          <th width='20%'>{$lang.global.date.date}</th>
          <th width='20%'>{$lang.global.size}</th>
          <th width='10%'></th>
        </tr>
      </thead>
      <tbody>
      {foreach $d.files as $f}
        <tr class='{cycle values="row1,row2"}'>
          <td>
            <img src='%PATH_IMAGES%/files/{$f.extension}.png'
                 width='32' height='32' alt='{$f.extension}' />
          </td>
          <td class="left">
            <a href="{$f.url}" target="_blank">{$f.title}</a>
            {if $f.content !== ''}
              <br />
              {$f.content}
            {/if}
          </td>
          <td>{$f.date}</td>
          <td>
            {$f.size}
            {if $USER_ROLE >= 3}
              <br />
              {$f.downloads} {$lang.global.downloads}
            {/if}
          </td>
          <td>
            {if $USER_ROLE >= 3}
              <a href='/download/{$f.id}/update'>
                <img src='%PATH_IMAGES%/spacer.png' class="icon-update" alt='{$lang.global.update.update}'
                  title='{$lang.global.update.update}' width="16" height="16" />
              </a>
              <a href="#" onclick="candy.system.confirmDestroy('/download/{$f.id}/destroy')">
                <img src='%PATH_IMAGES%/spacer.png' class="icon-destroy pointer" alt='{$lang.global.destroy.destroy}'
                  title='{$lang.global.destroy.destroy}' width="16" height="16" />
              </a>
            {else}
              <a href="{$f.url}" target="_blank">
                <img src='%PATH_IMAGES%/spacer.png' class="icon-download" alt='{$lang.global.download}'
                  title='{$lang.global.download}' width="32" height="32" />
              </a>
            {/if}
          </td>
        </tr>
      {/foreach}
      </tbody>
    </table>
  {/foreach}
{/if}