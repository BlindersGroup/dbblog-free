<?php
/**
* 2007-2020 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class Dbblog extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        require_once(dirname(__FILE__).'/classes/DbBlogCategory.php');
        require_once(dirname(__FILE__).'/classes/DbBlogPost.php');
        require_once(dirname(__FILE__).'/classes/DbBlogComment.php');

        $this->name = 'dbblog';
        $this->tab = 'front_office_features';
        $this->version = '1.2.0';
        $this->author = 'DevBlinders';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('DB Blog');
        $this->description = $this->l('Blog para PrestaShop optimizado para SEO');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        if($this->id && !$this->isRegisteredInHook('moduleRoutes')) {
            $this->registerHook('moduleRoutes');
        }
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
	if(!Module::isEnabled('dbaboutus')){
            $this->_errors[] = $this->l('Debe de tener instalado y activo el módulo dbaboutus y el módulo dbdatatext');
            return false;
        }
        // Settings
        $this->createDb();
        $this->createTabs();
        // Config general
        Configuration::updateValue('DBBLOG_SLUG', 'blog');
        Configuration::updateValue('DBBLOG_AUTHOR_SLUG', 'author');
        Configuration::updateValue('DBBLOG_COLOR', '#3e5062');
        Configuration::updateValue('DBBLOG_POSTS_PER_PAGE', '12');
        Configuration::updateValue('DBBLOG_POSTS_PER_HOME', '12');
        Configuration::updateValue('DBBLOG_POSTS_PER_AUTHOR', '3');
        // Sidebar Blog
        Configuration::updateValue('DBBLOG_SIDEBAR_VIEWS', '4');
        Configuration::updateValue('DBBLOG_SIDEBAR_AUTHOR', '3');
        Configuration::updateValue('DBBLOG_SIDEBAR_LAST', '4');
        // Config Comments
        Configuration::updateValue('DBBLOG_COMMENTS', '1');
        Configuration::updateValue('DBBLOG_RECAPTCHA_ENABLE', '0');
        // Config Post
        Configuration::updateValue('DBBLOG_POST_EXTRACT', '0');
        Configuration::updateValue('DBBLOG_POST_READMORE', '0');
        Configuration::updateValue('DBBLOG_POST_RELATED', '6');
        Configuration::updateValue('DBBLOG_POST_AUTHOR', '6');
        // Home PrestaShop
        Configuration::updateValue('DBBLOG_POST_FEATURED_HOMEPS', '0');
        Configuration::updateValue('DBBLOG_POST_VIEWS_HOMEPS', '0');
        // Sidebar PrestaShop
        Configuration::updateValue('DBBLOG_POST_VIEWS_SIDEBARPS', '0');
        Configuration::updateValue('DBBLOG_POST_LAST_SIDEBARPS', '0');


        return parent::install() &&
            $this->registerHook('moduleRoutes') &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHome') &&
            $this->registerHook('displayLeftColumn');
    }

    public function uninstall()
    {
        $this->dropTables();
        $this->deleteTabs();
        Configuration::deleteByName('DBBLOG_SLUG');
        Configuration::deleteByName('DBBLOG_AUTHOR_SLUG');

        return parent::uninstall();
    }

    /**
     * Create tables
     */
    public function createDb()
    {
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dbblog_category` (
            `id_dbblog_category` int(11) NOT NULL AUTO_INCREMENT,
            `id_parent` int(10) NOT NULL DEFAULT \'0\',
            `position` int(10) NOT NULL DEFAULT \'0\',
            `index` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
            `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_dbblog_category`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dbblog_category_lang` (
            `id_dbblog_category` int(11) NOT NULL,
            `id_lang` int(11) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `title` varchar(128) NOT NULL,
            `short_desc` varchar(4000) NOT NULL,
            `large_desc` text NOT NULL,
            `link_rewrite` varchar(128) NOT NULL,
            `meta_title` varchar(128) NOT NULL,
            `meta_description` varchar(255) NOT NULL,
            PRIMARY KEY (`id_dbblog_category`, `id_lang`, `id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dbblog_category_post` (
            `id_dbblog_category` int(11) NOT NULL,
            `id_dbblog_post` int(11) NOT NULL,
            PRIMARY KEY (`id_dbblog_category`, `id_dbblog_post`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dbblog_post` (
            `id_dbblog_post` int(11) NOT NULL AUTO_INCREMENT,
            `id_dbblog_category` int(11) NOT NULL,
            `type` int(11) NOT NULL DEFAULT \'1\',
            `author` int(11) NOT NULL,
            `featured` tinyint(1) NOT NULL DEFAULT \'0\',
            `index` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
            `views` int(11) unsigned NOT NULL DEFAULT \'0\',
            `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_dbblog_post`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dbblog_post_lang` (
            `id_dbblog_post` int(11) NOT NULL,
            `id_lang` int(11) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `title` varchar(128) NOT NULL,
            `short_desc` varchar(4000) NOT NULL,
            `large_desc` text NOT NULL,
            `image` varchar(255) NOT NULL,
            `link_rewrite` varchar(128) NOT NULL,
            `meta_title` varchar(128) NOT NULL,
            `meta_description` varchar(255) NOT NULL,
            PRIMARY KEY (`id_dbblog_post`, `id_lang`, `id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dbblog_comment` (
            `id_dbblog_comment` int(11) NOT NULL AUTO_INCREMENT,
            `id_comment_parent` int(11) unsigned NOT NULL DEFAULT \'0\',
            `id_post` int(11) NOT NULL,
            `name` varchar(128) NOT NULL,
            `comment` text NOT NULL,
            `rating` int(1) NOT NULL,
            `approved` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
            `moderator` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_dbblog_comment`, `id_post`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
        
        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }
    }

    /**
     * Drop tables
     */
    public function dropTables()
    {
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dbblog_category`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dbblog_category_lang`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dbblog_category_post`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dbblog_post`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dbblog_post_lang`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dbblog_author`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dbblog_comment`';

        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }
    }

    /**
     * Create Tabs
     */
    public function createTabs()
    {
        // Tabs
        $idTabs = array();
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlog');
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlogCategory');
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlogPost');
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlogComment');
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlogSetting');

        foreach ($idTabs as $idTab) {
            if ($idTab) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }

        // Tabs
        if (!Tab::getIdFromClassName('AdminDevBlinders')) {
            $parent_tab = new Tab();
            $parent_tab->name = array();
            foreach (Language::getLanguages(true) as $lang)
                $parent_tab->name[$lang['id_lang']] = $this->l('DevBlinders');

            $parent_tab->class_name = 'AdminDevBlinders';
            $parent_tab->id_parent = 0;
            $parent_tab->module = $this->name;
            $parent_tab->add();

            $id_full_parent = $parent_tab->id;
        } else {
            $id_full_parent = Tab::getIdFromClassName('AdminDevBlinders');
        }

        $parent = new Tab();
        $parent->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $parent->name[$lang['id_lang']] = $this->l('Blog');

        $parent->class_name = 'AdminDbBlog';
        $parent->id_parent = $id_full_parent;
        $parent->module = $this->name;
        $parent->icon = 'assignment';
        $parent->add();

        // Categorias
        $tab_config = new Tab();
        $tab_config->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab_config->name[$lang['id_lang']] = $this->l('Categorías');

        $tab_config->class_name = 'AdminDbBlogCategory';
        $tab_config->id_parent = $parent->id;
        $tab_config->module = $this->name;
        $tab_config->add();

        // Posts
        $tab_config = new Tab();
        $tab_config->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab_config->name[$lang['id_lang']] = $this->l('Posts');

        $tab_config->class_name = 'AdminDbBlogPost';
        $tab_config->id_parent = $parent->id;
        $tab_config->module = $this->name;
        $tab_config->add();

        // Comentarios
        $tab_config = new Tab();
        $tab_config->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab_config->name[$lang['id_lang']] = $this->l('Comentarios');

        $tab_config->class_name = 'AdminDbBlogComment';
        $tab_config->id_parent = $parent->id;
        $tab_config->module = $this->name;
        $tab_config->add();

        // Configuración
        $tab_config = new Tab();
        $tab_config->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab_config->name[$lang['id_lang']] = $this->l('Configuración');

        $tab_config->class_name = 'AdminDbBlogSetting';
        $tab_config->id_parent = $parent->id;
        $tab_config->module = $this->name;
        $tab_config->add();
    }

    /**
     * Delete Tabs
     */
    public function deleteTabs()
    {
        // Tabs
        $idTabs = array();
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlog');
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlogCategory');
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlogPost');
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlogComment');
        $idTabs[] = Tab::getIdFromClassName('AdminDbBlogSetting');

        foreach ($idTabs as $idTab) {
            if ($idTab) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitDbludaModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitDbludaModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($this->getConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $general_options = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('General'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_TITLE',
                        'label' => $this->l('Título Blog'),
                        'desc' => $this->l('El titulo del blog'),
                        'lang'  => true,
                    ),

                    array(
                        'type' => 'textarea',
                        'name' => 'DBBLOG_HOME_SHORT_DESC',
                        'label' => $this->l('Descripción corta'),
                        'desc' => $this->l('Descripción corta en la home del blog'),
                        'autoload_rte' => true,
                        'rows' => 5,
                        'cols' => 40,
                        'lang'  => true,
                    ),

                    array(
                        'type' => 'textarea',
                        'name' => 'DBBLOG_HOME_LARGE_DESC',
                        'label' => $this->l('Descripción larga'),
                        'desc' => $this->l('Descripción larga en la home del blog'),
                        'autoload_rte' => true,
                        'rows' => 5,
                        'cols' => 40,
                        'lang'  => true,
                    ),

                    array(
                        'type' => 'color',
                        'name' => 'DBBLOG_COLOR',
                        'label' => $this->l('Color'),
                        'desc' => $this->l('Color general del blog'),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_POSTS_PER_PAGE',
                        'label' => $this->l('Nº Posts por categoría'),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_POSTS_PER_HOME',
                        'label' => $this->l('Nº Posts en Home'),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_POSTS_PER_AUTHOR',
                        'label' => $this->l('Nº Posts en página de autor'),
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $seo_options = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('SEO'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_SLUG',
                        'label' => $this->l('URL del blog'),
                        'lang'  => true,
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_META_TITLE',
                        'label' => $this->l('Meta Título Blog'),
                        'desc' => $this->l('El meta Description del blog'),
                        'lang'  => true,
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_META_DESCRIPTION',
                        'label' => $this->l('Meta Description Blog'),
                        'desc' => $this->l('La metadescription del blog'),
                        'lang'  => true,
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $sidebar_options = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Sidebar'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_SIDEBAR_VIEWS',
                        'label' => $this->l('Nº Posts más vistos'),
                        'desc' => $this->l('0 para desactivarlos'),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_SIDEBAR_LAST',
                        'label' => $this->l('Nº Últimos posts'),
                        'desc' => $this->l('0 para desactivarlos'),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_SIDEBAR_AUTHOR',
                        'label' => $this->l('Nº de autores'),
                        'desc' => $this->l('0 para desactivarlos'),
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $list_cms = CMSCore::listCms($this->context->language->id);
        $comment_options = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Comentarios'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Permitir comentarios'),
                        'name' => 'DBBLOG_COMMENTS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),

                    array(
                        'type' => 'textarea',
                        'name' => 'DBBLOG_RGPD',
                        'label' => $this->l('Texto RGPD'),
                        'autoload_rte' => true,
                        'rows' => 5,
                        'cols' => 40,
                        'lang'  => true,
                    ),

                    array(
                        'type' => 'select',
                        'lang' => true,
                        'label' => $this->l('Política de privacidad'),
                        'name' => 'DBBLOG_PRIVACITY',
                        'desc' => $this->l('Seleccionar la página de de política de privacidad'),
                        'options' => array(
                            'query' => $list_cms,
                            'id' => 'id_cms', 
                            'name' => 'meta_title'
                        ),
                    ),

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activar Recaptcha V2'),
                        'name' => 'DBBLOG_RECAPTCHA_ENABLE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_RECAPTCHA',
                        'label' => $this->l('reCAPTCHA clave pública'),
                        'desc' => $this->l('Introducir la clave publica de reCAPTCHA de Google'),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_RECAPTCHA_PRIVATE',
                        'label' => $this->l('reCAPTCHA clave privada'),
                        'desc' => $this->l('Introducir la clave privada de reCAPTCHA de Google'),
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $post_options = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Posts'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Mostrar extracto listado'),
                        'name' => 'DBBLOG_POST_EXTRACT',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Mostrar leer más'),
                        'name' => 'DBBLOG_POST_READMORE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_POST_RELATED',
                        'label' => $this->l('Nº Post relacionados'),
                        'desc' => $this->l('0 para desactivarlos'),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_POST_AUTHOR',
                        'label' => $this->l('Nº Post del mismo autor'),
                        'desc' => $this->l('0 para desactivarlos'),
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $home_ps_options = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Home PrestaShop'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_POST_FEATURED_HOMEPS',
                        'label' => $this->l('Nº Post destacados'),
                        'desc' => $this->l('0 para desactivarlos'),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_POST_VIEWS_HOMEPS',
                        'label' => $this->l('Nº Post más vistos'),
                        'desc' => $this->l('0 para desactivarlos'),
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $sidebar_ps_options = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Sidebar PrestaShop'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_POST_VIEWS_SIDEBARPS',
                        'label' => $this->l('Nº Post más vistos'),
                        'desc' => $this->l('0 para desactivarlos'),
                    ),

                    array(
                        'type' => 'text',
                        'name' => 'DBBLOG_POST_LAST_SIDEBARPS',
                        'label' => $this->l('Nº Últimos posts'),
                        'desc' => $this->l('0 para desactivarlos'),
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        return array($general_options, $seo_options, $sidebar_options, $comment_options, $post_options, $home_ps_options, $sidebar_ps_options);

    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $languages = Language::getLanguages(false);
        $values = array();

        foreach ($languages as $lang){         
            $values['DBBLOG_TITLE'][$lang['id_lang']] = Configuration::get('DBBLOG_TITLE', $lang['id_lang']);
            $values['DBBLOG_HOME_SHORT_DESC'][$lang['id_lang']] = Configuration::get('DBBLOG_HOME_SHORT_DESC', $lang['id_lang']);
            $values['DBBLOG_HOME_LARGE_DESC'][$lang['id_lang']] = Configuration::get('DBBLOG_HOME_LARGE_DESC', $lang['id_lang']);
            $values['DBBLOG_SLUG'][$lang['id_lang']] = Configuration::get('DBBLOG_SLUG', $lang['id_lang']);
            $values['DBBLOG_META_TITLE'][$lang['id_lang']] = Configuration::get('DBBLOG_META_TITLE', $lang['id_lang']);
            $values['DBBLOG_META_DESCRIPTION'][$lang['id_lang']] = Configuration::get('DBBLOG_META_DESCRIPTION', $lang['id_lang']);
            $values['DBBLOG_RGPD'][$lang['id_lang']] = Configuration::get('DBBLOG_RGPD', $lang['id_lang']);
        }

        $values['DBBLOG_COLOR'] = Configuration::get('DBBLOG_COLOR');
        $values['DBBLOG_POSTS_PER_PAGE'] = Configuration::get('DBBLOG_POSTS_PER_PAGE');
        $values['DBBLOG_POSTS_PER_HOME'] = Configuration::get('DBBLOG_POSTS_PER_HOME');
        $values['DBBLOG_POSTS_PER_AUTHOR'] = Configuration::get('DBBLOG_POSTS_PER_AUTHOR');
        $values['DBBLOG_SIDEBAR_VIEWS'] = Configuration::get('DBBLOG_SIDEBAR_VIEWS');
        $values['DBBLOG_SIDEBAR_LAST'] = Configuration::get('DBBLOG_SIDEBAR_LAST');
        $values['DBBLOG_SIDEBAR_AUTHOR'] = Configuration::get('DBBLOG_SIDEBAR_AUTHOR');
        $values['DBBLOG_COMMENTS'] = Configuration::get('DBBLOG_COMMENTS');
        $values['DBBLOG_PRIVACITY'] = Configuration::get('DBBLOG_PRIVACITY');
        $values['DBBLOG_RECAPTCHA_ENABLE'] = Configuration::get('DBBLOG_RECAPTCHA_ENABLE');
        $values['DBBLOG_RECAPTCHA'] = Configuration::get('DBBLOG_RECAPTCHA');
        $values['DBBLOG_RECAPTCHA_PRIVATE'] = Configuration::get('DBBLOG_RECAPTCHA_PRIVATE');
        $values['DBBLOG_POST_RELATED'] = Configuration::get('DBBLOG_POST_RELATED');
        $values['DBBLOG_POST_EXTRACT'] = Configuration::get('DBBLOG_POST_EXTRACT');
        $values['DBBLOG_POST_READMORE'] = Configuration::get('DBBLOG_POST_READMORE');
        $values['DBBLOG_POST_AUTHOR'] = Configuration::get('DBBLOG_POST_AUTHOR');
        $values['DBBLOG_POST_FEATURED_HOMEPS'] = Configuration::get('DBBLOG_POST_FEATURED_HOMEPS');
        $values['DBBLOG_POST_VIEWS_HOMEPS'] = Configuration::get('DBBLOG_POST_VIEWS_HOMEPS');
        $values['DBBLOG_POST_VIEWS_SIDEBARPS'] = Configuration::get('DBBLOG_POST_VIEWS_SIDEBARPS');
        $values['DBBLOG_POST_LAST_SIDEBARPS'] = Configuration::get('DBBLOG_POST_LAST_SIDEBARPS');

        return $values;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $languages = Language::getLanguages(false);
        $id_shop_group = (int)Context::getContext()->shop->id_shop_group;
        $id_shop = (int)Context::getContext()->shop->id;

        foreach ($form_values as $name => $key) {
            if(is_array($key)){
                $values = array();
                foreach ($languages as $lang){
                    $values[$lang['id_lang']] = Tools::getValue($name . '_'.(int)$lang['id_lang']);
                }
                Configuration::updateValue($name, $values, true, $id_shop_group, $id_shop);
            } else {
                Configuration::updateValue($name, Tools::getValue($name), true, $id_shop_group, $id_shop);
            }
        }
    }
    

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        Media::addJsDef(array(
            'PS_ALLOW_ACCENTED_CHARS_URL' => (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
            'ps_force_friendly_product' => (int)Configuration::get('PS_FORCE_FRIENDLY_PRODUCT'),
        ));
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $id_lang = Context::getContext()->language->id;

        // SplideJS Carousel
        $this->context->controller->addCSS($this->_path . 'views/css/splide/splide.min.css');
        $this->context->controller->addCSS($this->_path . 'views/css/splide/themes/splide-default.min.css');
        $this->context->controller->addJS($this->_path . 'views/js/splide.min.js');

        $this->context->controller->addJS($this->_path.'/views/js/dbblog.js');
        $this->context->controller->addCSS($this->_path.'/views/css/dbblog.css');
        $this->context->smarty->registerPlugin("modifier",'base64_encode', 'base64_encode');
        Media::addJsDef(array(
            'dbblog_ajax' => Context::getContext()->link->getModuleLink('dbblog', 'ajax', array()),
        ));

        $color = Configuration::get('DBBLOG_COLOR');
        $inline = '<style>
            .db__taxonomy a:hover {
                color: '.$color.';
            }
            #module-dbblog-dbcategory .header__category,
            #module-dbblog-dbpost .header__category,
            #module-dbblog-dbhome .header__category {
                background: '.$color.';
            }
            .btn_db_inifinitescroll,
            .btn_db_inifinitescroll_author {
                background: '.$color.';
            }
            .bck_title {
                background: '.$color.';
            }
            .info_post .info_up .info_author a:hover {
                color: '.$color.';
            }
            .form_comment_post .send_comment {
                background: '.$color.';
            }
            .header__author {
                background: '.$color.';
            }
        </style>';
        return $inline;
    }
    
    /**
     * Add Routes
     */
    public function hookModuleRoutes($params)
    {

        $context = Context::getContext();
        $controller = Tools::getValue('controller', 0);
        $id_lang = $context->language->id;
        $blog_slug = Configuration::get('DBBLOG_SLUG', $id_lang);

        $my_routes = array(
            // Home
            'module-dbblog-dbhome' => array(
                'controller' => 'dbhome',
                'rule' => $blog_slug.'/',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'dbblog',
                ),
            ),

            // Category
            'module-dbblog-dbcategory' => array(
                'controller' => 'dbcategory',
                'rule' =>       $blog_slug.'/{rewrite}/',
                'keywords' => array(
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'dbblog',
                ),
            ),

            // Post
            'module-dbblog-dbpost' => array(
                'controller' => 'dbpost',
                'rule' =>       $blog_slug.'/{rewrite}.html',
                'keywords' => array(
                    'rewrite' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'dbblog',
                ),
            ),  

        );

        return $my_routes;
    }

    public function hookdisplayLeftColumn($params)
    {
        $id_lang = Context::getContext()->language->id;
        // Mas vistos Sidebar
        $num_views = Configuration::get('DBBLOG_POST_VIEWS_SIDEBARPS');
        $more_views = DbBlogCategory::getPostsViews($id_lang, null, null, null, $num_views);
        // Ultimos Sidebar
        $num_last = Configuration::get('DBBLOG_POST_LAST_SIDEBARPS');
        $last_posts = DbBlogCategory::getPostsLast($id_lang, null, null, null, $num_last);

        if($num_views > 0 || $last_posts > 0) {
            $this->context->smarty->assign(array(
                'path_img' => _MODULE_DIR_ . 'dbblog/views/img/',
                'more_views' => $more_views,
                'last_posts' => $last_posts,
            ));
            return $this->display(__FILE__, 'views/templates/hook/sidebar.tpl');
        }
    }

    public function hookdisplayHome($params)
    {
        $id_lang = Context::getContext()->language->id;
        // Mas vistos Sidebar
        $limit_views_home = (int)Configuration::get('DBBLOG_POST_VIEWS_HOMEPS');
        $more_views = DbBlogCategory::getPostsViews($id_lang, null, null, null, $limit_views_home);
        // Ultimos Sidebar
        $limit_last_home = (int)Configuration::get('DBBLOG_POST_FEATURED_HOMEPS');
        $last_posts = DbBlogCategory::getPostsLast($id_lang, null, null, null, $limit_last_home);

        if($limit_views_home > 0 || $limit_last_home > 0) {
            $this->context->smarty->assign(array(
                'more_views' => $more_views,
                'last_posts' => $last_posts,
                'limit_views' => $limit_views_home,
                'limit_last' => $limit_last_home,
            ));
            return $this->display(__FILE__, 'views/templates/hook/homeps.tpl');
        }
    }

    public function renderScroll($posts)
    {
        $this->smarty->assign(array(
            'list_post' => $posts,
        ));

        return $this->fetch('module:dbblog/views/templates/front/_partials/infinite_scroll.tpl');
    }

    public function renderFormRespond($id_comment, $id_post)
    {
        // Customer
        $customer_login = $this->context->customer->isLogged();
        $customer_name = '';
        if($customer_login){
            $customer_name = $this->context->customer->firstname.' '.$this->context->customer->lastname;
        }

        $this->smarty->assign(array(
            'id_comment' => $id_comment,
            'customer_name' => $customer_name,
            'customer_login' => $customer_login,
            'id_post' => $id_post,
        ));

        return $this->fetch('module:dbblog/views/templates/front/_partials/form_comment.tpl');
    }

    public function shortCodes($desc)
    {

        preg_match_all("/{dbblog_products (.*)}/",
            $desc,
            $shortcodes, PREG_PATTERN_ORDER);

        // Valores
        $variables = [];
        foreach ($shortcodes[1] as $z => $sc){
            $valores = explode(' ', $sc);
            foreach ($valores as $val){
                list($key, $data) = explode('=', $val);
                $variables[$z][$key] = $data;
                if($key == 'id_product'){
                    $variables[$z]['type'] = 'product';
                } elseif($key == 'id_category'){
                    $variables[$z]['type'] = 'category';
                }
            }
        }

        foreach($variables as $key => $val){
            $replace = '';
            if($val['type'] == 'product'){
                $product = $this->getProductSC((int)$val['id_product']);
                if(is_object($product)) {
                    $this->smarty->assign(array(
                        'product' => $product,
                        'type' => 'product'
                    ));
                    $replace = $this->fetch('module:dbblog/views/templates/front/_partials/sc_product.tpl');
                }
            } elseif($val['type'] == 'category') {
                $products = $this->getProductsSC((int)$val['id_category'], $val['order'], $val['way'], (int)$val['num']);
                if(is_array($products)) {
                    $this->smarty->assign(array(
                        'products' => $products,
                        'type' => 'category'
                    ));
                    $replace = $this->fetch('module:dbblog/views/templates/front/_partials/sc_product.tpl');
                }
            }
            $string_replace = $shortcodes[0][$key];

            $desc = str_replace($string_replace, $replace, $desc);
        }

        return $desc;
    }

    public function getProductSC($id_product)
    {
        $product = new Product($id_product, null, $this->context->language->id);
        if(!empty($product->link_rewrite) && $product->link_rewrite != '') {
            $product = array(
                'id_product' => $id_product,
            );
            $assembler = new ProductAssembler($this->context);

            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            $product_for_template = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($product),
                $this->context->language
            );

            return $product_for_template;
        }

        return;
    }

    public function getProductsSC($id_category = null, $orderby = null, $way = null, $num = null)
    {
        $id_lang = $this->context->language->id;
        $category = new Category($id_category, $id_lang);
        if($orderby != 'seller' && $orderby != 'id_product' && $orderby != 'date_add' && $orderby != 'date_upd' && $orderby != 'name'
            && $orderby != 'manufacturer' && $orderby != 'position' && $orderby != 'price'){
            $orderby = 'seller';
        }
        if($way != 'asc' && $way != 'desc'){
            $way = 'DESC';
        }
        if($num < 1){ $num = 4; }
        if(!empty($category->link_rewrite) && $category->link_rewrite != '') {
            if($orderby == 'seller'){
                $products = $this->getProductsSeller($id_category, $way, $num);
            } else {
                $products = $category->getProducts($id_lang, 1, $num, $orderby);
            }

            $products_for_template = [];
            $assembler = new ProductAssembler($this->context);
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(new ImageRetriever($this->context->link), $this->context->link, new PriceFormatter(), new ProductColorsRetriever(), $this->context->getTranslator());
            foreach ($products as $rawProduct)
            {
                $products_for_template[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $this->context->language
                );
            }

            return $products_for_template;
        }
    }

    public function getProductsSeller($id_category, $way = null, $num = null)
    {
        if($way == null){ $way = DESC; }
        $sql = "SELECT sp.id_product 
                    FROM "._DB_PREFIX_."specific_price sp
                    LEFT JOIN "._DB_PREFIX_."category_product pp
                        ON sp.id_product = pp.id_product
                    LEFT JOIN "._DB_PREFIX_."stock_available sa
                        ON sp.id_product = sa.id_product
                    LEFT JOIN "._DB_PREFIX_."product p
                        ON pp.id_product = p.id_product
                    WHERE pp.id_category = '$id_category' AND p.active = 1 AND sa.id_product_attribute = 0
                    GROUP BY p.id_product
                    HAVING SUM(sa.quantity) > 0
                    ORDER BY sp.reduction ".$way."
                    LIMIT ".$num;
        $products = Db::getInstance()->ExecuteS($sql);

        return $products;
    }

}
