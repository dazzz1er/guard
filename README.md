# guard
Swift 2 inspired guard for PHP to make simple validation more readable.

Will add more methods as and when I need extended functionality, or if requested - pull requests welcome as well!

## installation
`composer require djb/guard`

## suggested usage
Common validation required: email address, string, int, greaterthan, less than etc should be handled by the package.

After that I suggest extending the class to incorporate your own readable guard methods:

```php
class MyFlyingAppGuard extends \DJB\Guard\Guard {
  
  /**
   * This var allows you to describe your exceptions.
   * Should the raises() function be used, the exception raised will automatically contain the message associated with the relevant validation issue.
   */
  protected $exceptionMessages = [
   'can fly' => 'The provided object cannot fly!'
  ];

  private function canFly() {
    if ( ! $this->value->canFly()) $this->setIssue('can fly');
  }
}
```

In use:

```php
guard($plane)->canFly()->otherwise(function($error) {
  // $error holds the error description from which validation failed.. but as we are only checking one thing we don't need it!
  throw new \Exception('The provided plane cannot fly!');
});

guard($plane)->canFly()->raises();
// Results in Excepton with message: 'The provided object cannot fly!'
```

Note that to use the `guard()` function with your new class, you will need to modify the `helpers.php` file in the package to instantiate your new class instead of the package base class.

## syntax
The syntax is inspired by Swift 2's guard feature, to guard a simple variable with an exception you could use the `otherwise` method:

```php
function createBag($studs) {
  guard($studs)->greaterThan(5)->otherwise(function($error) {
  	  // $error will be 'equal or greater than'
	  throw new \Exception('There are not enough studs to make your gothic bag!');
  });
  return new Bag($studs);
}
```

or you can get a boolean pass or fail using `passes`:

```php
if (! guard($var)->isString()->equal('hello')->passes()) {
  return 'Error!';
}
```

or you can have guard raise an `Exception` for you with `raises`:

```php
$min  = '2015-10-25 00:00:00';
$max  = '2015-10-26 00:00:00';
$date = '2015-10-24 00:00:00';

// The below will raise an Exception as $date does not fall between $min and $max
guard($date)->between($min, $max)->raises();
// Results in Excepton with message: 'The variable provided does not fall between the required boundaries.'
```

All validation methods handled by guard have a built in validation error description like the one above.

You can override this by subclassing `\DJB\Guard\Guard`, as in the example in suggested usage, and providing the `$exceptionMessages` property with keys matching the error code you want to override. i.e:

```php
protected $exceptionMessages = ['is between' => 'Make it between 12 and 24, please'];
```

would override the `between($min, $max)` validation method's error message.

## validation methods

You can check if the variable:

* **`exists`** - exists (if the var has length, or is a `bool` or `array`)
* **`isTrue`** - evaluates to bool `true`
* **`isFalse`** - evaluates to bool `false`
* **`isDate`** - is in a valid date format
* **`isClass($className)`** - is an instance of class `$className`
* **`isArray`** - is an `array`
* **`isInteger`** - is an `integer`
* **`isString`** - is a `string`
* **`isAlpha`** - contains only alphabetic characters
* **`isNumeric`** - contains only numeric characters
* **`isEmailAddress`** - is in email address format
* **`isURL`** - is a URL
* **`isActiveURL`** - is an URL which can be reached (i.e. website is live)
* **`isJSON`** - is a valid JSON string
* **`isIP`** - is IP address format
* **`length($length)`** - is the length specified
* **`in(Array $acceptable)`** - is one of the accepted values
* **`notIn(Array $excluded)`** - is not one of the excluded values
* **`after($date)`** - is after the provided date (can be a date format string, or an instance of `DateTime`)
* **`before($date)`** - is before the provided date (can be a date format string, or an instance of `DateTime`)
* **`between($min, $max)`** - is between the `$min` and `$max` values (can be a date format string, instance of `DateTime` or numbers)
* **`equal($value)`** - is equal to the provided value (number, string)
* **`lessThan($value)`** - is less than the provided value (number)
* **`greaterThan($value)`** - is greater than the provided value (number)
* **`equalOrLessThan($value)`** - is equal or less than the provided value (number)
* **`equalOrGreaterThan($value)`** - is equal or greater than the provided value (number)
