<?php


require_once(BUILDER_DIR . 'api/connection.php');


class PL_Shortcode_Handler {
	public static function register_shortcodes() {}
	public static function shortcode_disabled($args, $content, $shortcode) {
		return "[$shortcode]";
	}
}


class PL_Shortcode_Result {
}

class PL_Shortcode_Yield extends PL_Shortcode_Result {
}

class PL_Shortcode_Error extends PL_Shortcode_Result {
	public $message;
	public function __construct($m = 'shortcode error') { $this->message = $m; }
}


class PL_Shortcode_Dispatcher {
	protected $registered_classes;

	protected $active_classes;
	protected $active_handlers;

	protected $shortcode_classes;
	protected $shortcode_functions;

	public function __construct() {
		$this->registered_classes = array();
		$this->active_classes = array();
		$this->active_handlers = array();
		$this->shortcode_classes = array();
		$this->shortcode_functions = array();
	}

	public function register_handler($class) {
		if(class_exists($class) && method_exists($class, 'register_shortcodes')) {
			$this->registered_classes[$class] = $class;
			$class::register_shortcodes($this);
		}
	}

	public function attach_handler($class, PL_Shortcode_Handler $handler) {
		if($this->registered_classes[$class]) {
			array_unshift($this->active_classes, $class);
			array_unshift($this->active_handlers, $handler);
		}
	}

	public function detach_handler($class, PL_Shortcode_Handler $handler) {
		foreach(array_keys($this->active_classes, $class) as $index)
			if($this->active_handlers[$index] === $handler) {
				unset($this->active_classes[$index]);
				unset($this->active_handlers[$index]);
			}
	}

	public function register_shortcode($shortcode, $class, $function) {
		if($this->registered_classes[$class]) {
			$this->shortcode_classes[$shortcode][] = $class;
			$this->shortcode_functions[$shortcode][$class] = $function;
		}
	}

	public function dispatch_shortcode($shortcode, $args, $content) {
		if($shortcode_classes = $this->shortcode_classes[$shortcode])
			if($active_classes = array_intersect($this->active_classes, $this->shortcode_classes[$shortcode])) {
				foreach($active_classes as $key => $class) {
					$handler = $this->active_handlers[$key];
					$function = $this->shortcode_functions[$shortcode][$class];
					$result = $handler->{$function}($args, $content, $shortcode);
					if(is_a($result, 'PL_Shortcode_Yield'))
						continue;
					else if(is_a($result, 'PL_Shortcode_Error'))
						return "[$shortcode: {$result->message}]";
					else if(is_a($result, 'PL_Shortcode_Result'))
						return "[$shortcode: Unexpected result]";
					else
						return $result;
				}
			}

		return PL_Shortcode_Handler::shortcode_disabled($args, $content, $shortcode);
	}

	public function get_registered_shortcodes() {
		return array_unique(array_keys($this->shortcode_classes));
	}
	public function get_active_shortcodes() {
		$step1 = array_intersect($this->shortcode_classes, $this->active_classes);
		$step2 = array_keys($step1);
		$step3 = array_unique($step2);
		return array_unique(array_keys(array_intersect($this->shortcode_classes, $this->active_classes)));
	}
}


class PL_Shortcode_System {
	static protected $dispatcher;

	public function __construct() {
		if(!self::$dispatcher)
			self::$dispatcher = new PL_Shortcode_Dispatcher();
	}

	static public function register_handler($class) {
		self::$dispatcher->register_handler($class);
	}

	public function attach_handler(PL_Shortcode_Handler $handler) {
		self::$dispatcher->attach_handler(get_class($handler), $handler);
	}

	public function detach_handler(PL_Shortcode_Handler $handler) {
		self::$dispatcher->detach_handler(get_class($handler), $handler);
	}
}


class PL_Shortcode_Context extends PL_Shortcode_System {
	protected $handler;

	public function __construct(PL_Shortcode_Handler $handler) {
		$this->attach_handler($this->handler = $handler);
	}

	public function __destruct() {
		$this->detach_handler($this->handler);
	}
}
