<?php

namespace DJB\Guard;

class Guard {

	/**
	 * The variable that is being guarded against
	 * 
	 * @var mixed
	 */
	private $value;

	/**
	 * Whether the guard has found an issue yet or not
	 * 
	 * @var boolean
	 */
	private $hasIssue = false;

	/**
	 * Description of issue found
	 * 
	 * @var string
	 */
	private $issue;

	function __construct($value) {
		$this->value = $value;
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
	private function canCall($method) {
		return method_exists($this, $method);
	}

	/**
	 * Checks whether the guard has already found an issue or not
	 * 
	 * @return boolean
	 */
	private function alreadyHasIssue() {
		return $this->hasIssue;
	}

	/**
	 * Designate that an issue has been found
	 * 
	 * @param string  $issue
	 */
	private function setIssue($issue) {
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
	private function exists() {
		$boolOrArray = is_bool($this->value) || is_array($this->value);
		if ( ! $boolOrArray && trim((string) $this->value) === '') $this->setIssue('exists');
	}

	/**
	 * Run a check that the variable resolves to true
	 */
	private function isTrue() {
		if ( ! is_bool($this->value) || ! $this->value) $this->setIssue('true');
	}

	/**
	 * Run a check that the variable resolves to false
	 */
	private function isFalse() {
		if ( ! is_bool($this->value) || $this->value) $this->setIssue('false');
	}

	/**
	 * Run a check that the variable is of type of class
	 * 
	 * @param string   $className
	 */
	private function isClass($className) {
		if ( ! is_a($this->value, $className)) $this->setIssue('is class');
	}

	/**
	 * Run a check that the variable is numeric
	 */
	private function isNumeric() {
		if ( ! is_numeric($this->value)) $this->setIssue('is numeric');
	}

	/**
	 * Run a check that the variable is an integer
	 */
	private function isInteger() {
		if ( ! is_int($this->value)) $this->setIssue('is integer');
	}

	/**
	 * Run a check that the variable is a string
	 */
	private function isString() {
		if ( ! is_string($this->value)) $this->setIssue('is string');
	}

	/**
	 * Run a check to determine that the variable is equal to the provided match
	 * 
	 * @param mixed  $match
	 */
	private function equal($match) {
		if ( ! $this->value === $match) $this->setIssue('equal');
	}

	/**
	 * Run a check to determine that the variable is less than the provided match
	 * 
	 * @param mixed  $match
	 */
	private function lessThan($match) {
		if ( ! is_numeric($match) || ! is_numeric($this->value) || $this->value >= $match) $this->setIssue('less than');
	}

	/**
	 * Run a check to determine that the variable is equal or less than the provided match
	 * 
	 * @param mixed  $match
	 */
	private function equalOrLessThan($match) {
		if ( ! is_numeric($match) || ! is_numeric($this->value) || $this->value > $match) $this->setIssue('equal or less than');
	}

	/**
	 * Run a check to determine that the variable is more than the provided match
	 * 
	 * @param mixed  $match
	 */
	private function moreThan($match) {
		if ( ! is_numeric($match) || ! is_numeric($this->value) || $this->value <= $match) $this->setIssue('more than');
	}

	/**
	 * Run a check to determine that the variable is equal or more than the provided match
	 * 
	 * @param mixed  $match
	 */
	private function equalOrMoreThan($match) {
		if ( ! is_numeric($match) || ! is_numeric($this->value) || $this->value < $match) $this->setIssue('equal or more than');
	}

	/**
	 * If the guard found an issue, call a callback to handle it
	 * 
	 * @param Closure  $callback
	 */
	public function otherwise($callback = null) {
		if (is_null($callback) || ! is_callable($callback)) throw new \Exception('Guard callback is not a function');
		if ($this->hasIssue) $callback();
	}

	/**
	 * Check whether the guard has passed all checks successfully or not
	 * 
	 * @return boolean
	 */
	public function passes() {
		return ! $this->hasIssue;
	}

}