<?php

namespace DJB\Guard;

class Guard {

	/**
	 * The variable that is being guarded against
	 * 
	 * @var mixed
	 */
	protected $value;

	/**
	 * Whether the guard has found an issue yet or not
	 * 
	 * @var boolean
	 */
	protected $hasIssue = false;

	/**
	 * Description of issue found
	 * 
	 * @var string
	 */
	protected $issue;

	/**
	 * Description of the exceptions raised
	 *
	 * In use via the raises() function
	 * 
	 * @var array
	 */
	protected $guardExceptionMessages = [
		'exists' => 'The variable does not exist.',
		'true' => 'The variable provided did not evaluate to true.',
		'false' => 'The variable provided did not evaluate to false.',
		'is between' => 'The variable provided does not fall between the required boundaries.',
		'is class' => 'The variable is not an instance of the required class.',
		'is array' => 'The variable provided is not an array.',
		'is date' => 'The variable provided is not a date.',
		'is before' => 'The variable provided does not fall before the required boundary.',
		'is after' => 'The variable provided does not fall after the required boundary.',
		'is alpha' => 'The variable provided is not comprised entirely of alpha characters.',
		'is numeric' => 'The variable provided is not comprised entirely of numeric characters.',
		'is length' => 'The variable provided is not the required length.',
		'is url' => 'The variable provided is not a url.',
		'is active url' => 'The variable provided is not an active url.',
		'is in' => 'The variable provided is not one of the suitable values.',
		'is not excluded' => 'The variable provided is an excluded value.',
		'is json' => 'The variable provided is not valid json.',
		'is ip' => 'The variable provided is not an IP address.',
		'is integer' => 'The variable provided is not an integer.',
		'is string' => 'The variable provided is not a string.',
		'is email' => 'The variable provided is not an email.',
		'is less than' => 'The variable provided is not less than the required boundary.',
		'is greater than' => 'The variable provided is not more than the required boundary.',
		'equal or less than' => 'The variable provided is not less or equal to the required boundary.',
		'equal or greater than' => 'The variable provided is not more or equal to the required boundary.'
	];

	function __construct($value) {
		$this->value = $value;
		// Merge any custom exception messages provided in sub classes
		// Will allow for overriding, as same keys use the latter array's version
		if (property_exists($this, 'exceptionMessages')) $this->guardExceptionMessages = array_merge($this->guardExceptionMessages, $this->exceptionMessages);
	}

	/**
	 * Catch all the check method calls
	 *
	 * Should be called before all functions bar resolving functions (passes and otherwise)
	 * Used to perform the alreadyHasIssue check in one place, not in every check function
	 *  
	 * @param  string  $method
	 * @param  array   $args
	 * @throws \Exception
	 * @return mixed
	 */
	public function __call($method, $args) {
		if ( ! $this->canCall($method)) {
			throw new \Exception("Guard check {$method} method does not exist");
			return;
		}
		if ($this->alreadyHasIssue()) return $this;
		call_user_func_array(array($this, $method), $args);
		return $this;
	}

	/**
	 * Checks whether a method can be called on the class
	 * 
	 * @param  string   $method
	 * @return boolean
	 */
	protected function canCall($method) {
		return method_exists($this, $method);
	}

	/**
	 * Attempt to make a date with the variable provided, or the current variable if not specified
	 *
	 * @return mixed  DateTime/bool
	 */
	protected function makeDate($date = null) {
		try {
			$result = new \DateTime(is_null($date) ? $this->value : $date, $this->getTimeZone());
		} catch (\Exception $error) {
			return false;
		}
		return $result;
	}

	/**
	 * Create a DateTimeZone based on the website's default
	 * 
	 * @return DateTimeZone
	 */
	protected function getTimeZone() {
		return new \DateTimeZone(date_default_timezone_get());
	}

	/**
	 * Checks whether the guard has already found an issue or not
	 * 
	 * @return boolean
	 */
	protected function alreadyHasIssue() {
		return $this->hasIssue;
	}

