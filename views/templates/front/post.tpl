{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{extends file='page.tpl'}

{block name='head_seo'}
    <title>
        {block name='head_seo_title'}
            {if !empty($post.meta_title)}
                {$post.meta_title} - {$title_blog}
            {else}
                {$post.title} - {$title_blog}
            {/if}
        {/block}
    </title>
    <meta rel="description" content="{$post.meta_description}">
    {if $post.index == 0}
        <meta name="robots" content="noindex,follow">
    {/if}
    <link rel="canonical" href="{$post.url}">

    <meta property="og:url" content="{$url_post}" />
    <meta property="og:title" content="{$post.title}" />
    <meta property="og:description" content="{$post.short_desc|strip_tags}" />
    <meta property="og:image" content="{_PS_BASE_URL_}{$post.img}" />
{/block}

{block name='hook_extra'}
    <script type="application/ld+json">{ 
        "@context" : "http://schema.org",  
        "@type" : "BlogPosting",
        "url" : "{$post.url}",
        "mainEntityOfPage": "{$post.short_desc|strip_tags}",
        "articleSection": "{$post.title_category}",
        "wordCount": {$post.large_desc|count_words},
        "headline": "{$post.title}",
        "alternativeHeadline": "{$post.meta_title} - {$title_blog}",
        "commentCount": {$post.comments.total},
        "creator": "{$post.author.name}",
        "dateCreated": "{$post.date_add_json}",
        {if $premium == 1}
            "dateModified": "{$post.date_upd_json}",
        {/if}
        "datePublished": "{$post.date_add_json}",
        "inLanguage": "es", 
        "image": "{$baseurl}{$post.img}",
        "thumbnailUrl": "{$baseurl}{$post.img}",
        "comment": [
            {foreach from=$comments item=comment name=foo}
            {
                "@type": "comment",
                "author": {
                    "@type": "Person",
                    "name": "{$comment.name}" 
                },
                "datePublished": "{$comment.date_add}",
                "comment": "{$comment.comment}"
            }{if !$smarty.foreach.foo.last || isset($comment.children)},{/if}
            {if isset($comment.children)}
                {foreach from=$comment.children item=comment name=foo2}
                {
                    "@type": "comment",
                    "author": {
                        "@type": "Person",
                        "name": "{$comment.name}" 
                    },
                    "datePublished": "{$comment.date_add}",
                    "comment": "{$comment.comment}"
                }{if !$smarty.foreach.foo.last},{/if}
                {/foreach}
            {/if}
            {/foreach}
        ],
        
        "author": {
            "@type" : "Person",
            "name": "{$post.author.name}"
        }, 
        "publisher": {
            "@type": "Organization",
            "name": "{Configuration::get('PS_SHOP_NAME')}",
            "logo": {
                "@type" : "ImageObject",
                "url": "{$baseurl}{$logo_shop}"
            }        
        }    
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {foreach from=$breadcrumb.links item=path name=breadcrumb}
            {
                "@type": "ListItem",
                "position": {$smarty.foreach.breadcrumb.iteration},
                "name": "{$path.title}",
                "item": "{$path.url}"
            }{if not $smarty.foreach.breadcrumb.last},{/if}
            {/foreach}
        ]
    }
    </script>
{/block}

{include file='module:dbblog/views/templates/front/_partials/header.tpl'}
{include file='module:dbblog/views/templates/front/_partials/breadcrumb.tpl'}

