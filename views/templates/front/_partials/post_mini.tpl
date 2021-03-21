<article class="post --card-blog">
    <a href="{$post.url}">
        {if !empty($post.img)}
            <img src="{$post.img}" alt="{$post.title}">
        {/if}
        <span class="db_title_h3 name__post">{$post.title}</span>
    </a>
    <span class="db__taxonomy">
        <span class="author"><a href="{$post.author.url}">{$post.author.name}</a></span> | <span>{$post.date}</span> | <span><a href="{$post.url_category}">{$post.title_category}</a></span> | <span>{if isset($post.total_comments)}{$post.total_comments}{else}0{/if} <img class="icon_msg" src="/modules/dbblog/views/img/icons/message.svg" alt="{l s='Comentarios' mod='dbblog'}"></span>
    </span>
    {*<div class="stars">
        <div class="total">
            <div class="valoracion" style="width: {$post.rating}%"></div>
        </div>
    </div>*}
    <div class="desc_post">
        {if isset($post_extract) && $post_extract == 1}
            {$post.short_desc nofilter}
        {/if}
        {if isset($post_readmore) && $post_readmore == 1}
            <span datatext="{$post.url|base64_encode nofilter}" class="read_more datatext">{l s='Leer más'}</span>
        {/if}
    </div>
</article>