	/**
	 * Designate that an issue has been found
	 * 
	 * @param string  $issue
	 */
	protected function setIssue($issue) {
		$this->hasIssue = true;
		$this->issue = $issue;
	}

	/**
	 * Run a check that the variable 'exists'
	 *
	 * Could maybe do with a rename, really just checking if var has length or is a bool or array
	 *
	 * Will fail if sent a class that does not implement __toString
	 */
	protected function exists() {
		$boolOrArray = is_bool($this->value) || is_array($this->value);
		if ( ! $boolOrArray && trim((string) $this->value) === '') $this->setIssue('exists');
	}

	/**
	 * Run a check that the variable resolves to true
	 */
	protected function isTrue() {
		if ( ! is_bool($this->value) || ! $this->value) $this->setIssue('true');
	}

	/**
	 * Run a check that the variable resolves to false
	 */
	protected function isFalse() {
		if ( ! is_bool($this->value) || $this->value) $this->setIssue('false');
	}

	/**
	 * Run a check that the variable is of type of class
	 * 
	 * @param string  $className
	 */
	protected function isClass($className) {
		if ( ! $this->value instanceof $className) $this->setIssue('is class');
	}

	/**
	 * Run a check that the variable is an array
	 */
	protected function isArray() {
		if ( ! is_array($this->value)) $this->setIssue('is array');
	}

	/**
	 * Run a check that the variable is a date
	 */
	protected function isDate() {
		$date = $this->makeDate();
		if ( ! $date) $this->setIssue('is date');
	}

	/**
	 * Run a check that the variable is before the date provided
	 * 
	 * @param string  $date
	 */
	protected function before($date) {
		if ( ! $date instanceof \DateTime) {
			$date = $this->makeDate($date);
		}
		$var_date = $this->makeDate();
		// check for either date not being created successfully
		if ( ! $var_date || ! $date) {
			$this->setIssue('is date');
			return;
		}
		if ($var_date >= $date) $this->setIssue('is before');
	}

	/**
	 * Run a check that the variable is after the date provided
	 * 
	 * @param string  $date
	 */
	protected function after($date) {
		if ( ! $date instanceof \DateTime) {
			$date = $this->makeDate($date);
		}
		$var_date = $this->makeDate();
		// check for either date not being created successfully
		if ( ! $var_date || ! $date) {
			$this->setIssue('is date');
			return;
		}
		if ($var_date <= $date) $this->setIssue('is after');
	}

	/**
	 * Run a check if the variable is between two figures (can be dates/numbers)
	 * 
	 * @param  mixed  $min
	 * @param  mixed  $max
	 */
	protected function between($min, $max) {
		$_min = $min;
		$_max = $max;
		if (($min instanceof \DateTime || $min = $this->makeDate($min)) && ($max instanceof \DateTime || $max = $this->makeDate($min))) {
			$date = $this->makeDate();
			if ( ! $date) {
				$this->setIssue('is date');
				return;
			}
			if ($min >= $date || $max <= $date) $this->setIssue('is between');
		} else {
			if ($_min >= $this->value || $_max <= $this->value) $this->setIssue('is between');
		}
	}

	/**
	 * Run a check that the variable contains only alpha characters
	 */
	protected function isAlpha() {
		if (ctype_alpha($this->value)) $this->setIssue('is alpha');
	}

	/**
	 * Run a check that the variable is numeric
	 */
	protected function isNumeric() {
		if ( ! is_numeric($this->value)) $this->setIssue('is numeric');
	}

	/**
	 * Run a check that the length of the variable matches a certain number
	 * 
	 * @param int  $length
	 */
	protected function length($length) {
		if (strlen($this->value) !== $length) $this->setIssue('is length');
	}

	/**
	 * Run a check that the variable represents a URL
	 */
	protected function isURL() {
		if ( ! filter_var($this->value, FILTER_VALIDATE_URL)) $this->setIssue('is url');
	}

