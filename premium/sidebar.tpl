{if $more_views}
<div class="db_more_views_sidebar --card-blog">
    <span class="db_title_h4 bck_title">{l s='Más visto' mod='dbblog'}</span>
    <ul class="post_list_sidebar">
        {foreach from=$more_views item=post}
        <li>
            <a href="{$post.url}">
                <img src="{$post.img}" alt="{$post.title}">
            </a>
            <div class="title">
                <a href="{$post.url}">{$post.title}</a>
                <div class="post-meta">
                    <span class="updated"><img class="visibilidad" src="{$path_img}icons/visibilidad.svg" alt="{l s='Visualizaciones' mod='dbblog'}"> {$post.views}</span> | <a href="{$post.url_category}"">{$post.title_category}</a>
                </div>
            </div>
            </a>
        </li>
        {/foreach}
    </ul>
</div> 
{/if}

{if $last_posts}
<div class="db_more_views_sidebar --card-blog">
    <span class="db_title_h4 bck_title">{l s='Últimos artículos' mod='dbblog'}</span>
    <ul class="post_list_sidebar">
        {foreach from=$last_posts item=post}
        <li>
            <a href="{$post.url}">
                <img src="{$post.img}" alt="{$post.title}">
            </a>
            <div class="title">
                <a href="{$post.url}">{$post.title}</a>
                <div class="post-meta">
                    <span class="updated">{$post.date}</span> | <a href="{$post.url_category}"">{$post.title_category}</a>
                </div>
            </div>
            </a>
        </li>
        {/foreach}
    </ul>
</div> 
{/if}