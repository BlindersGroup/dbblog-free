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
            {if !empty($category.meta_title)}
                {$category.meta_title} - {$title_blog}
            {else}
                {$category.title} - {$title_blog}
            {/if}
        {/block}
    </title>
    <meta rel="description" content="{$category.meta_description}">
    {if $category.index == 0}
        <meta name="robots" content="noindex,follow">
    {/if}
    <link rel="canonical" href="{$category.url}">
{/block}

{block name='hook_extra'}
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
            <section class="post__grid">
                {foreach from=$list_cat item=post}
                    {include file='module:dbblog/views/templates/front/_partials/post_mini.tpl'}
                {/foreach}
            </section>

            <div class="db_infinitescroll">
                <p class="sum_infinite">{l s='Has visto' mod='dbblog'} <span id="sum_infinite_from" class="sum_infinite_from" data-sumin="{$posts_per_page}">{$posts_per_page}</span> {l s='de' mod='dbblog'} <span class="total_posts" data-total="{$total_posts}">{$total_posts}</span> {l s='posts' mod='dbblog'}</p>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: {$percent_view}%" aria-valuenow="{$percent_view}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                {if $pagination == 1}
                    <div id="btn_db_inifinitescroll" class="btn_db_inifinitescroll" data-category="{$rewrite}" data-pag="1">Cargar más</div>
                {/if}
            </div>

            {if $category.large_desc|count_characters > 1}
            <div class="dbblog_large_desc_cat">
                {$category.large_desc nofilter}
            </div>
            {/if}
        </div>
{/block}

        {include file='module:dbblog/views/templates/front/_partials/sidebar.tpl'}
    </div>