	/**
	 * Run a check if the variable is an URL that is active
	 */
	protected function isActiveURL() {
		if ( ! checkdnsrr($this->value)) $this->setIssue('is active url');
	}

	/**
	 * Run a check that the variable is one of the provided vars
	 * 
	 * @param array  $acceptable
	 */
	protected function in(Array $acceptable) {
		if ( ! in_array($this->value, $acceptable)) $this->setIssue('is in');
	}

	/**
	 * Run a check that the variable is not one of the provided vars
	 * 
	 * @param array  $exclusions
	 */
	protected function notIn(Array $exclusions) {
		if (in_array($this->value, $exclusions)) $this->setIssue('is not excluded');
	}

	/**
	 * Run a check that the variable is valid JSON
	 */
	protected function isJSON() {
		json_decode($this->value);
 		if (json_last_error() !== JSON_ERROR_NONE) $this->setIssue('is json');
	}

	/**
	 * Run a check that the variable is a valid IP address
	 */
	protected function isIP() {
		if ( ! filter_var($this->value, FILTER_VALIDATE_IP)) $this->setIssue('is ip');
	}

	/**
	 * Run a check that the variable is an integer
	 */
	protected function isInteger() {
		if ( ! is_int($this->value)) $this->setIssue('is integer');
	}

	/**
	 * Run a check that the variable is a string
	 */
	protected function isString() {
		if ( ! is_string($this->value)) $this->setIssue('is string');
	}

	/**
	 * Run a check that the variable is an email address
	 */
	protected function isEmailAddress() {
		if ( ! filter_var($this->value, FILTER_VALIDATE_EMAIL)) $this->setIssue('is email');
	}

	/**
	 * Run a check to determine that the variable is equal to the provided match
	 * 
	 * @param mixed  $match
	 */
	protected function equal($match) {
		$_match = $match;
		if ($match instanceof \DateTime || $match = $this->makeDate($match)) {
			$date = $this->makeDate();
			if ( ! $date) {
				$this->setIssue('is date');
				return;
			}
			if ($date < $match || $date > $match) $this->setIssue('equal');
		} else {
			if ($this->value !== $_match) $this->setIssue('equal');
		}
	}

	/**
	 * Run a check to determine that the variable is less than the provided match
	 * 
	 * @param mixed  $match
	 */
	protected function lessThan($match) {
		if ( ! is_numeric($match) || ! is_numeric($this->value) || $this->value >= $match) $this->setIssue('less than');
	}

	/**
	 * Run a check to determine that the variable is equal or less than the provided match
	 * 
	 * @param mixed  $match
	 */
	protected function equalOrLessThan($match) {
		if ( ! is_numeric($match) || ! is_numeric($this->value) || $this->value > $match) $this->setIssue('equal or less than');
	}

	/**
	 * Run a check to determine that the variable is more than the provided match
	 * 
	 * @param mixed  $match
	 */
	protected function greaterThan($match) {
		if ( ! is_numeric($match) || ! is_numeric($this->value) || $this->value <= $match) $this->setIssue('greater than');
	}

	/**
	 * Run a check to determine that the variable is equal or more than the provided match
	 * 
	 * @param mixed  $match
	 */
	protected function equalOrGreaterThan($match) {
		if ( ! is_numeric($match) || ! is_numeric($this->value) || $this->value < $match) $this->setIssue('equal or greater than');
	}

	/**
	 * If the guard found an issue, call a callback to handle it
	 * 
	 * @param Closure  $callback
	 */
	public function otherwise($callback = null) {
		if (is_null($callback) || ! is_callable($callback)) throw new \Exception('Guard callback is not a function');
		if ($this->hasIssue) $callback($this->issue);
	}

	/**
	 * Check whether the guard has passed all checks successfully or not
	 * 
	 * @return boolean
	 */
	public function passes() {
		return ! $this->hasIssue;
	}

	public function raises() {
		if ($this->hasIssue) {
			throw new \Exception($this->guardExceptionMessages[$this->issue]);
		}
	}
	
}