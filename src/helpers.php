<?php

/**
 * Create new instance of \DJB\Guard\Guard for use in chaining
 * 
 * @param  mixed $value
 * @return \DJB\Guard\Guard
 */
function guard($value) {
	return (new \DJB\Guard\Guard($value));
}