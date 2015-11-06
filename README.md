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
  private function canFly() {
    if ( ! $this->value->canFly()) $this->setIssue('can fly');
  }
}
```

```php
guard($plane)->canFly()->otherwise(function() {
  throw new \Exception('The provided plane cannot fly!');
});
```

Note that to use the `guard()` function with your new class, you will need to modify the `helpers.php` file in the package to instantiate your new class instead of the package base class.

## syntax
The syntax is inspired by Swift 2's guard feature, to guard a simple variable with an exception you could use the `otherwise` method:

```php
function createBag($studs) {
  guard($studs)->greaterThan(5)->otherwise(function() {
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

## todo
Package specific error handling, pass the failed validation message to the `otherwise` function so you can call something similar to:

```php
guard($var)->exists()->otherwise(function($failedCheck) {
  throw \YourAppException\ValidationError($failedCheck->message());
});
```

Also need to add default check methods such as email validation.
