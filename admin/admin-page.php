<?php


if(!class_exists('PL_Admin_Page_Base')) {
	class PL_Admin_Page_Base {
		static protected $class_version;
		static protected $pages;

		protected $version;

		protected function __construct($version) {
			if(!$version) return; // ignore any pages created without a proper version string

			$this->version = $version;
			if(!self::$class_version || $version > self::$class_version)
				self::$class_version = $version;

			if(!self::$pages)
				add_action('plugins_loaded', array(__CLASS__, 'plugins_loaded'));

			self::$pages[] = $this;
		}

		static public function plugins_loaded() {
			if(method_exists(self::$class_version, 'plugins_loaded'))
				call_user_func(array(self::$class_version, 'plugins_loaded'));
		}
	}
}

if(!class_exists('PL_Admin_Page_v1A')) {
	class PL_Admin_Page_v1A extends PL_Admin_Page_Base {
		static protected $hooked_pages;
		static protected $registered_scripts;
		static protected $registered_styles;


		public $page_parent;
		public $page_name;
		public $order;

		public $page_title;
		public $menu_title;

		public $template;
		public $scripts;
		public $styles;


		public function __construct($page_parent, $order, $page_name, $menu_title, $page_title, $template) {
			parent::__construct(__CLASS__);
			$this->page_parent = $page_parent;
			$this->page_name = $page_name;
			$this->order = $order;

			$this->menu_title = $menu_title;
			$this->page_title = $page_title;

			$this->template = $template;
		}

		public function require_script($name, $src = null, $dependencies = array(), $version = null, $in_footer = false) {
			if(!$this->scripts[$name])
				$this->scripts[$name] = self::script_entry($name, $src, $dependencies, $version, $in_footer);
		}

		public function require_style($name, $src = null, $dependencies = array()) {
			if(!$this->styles[$name])
				$this->styles[$name] = self::style_entry($name, $src, $dependencies);
		}


		static public function register_script($name, $src, $dependencies = array(), $version = null, $in_footer = false) {
			if(!self::$registered_scripts[$name])
				self::$registered_scripts[$name] = self::script_entry($name, $src, $dependencies, $version, $in_footer);
		}

		static private function script_entry($name, $src, $dependencies, $version, $in_footer) {
			$script = new StdClass();

			$script->name = $name;
			$script->src = $src;
			$script->dependencies = $dependencies;
			$script->in_footer = $in_footer;

			if (!$version) {
				$pos = strpos($src, PLACESTER_PLUGIN_URL);
				if ($pos === 0) {
					$file = PLACESTER_PLUGIN_DIR . substr($src, strlen(PLACESTER_PLUGIN_URL));
					if (file_exists($file)) {
						$version = filemtime($file);
					}
				}
			}
			$script->version = $version;

			return $script;
		}

		static public function register_style($name, $src, $dependencies = array()) {
			if(!self::$registered_styles[$name])
				self::$registered_styles[$name] = self::style_entry($name, $src, $dependencies);
		}

		static private function style_entry($name, $src, $dependencies) {
			$style = new StdClass();

			$style->name = $name;
			$style->src = $src;
			$style->dependencies = $dependencies;

			return $style;
		}


		// the class needs to be versioned in order to change this logic
		final public function render_admin_page() {
			// call the newest available render_admin_header, not necessarily mine
			if(method_exists(self::$class_version, 'render_admin_header'))
				call_user_func(array(self::$class_version, 'render_admin_header'), $this);

			$this->render_admin_content();

			// call the newest render_admin_footer, not necessarily mine
			if(method_exists(self::$class_version, 'render_admin_footer'))
				call_user_func(array(self::$class_version, 'render_admin_footer'), $this);
		}

		public function page_enqueue_scripts() {
			if($this->scripts) foreach($this->scripts as $script)
				if(!$script->src || wp_script_is($script->name, 'registered'))
					wp_enqueue_script($script->name);
				else
					wp_enqueue_script($script->name, $script->src, $script->dependencies, $script->version, $script->in_footer);
		}

		public function page_enqueue_styles() {
			if($this->styles) foreach($this->styles as $style)
				if(!$style->src || wp_style_is($style->name, 'registered'))
					wp_enqueue_style($style->name);
				else
					wp_enqueue_style($style->name, $style->src, $style->dependencies);
		}

		static protected function render_admin_header(PL_Admin_Page $current_page = null) {
			if(!$current_page)
				$current_page = reset(self::$hooked_pages);

			if($current_page->page_parent == 'placester')
				$parent_page = $current_page;
			else
				$parent_page = self::$hooked_pages['placester_page_' . $current_page->page_parent];

			if(!$parent_page)
				$parent_page = reset(self::$hooked_pages);

			echo "<div class='wrap'><div class='clear'></div>";
			echo "<h2 id='placester-admin-menu' class='nav-tab-wrapper'>";

			$sub_pages = array();
			foreach(self::$hooked_pages as $page) {
				if($page->page_parent == 'placester') {
					$class = 'nav-tab' . ($page === $parent_page ? ' nav-tab-active' : '');
					echo "<a href='admin.php?page={$page->page_name}' style='font-size: 15px'" .
						" class='$class' id='{$page->page_name}'>{$page->menu_title}</a>";
				}
				else if($page->page_parent == $parent_page->page_name) {
					$sub_pages[] = $page;
				}
			}

			echo "</h2>";
			echo "<div class='wrapper'>";

			if($sub_pages) {
				echo "<div class='settings_sub_nav'><ul>";
				echo "<li class='submenu-title'>{$parent_page->menu_title}:</li>";

				$style = ($parent_page === $current_page ? " style='color:#D54E21;'" : '');
				echo "<li><a href='admin.php?page={$parent_page->page_name}'{$style}>{$parent_page->page_title}</a></li>";

				foreach ($sub_pages as $page) {
					$style = ($page === $current_page ? " style='color:#D54E21;'" : '');
					echo "<li><a href='admin.php?page={$page->page_name}'{$style}>{$page->menu_title}</a></li>";
				}

				echo "</ul></div>";
			}
		}

		// can be overriden for specialized pages
		protected function render_admin_content() {
			if($this->template)
				include $this->template;
		}

		static protected function render_admin_footer(PL_Admin_Page $page = null) {
			echo '</div></div>';
		}


		static public function plugins_loaded() {
			add_action('admin_init', array(__CLASS__, 'admin_init'));
			add_action('admin_menu', array(__CLASS__, 'admin_menu'));
			add_action('admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts'));
		}

		static public function admin_init() {
			if(self::$registered_scripts) foreach(self::$registered_scripts as $script)
				if(!wp_script_is($script->name, 'registered'))
					wp_register_script($script->name, $script->src, $script->dependencies, $script->version, $script->in_footer);

			if(self::$registered_styles) foreach(self::$registered_styles as $style)
				if(!wp_style_is($style->name, 'registered'))
					wp_register_style($style->name, $style->src, $style->dependencies);
		}

		static public function admin_menu() {
			add_menu_page('Placester', 'Placester', 'edit_pages', 'placester', array(reset(self::$pages), 'render_admin_page'), PLACESTER_PLUGIN_URL . 'admin/logo_16.png', '3.5');

			global $submenu;
			$submenu['placester'] = array();

			if(self::$pages) {
				usort(self::$pages, function ($a, $b) { return $a->order == $b->order ? 0 : ($a->order > $b->order ? 1 : -1); });
				foreach(self::$pages as $page)
					if($hook = add_submenu_page($page->page_parent, $page->menu_title, $page->menu_title, 'edit_pages', $page->page_name, array($page, 'render_admin_page')))
						self::$hooked_pages[$hook] = $page;
			}
		}

		static public function admin_enqueue_scripts($hook) {
			if($page = self::$hooked_pages[$hook]) {
				$page->page_enqueue_scripts();
				$page->page_enqueue_styles();

				// manually set the active menu page if we're on one of our sub-pages, so WP can render the menus correctly
				if($page->page_parent != 'placester') {
					global $parent_file, $plugin_page;
					$plugin_page = $page->page_parent;
					$parent_file = 'placester';
				}
			}
		}
	}
}
