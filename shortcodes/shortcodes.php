<?php


define('PL_SC_PREFIX', '');


class PL_Shortcode_Handler {
	static public function register_shortcodes() {}
	public function shortcode_disabled($args, $content, $shortcode) {
		return "[$shortcode]";
	}
}


class PL_Shortcode_Result {
}

class PL_Shortcode_Yield extends PL_Shortcode_Result {
}


class PL_Shortcode_Dispatcher {
	protected $default_handler;     // implements fall through processing for shortcodes without active handlers

	protected $registered_classes;  // all known handler classes providing shortcode functionality
	protected $descendant_classes;  // known subclasses of registered classes -- so we only have to search once

	protected $modal_handler;       // a single active handler, blocks normal processing
	protected $active_classes;      // registered classes that are currently enabled with active handlers
	protected $active_handlers;     // the active handler objects of those classes (using same numeric index)

	protected $shortcode_classes;   // the registered class(es) that can handle each shortcode tag, for lookup
	protected $shortcode_functions; // the associated member function(s) for each shortcode tag, for lookup

	public function __construct() {
		$this->default_handler = new PL_Shortcode_Handler();
		$this->modal_handler = null;

		$this->registered_classes = array();
		$this->active_classes = array();
		$this->active_handlers = array();
		$this->shortcode_classes = array();
		$this->shortcode_functions = array();
	}

	public function register_handler_class($handler_class) {
		$this->registered_classes[$handler_class] = $handler_class;
		$handler_class::register_shortcodes($this);
	}

	public function register_shortcode($handler_class, $shortcode, $function) {
		if(!$this->shortcode_classes[$shortcode]) {
			$this->shortcode_classes[$shortcode] = array($handler_class);
			add_shortcode($shortcode, array($this, 'dispatch_shortcode'));
		}
		else if(!in_array($handler_class, $this->shortcode_classes[$shortcode])) {
			array_unshift($this->shortcode_classes[$shortcode], $handler_class);
		}

		$this->shortcode_functions[$shortcode][$handler_class] = $function;
	}

	public function dispatch_shortcode($args, $content, $shortcode) {
		if($shortcode_classes = $this->shortcode_classes[$shortcode])
			if($active_classes = array_intersect($this->active_classes, $this->shortcode_classes[$shortcode])) {
				foreach($active_classes as $key => $class) {

					$handler = $this->active_handlers[$key];
					if($this->modal_handler && $this->modal_handler !== $handler)
						continue;

					$function = $this->shortcode_functions[$shortcode][$class];
					$result = $handler->{$function}($args, $content, $shortcode);

					if(is_a($result, 'PL_Shortcode_Yield'))
						continue;

					return $result;
				}
			}

		return $this->default_handler->shortcode_disabled($args, $content, $shortcode);
	}

	public function attach_handler(PL_Shortcode_Handler $handler, $modal = false) {
		if($class = $this->get_handler_class($handler)) {
			if($modal)
				$this->modal_handler = $handler;
			array_unshift($this->active_classes, $class);
			array_unshift($this->active_handlers, $handler);
		}
	}

	public function detach_handler(PL_Shortcode_Handler $handler) {
		if(($index = array_search($handler, $this->active_handlers, true)) !== false) {
			if($handler === $this->modal_handler)
				$this->modal_handler = null;
			unset($this->active_classes[$index]);
			unset($this->active_handlers[$index]);
		}
	}

	protected function get_handler_class(PL_Shortcode_Handler $handler) {
		$class = get_class($handler);
		if($this->registered_classes[$class])
			return $class;

		if($this->descendant_classes[$class])
			return $this->descendant_classes[$class];

		foreach($this->registered_classes as $registered_class)
			if(is_a($handler, $registered_class))
				return $this->descendant_classes[$class] = $registered_class;

		return null;
	}
}


class PL_Shortcode_Global {
	static protected $dispatcher;

	public function __construct(PL_Shortcode_Dispatcher $dispatcher = null) {
		if(!self::$dispatcher)
			if($dispatcher)
				self::$dispatcher = $dispatcher;
			else
				self::$dispatcher = new PL_Shortcode_Dispatcher();
	}
}


class PL_Shortcode_Context extends PL_Shortcode_Global {
	protected $handler;

	public function __construct(PL_Shortcode_Handler $handler, $modal = false) {
		self::$dispatcher->attach_handler($this->handler = $handler, $modal);
	}

	public function __destruct() {
		self::$dispatcher->detach_handler($this->handler);
	}
}


class PL_Shortcode_System extends PL_Shortcode_Global {
	public function register_handler_class($handler_class) {
		self::$dispatcher->register_handler_class($handler_class);
	}

	public function attach_handler(PL_Shortcode_Handler $handler) {
		self::$dispatcher->attach_handler($handler);
	}

	public function detach_handler(PL_Shortcode_Handler $handler) {
		self::$dispatcher->detach_handler($handler);
	}
}
