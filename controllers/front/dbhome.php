<?php

class DbblogDbHomeModuleFrontController extends ModuleFrontController
{
    public $home;

    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        $id_lang = Context::getContext()->language->id;

        parent::initContent();

        // Cabecera
        $title_blog = Configuration::get('DBBLOG_TITLE', $id_lang);
        $categories = DbBlogCategory::getCategories($id_lang, true);

        // Descripciones
        $short_desc = Configuration::get('DBBLOG_HOME_SHORT_DESC', $id_lang);
        $large_desc = Configuration::get('DBBLOG_HOME_LARGE_DESC', $id_lang);

        // Posts Destacados
        $destacados = DbBlogPost::getPostFeatures($id_lang);
        
        // Posts views
        $post_extract = Configuration::get('DBBLOG_POST_EXTRACT');
        $post_readmore = Configuration::get('DBBLOG_POST_READMORE');

        // Authors
        $authors = DbBlogPost::getAuthors(0, 0);

        // Redes sociales
        $rrss = 0;
        $twitter = Configuration::get('DBBLOG_TWITTER');
        $facebook = Configuration::get('DBBLOG_FACEBOOK');
        $linkedin = Configuration::get('DBBLOG_LINKEDIN');
        $youtube = Configuration::get('DBBLOG_YOUTUBE');
        $instagram = Configuration::get('DBBLOG_INSTAGRAM');
        if(!empty($twitter) || !empty($facebook) || !empty($linkedin) || !empty($youtube)
            || !empty($instagram)){
                $rrss = 1;
        }

        // Mas vistos Sidebar
        $more_views = DbBlogCategory::getPostsViews($id_lang, NULL, NULL, NULL, Configuration::get('DBBLOG_SIDEBAR_VIEWS'));

        // Ultimos Sidebar
        $last_posts = DbBlogCategory::getPostsLast($id_lang, NULL, NULL, NULL, Configuration::get('DBBLOG_SIDEBAR_LAST'));

        $last_posts_home = DbBlogCategory::getPostsLast($id_lang, NULL, NULL, NULL, Configuration::get('DBBLOG_POSTS_PER_HOME'));

        $this->context->smarty->assign(array(
            'title_blog'    => $title_blog,
            'categories'    => $categories,
            'isHome'        => 1,
            'isCategory'    => 0,
            'isAuthors'     => 0,
            'isAuthor'      => 0,
            'isPost'        => 0,

            'short_desc'    => $short_desc,
            'large_desc'    => $large_desc,
            'destacados'    => $destacados,
            'path_img_posts' => _MODULE_DIR_.'dbblog/views/img/post/',
            'post_extract'      => $post_extract,
            'post_readmore'     => $post_readmore,

            'authors'       => $authors,
            'rrss'          => $rrss,
            'twitter'       => $twitter,
            'facebook'      => $facebook,
            'linkedin'      => $linkedin,
            'youtube'       => $youtube,
            'instagram'     => $instagram,
            'path_img'      => _MODULE_DIR_.'dbblog/views/img/',
            'more_views'    => $more_views,
            'last_posts'    => $last_posts,
            'last_posts_home'    => $last_posts_home,
        ));

        $this->setTemplate('module:dbblog/views/templates/front/home.tpl');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $id_lang = Context::getContext()->language->id;
        $title_blog = Configuration::get('DBBLOG_TITLE', $id_lang);
        $url_home = Context::getContext()->link->getModuleLink('dbblog', 'dbhome', array());

        $breadcrumb['links'][] = [
            'title' => $title_blog,
            'url'   => $url_home,
        ];

        return $breadcrumb;
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();

        $url_home = Context::getContext()->link->getModuleLink('dbblog', 'dbhome', array());
        $meta_title = Configuration::get('DBBLOG_META_TITLE', $this->context->language->id);
        $meta_description = Configuration::get('DBBLOG_META_DESCRIPTION', $this->context->language->id);

        $page['canonical'] = $url_home;
        $page['meta']['title'] = $meta_title;
        $page['meta']['description'] = $meta_description;

        return $page;
    }
}