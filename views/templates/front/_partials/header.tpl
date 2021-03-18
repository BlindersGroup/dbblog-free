{block name='notifications'}
    <div class="header__blog">
		<div class="container">
            <div class="row">
                <div class="col-8 col-xs-8 col-md-3">
                    {if $isHome == 0}
                        <a href="{$url_home}"><h1 class="name">{$title_blog}</h1></a>
                    {else}
                        <h1 class="name">{$title_blog}</h1>
                    {/if}
                </div>
                <div class="col-4 col-xs-4 col-md-9 menu">

                    <ul class="list_category">
                        <div class="header_menub hide_desktop">
                            <span class="title">{$title_blog}</span>
                            <span class="close_menub"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" enable-background="new 0 0 24 24" class="c-icon-svg c-icon-svg--big"><path d="M20.7 4.7l-1.4-1.4-7.3 7.3-7.3-7.3-1.4 1.4 7.3 7.3-7.3 7.3 1.4 1.4 7.3-7.3 7.3 7.3 1.4-1.4-7.3-7.3z"></path></svg></span>
                        </div>
                        {$cat = 1}
                        {foreach from=$categories item=category key=key}
                            <li><a href="{$category.url}" title="{$category.title}">{$category.title}</a></li>
                            {if $cat > 4}
                                {break}
                            {else}
                                {$cat = $cat+1}
                            {/if}
                        {/foreach}
                        {if $categories|count > 4}
                            <li>
                                <div class="dropdown">
                                    <button class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {l s="Ver más" mod="dbblog"}
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        {$cat = 1}
                                        {foreach from=$categories item=category key=key}
                                            {if $cat > 5}
                                                <a class="dropdown-item" href="{$category.url}" title="{$category.title}">{$category.title}</a>
                                            {else}
                                                {$cat = $cat+1}
                                            {/if}
                                        {/foreach}    
                                    </div>                                                                                                
                                </div>
                            </li>
                        {/if}
                    </ul>

                    <button class="hide_desktop dbblog_menu_mobile"><img src="{$path_img}icons/lista.svg"></button>
                    <div class="menub__overlay"></div>
                </div>
            </div>
        </div>
    </div>

    {if $isHome == 1 && $destacados|count > 0}
        <div class="header__category">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-8 destacado_first">

                        <div class="db__posts">
                            {foreach from=$destacados item=post name=dest}
                                {if $smarty.foreach.dest.first}
                                    <article class="post --card-blog first_destacado">
                                        <a href="{$post.url}">
                                            <img src="{$path_img_posts}{$post.image}" alt="{$post.title}">
                                            <p class="db_title_h3 name__post">{$post.title}</p>
                                        </a>
                                        <span class="db__taxonomy">
                                            <span class="author">{l s='Por' mod='dbblog'} <a href="{$post.author.url}">{$post.author.name}</a></span> |
                                            <span class="category"><a href="{$post.url_category}">{$post.title_category}</a></span>
                                        </span>
                                    </article>
                                {/if}
                            {/foreach}
                        </div>

                    </div>
                    <div class="col-sm-12 col-md-4 destacado_list">

                        <ul class="post_list_more_destacado">
                            {foreach from=$destacados item=post name=dest}
                                {if !$smarty.foreach.dest.first}
                                    <li class="--card-blog more_destacado">
                                        <a href="{$post.url}">
                                            <img src="{$path_img_posts}{$post.image}" alt="{$post.title}">
                                        </a>
                                        <div class="title">
                                            <a href="{$post.url}">{$post.title}</a>
                                            <span class="author">{l s='Por' mod='dbblog'} <a href="{$post.author.url}">{$post.author.name}</a></span>
                                        </div>

                                    </li>
                                {/if}
                            {/foreach}
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    {/if}

    {if $isCategory == 1}
        <div class="header__category">
            <div class="container">
                <h1 class="name">{$category.title}</h1>
                <div class="description">{$category.short_desc nofilter}</div>
            </div>
        </div>
    {/if}

    {if $isAuthors == 1}
        <div class="header__category">
            <div class="container">
                <h1 class="name">{l s='Nuestro equipo'}</h1>
                <div class="description"><p>Nam nec tellus a odio tincidunt auctor a ornare odio. Sed non mauris vitae erat consequat auctor eu in elit. Class aptent taciti sociosqu ad litora torquent per conubia nostra.</p></div>
            </div>
        </div>
    {/if}

    {if $isAuthor == 1}
        <div class="header__author">
            <div class="container">
                <div class="author_image">
                    <img src="{$author.imagen}">
                </div>
                <div class="author_info">
                    <h1 class="name">{$author.name}</h1>
                    <p class="profession">{$author.profession}</p>
                    <p class="description">{$author.description}</p>
                    <div class="more_info">{l s='Nº de artículos:' mod='dbblog'} {$count_posts} | {l s='Valoración media:' mod='dbblog'} {$avg_cm}
                         <div class="stars">
                            <div class="total">
                                <div class="valoracion" style="width: {$avg_cmp}%"></div>
                            </div>
                        </div>
                    </div>
                    <ul class="redes">
                        {if !empty($author.twitter)}
                            <li><a href="#" target="_blank"><img src="/modules/dbblog/views/img/icons/twitter.svg" alt="twitter"></a></li>
                        {/if}
                        {if !empty($author.facebook)}
                            <li><a href="#" target="_blank"><img src="/modules/dbblog/views/img/icons/facebook.svg" alt="facebook"></a></li>
                        {/if}
                        {if !empty($author.linkedin)}
                            <li><a href="#" target="_blank"><img src="/modules/dbblog/views/img/icons/linkedin.svg" alt="linkedin"></a></li>
                        {/if}
                        {if !empty($author.youtube)}
                            <li><a href="#" target="_blank"><img src="/modules/dbblog/views/img/icons/youtube.svg" alt="youtube"></a></li>
                        {/if}
                        {if !empty($author.instagram)}
                            <li><a href="#" target="_blank"><img src="/modules/dbblog/views/img/icons/instagram.svg" alt="instagram"></a></li>
                        {/if}
                    </ul>
                </div>
            </div>
        </div>
    {/if}

    {if $isPost == 1}
        <div class="header__category">
            <div class="container">
                <h1 class="name">{$post.title}</h1>
                <div class="description">{$post.short_desc nofilter}</div>  
            </div>
        </div>
    {/if}
{/block}