<div class="dbblog__home">
    {if $limit_views > 0}
        <p class="title h2">{l s='Posts más vistos'}</p>
        <section class="homepsgrid post__grid">
            {foreach from=$more_views item=post}
                {include file='module:dbblog/views/templates/front/_partials/post_mini.tpl'}
            {/foreach}
        </section>
    {/if}

    {if $limit_last > 0}
        <p class="title h2">{l s='Últimos Posts'}</p>
        <section class="homepsgrid post__grid">
            {foreach from=$last_posts item=post}
                {include file='module:dbblog/views/templates/front/_partials/post_mini.tpl'}
            {/foreach}
        </section>
    {/if}
</div>