{block name="content_wrapper"}
    <div class="row">
        <div id="content-wrapper" class="center-column col-sm-4 col-md-9">
            <section class="section_post --card-blog">
                <div class="info_post">
                    {if !empty($post.author.imagen)}
                        <div class="img_author">
                            <img src="{$post.author.imagen}" alt="{$post.author.name}">
                        </div>
                    {/if}
                    <div class="more_info">
                        <div class="info_up">
                            <div class="info_author">
                                <a href="{$post.author.url}">{$post.author.name}</a> | <span class="profession">{$post.author.profession}</span>
                            </div>
                            <span class="date_publish hide_mobile">{$post.date_add} {if $premium == 1}| {l s='Actualizado:' mod='dbblog'} {$post.date_upd}{/if}</span>
                            <span class="date_publish hide_desktop">{$post.date_add}</span>
                        </div>
                        <div class="info_down">
                            {if $c_active == true}
                                <div class="comentarios">
                                    {*<div class="stars">
                                        <div class="total">
                                            <div class="valoracion" style="width: {$post.rating}%"></div>
                                        </div>
                                    </div>*}
                                    <div class="valoracion_media">
                                        <div class="Stars" style="--rating: {$post.avg_rating|escape:'htmlall':'UTF-8'};"></div>
                                    </div>
                                    <span>{$post.avg_rating}</span>&nbsp;|&nbsp;<span class="valoraciones hide_desktop">{$post.comments.total} <img class="icon_msg" src="{$path_img}icons/message.svg"></span><span class="valoraciones hide_mobile">{$post.comments.total} {l s='valoraciones' mod='dbblog'}</span>
                                </div>
                            {/if}
                            <span class="views"><img class="visibilidad" src="{$path_img}icons/visibilidad.svg"> {$post.views}</span>
                        </div>

                    </div>
                </div>
                {if !empty($post.img)}
                    <img src="{$post.img}" alt="{$post.title}">
                {/if}
                <div class="post_share">
                    <span>{l s='Compartir' mod='dbblog'}:</span>
                    <span class="share_link datatext" datatext="{$share_facebook|base64_encode}"><img src="{$path_img|escape:'htmlall':'UTF-8'}../../../dbaboutus/views/img/icons/facebook.png"></span>
                    <span class="share_link datatext" datatext="{$share_twitter|base64_encode}"><img src="{$path_img|escape:'htmlall':'UTF-8'}../../../dbaboutus/views/img/icons/twitter.png"></span>
                    {*<a href="https://www.facebook.com/sharer.php?u={$url_post}" class="share_link" target="_blank"><img src="{$path_img|escape:'htmlall':'UTF-8'}../../../dbaboutus/views/img/icons/facebook.png"></a>
                    <a href="https://twitter.com/intent/tweet?text={$post.title}&url={$url_post}" class="share_link" target="_blank"><img src="{$path_img|escape:'htmlall':'UTF-8'}../../../dbaboutus/views/img/icons/twitter.png"></a>*}
                </div>
                {*<div class="large_desc">{$post.large_desc nofilter}</div>*}
                <div class="large_desc">{$desc nofilter}</div>
            </section>

            {if $more_views_post|count != 0 && $more_posts_author|count != 0}
            <section class="posts_recommended --card-blog">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    {if $more_views_post|count > 0}
                    <li class="nav-item">
                        <span class="nav-link active" id="home-tab" data-toggle="tab" href="#related_posts" role="tab" aria-controls="home"
                        aria-selected="true">{l s='Posts relacionados' mod='dbblog'}</span>
                    </li>
                    {/if}
                    {if $more_posts_author|count > 0}
                    <li class="nav-item">
                        <span class="nav-link" id="profile-tab" data-toggle="tab" href="#author_posts" role="tab" aria-controls="profile"
                        aria-selected="false">{l s='Más del autor' mod='dbblog'}</span>
                    </li>
                    {/if}
                </ul>
                <div class="tab-content" id="myTabContent">
                    {if $more_views_post|count > 0}
                    <div class="tab-pane fade {if $more_views_post|count > 0}active in show{/if}" id="related_posts" role="tabpanel" aria-labelledby="home-tab">
                        <ul class="post_list_sidebar">
                            {foreach from=$more_views_post item=post}
                            <li>
                                <a href="{$post.url}">
                                    <img src="{$post.img}" alt="{$post.title}">
                                </a>
                                <div class="title">
                                    <a href="{$post.url}">{$post.title}</a>
                                    <div class="post-meta">
                                        <span class="updated"><img class="visibilidad" src="/modules/dbblog/views/img/icons/visibilidad.svg" alt="Visualizaciones"> {$post.views}</span> | <span class="updated">{$post.author.name}</span>
                                    </div>
                                </div>
                            </li>
                            {/foreach}
                        </ul>
                    </div>
                    {/if}
                    {if $more_posts_author|count > 0}
                    <div class="tab-pane fade {if $more_views_post|count == 0}active in{/if}" id="author_posts" role="tabpanel" aria-labelledby="profile-tab">
                        <ul class="post_list_sidebar">
                            {foreach from=$more_posts_author item=post}
                            <li>
                                <a href="{$post.url}">
                                    <img src="{$post.img}" alt="{$post.title}">
                                </a>
                                <div class="title">
                                    <a href="{$post.url}">{$post.title}</a>
                                    <div class="post-meta">
                                        <span class="updated"><img class="visibilidad" src="/modules/dbblog/views/img/icons/visibilidad.svg" alt="Visualizaciones"> {$post.views}</span> | <span class="updated">{$post.title_category}</span>
                                    </div>
                                </div>
                            </li>
                            {/foreach}
                        </ul>
                    </div>
                    {/if}
                </div>

            </section>
            {/if}

            {if $active_comments == 1}

                <span class="db_title_h3 comments_count">{$comments|count} {l s='comentarios' mod='dbblog'}</span>
                <section class="form_comments --card-blog">
                    <p class="db_title_h3"><strong>{l s='Escribe un comentario' mod='dbblog'}</strong></p>
                    {include file='module:dbblog/views/templates/front/_partials/form_comment.tpl'}
                </section>

                {if $comments|count > 0}
                <section class="list_comments">
                    <div class="comentarios_users">
                        <div class="append_comments">
                            {foreach from=$comments item=comment}
                                <div class='comentario --card-blog'>
                                    <div class="comment_info">
                                        <div class="other_info">
                                            <span class="name">{$comment.name}</span>
                                            {*<div class="stars">
                                                <div class="total">
                                                    <div class="valoracion" style="width: {$comment.rating * 100 / 5}%"></div>
                                                </div>
                                            </div>*}
                                            <div class="valoracion_media">
                                                <div class="Stars" style="--rating: {$comment.rating|escape:'htmlall':'UTF-8'};"></div>
                                            </div>
                                        </div>
                                        <span><small>{$comment.date_add}</small></span>
                                    </div>
                                    <div class="comment_desc">
                                        <p>{$comment.comment}</p>
                                        <button type="button" class="boton xs btn_form_respond" data-id-post="{$post.id}" data-id-comment="{$comment.id_dbblog_comment}"><img src="{$path_img}icons/responder.svg"></button>
                                        <div id="form_respond" class="form_respond_{$comment.id_dbblog_comment}"></div>
                                    </div>
                                </div>

                                {if isset($comment.children)}
                                    {foreach from=$comment.children item=comment2}
                                        <span class="padding_1 name_resp">{l s='Respondiendo a'} {$comment.name}</span>
                                        <div class='comentario --card-blog padding_1'>
                                            <div class="comment_info">
                                                <div class="other_info">
                                                    <span class="name">{$comment2.name}</span>
                                                </div>
                                                <span><small>{$comment2.date_add}</small></span>
                                            </div>
                                            <div class="comment_desc">
                                                <p>{$comment2.comment}</p>
                                                <button type="button" class="boton xs btn_form_respond" data-id-post="{$post.id}" data-id-comment="{$comment2.id_dbblog_comment}"><img src="{$path_img}icons/responder.svg"></button>
                                                <div id="form_respond" class="form_respond_{$comment2.id_dbblog_comment}"></div>
                                            </div>
                                        </div>

                                        {if isset($comment2.children)}
                                            {foreach from=$comment2.children item=comment3}
                                                <span class="padding_2 name_resp">{l s='Respondiendo a'} {$comment2.name}</span>
                                                <div class='comentario --card-blog padding_2'>
                                                    <div class="comment_info">
                                                        <div class="other_info">
                                                            <span class="name">{$comment3.name}</span>
                                                        </div>
                                                        <span><small>{$comment3.date_add}</small></span>
                                                    </div>
                                                    <div class="comment_desc">
                                                        <p>{$comment3.comment}</p>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        {/if}
                                    {/foreach}
                                {/if}
                            {/foreach}

                        </div>
                    </div>
                </section>
                {/if}

            {/if}

        </div>

{/block}

        {include file='module:dbblog/views/templates/front/_partials/sidebar.tpl'}
    </div>