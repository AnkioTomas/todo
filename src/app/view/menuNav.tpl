{function name="renderMenu" items=[] pathPrefix=''}
    {foreach $items as $idx => $node}

        {if $pathPrefix == ''}
            {$currentPath = $idx}
        {else}
            {$currentPath = "$pathPrefix-$idx"}
        {/if}

        {if !empty($node.sub)}
            <mdui-collapse>
                <mdui-collapse-item value="item-{$currentPath}">
                    <mdui-list-item slot="header" rounded icon="{$node.icon}">
                        <span>{$node.title}</span>
                        <mdui-icon slot="end-icon" name="keyboard_arrow_left"></mdui-icon>
                    </mdui-list-item>
                    <div style="margin-left: 2.5rem">
                        {call name="renderMenu" items=$node.sub pathPrefix=$currentPath}
                    </div>
                </mdui-collapse-item>
            </mdui-collapse>
        {else}
            <mdui-list-item
                    rounded
                    icon="{$node.icon}"
                    data-link="{isset($node.url) ? $node.url : '' }"
                    data-pjax="{isset($node.pjax) ? 'true' : 'false'}"
                    data-match="{if isset($node.match)}{$node.match}{/if}"
                    {if isset($node.list_id)}data-list-id="{$node.list_id}"{/if}
                    {if isset($node.is_default)}data-is-default="{$node.is_default}"{/if}
                    class="{if isset($node.list_id)}todo-list-item{/if}">
                {$node.title}
            </mdui-list-item>
        {/if}
    {/foreach}
{/function}

{call name="renderMenu" items=$menuConfig}
