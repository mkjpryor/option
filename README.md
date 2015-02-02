# mkjpryor/option #

Simple option and result classes for PHP, inspired by Scala's Option, Haskell's Maybe and Rust's Result.

The Option type provides additional functionality over nullable types for operating on values that may or may not be present.

The Result type also includes a reason for the failure (an exception), and allows errors to be propagated without worrying about specifically handling them.


## Installation ##

`mkjpryor/option` can be installed via [Composer](https://getcomposer.org/):

```bash
php composer.phar require mkjpryor/option dev-master
```


## Usage ##

### Creating an Option ###

```
<?php

use Mkjp\Option\Option;

// Creates a non-empty option containing the value 10
$o1 = Option::just(10);

// Creates an empty option
$o2 = Option::none();

// Creates an option from a nullable value
//   If null is given, an empty option is created
$o3 = Option::from(10);
$o3 = Option::from(null);
```

### Creating a Result ###

```
<?php

use Mkjp\Option\Result;

// Create a successful result with the given value
$r1 = Result::success(42);

// Create an errored result with the given error
$r2 = Result::error(new \Exception("Some error occurred"));

// Create a result by trying some operation that might fail
//   Creates a success if the function returns successfully
//   Creates an error if the function throws an exception
$r3 = Result::try_(() => {
    // Some operation that might fail with an exception
});
```

### Retrieving a value from an Option (or Result) ###

The underlying value can be retrieved from an `Option` in an unsafe or safe manner (N.B. the same methods are available for retrieving a value from a `Result`):

```
<?php

// UNSAFE - throws a LogicException if the option is empty
$val = $opt->get();

// Returns the Option's value if it is non-empty, 0 otherwise
$val = $opt->getOrDefault(0);  

// Returns the Option's value if it is non-empty, otherwise the result of evaluating the given function
//   Useful if the default value is expensive to compute
$val = $opt->getOrElse(function() { return 0; });  

// Return the Option's value if it is non-empty, null otherwise
$val = $opt->getOrNull();
```

### Manipulating options and results ###

`Option`s and `Result`s have several methods for manipulating them in an 'empty-safe' manner, e.g. `map`, `filter`. See the code for more details.


## Examples ##

In the following example, we want to retrieve a user by id from the database and welcome them. If the user does not exist, we want to welcome them as a guest.

```
<?php

use Mkjp\Option\Option;
use Mkjp\Option\Result;


/**
 * Simple user class
 */
class User {
    public $id;
    public $username;
    public function __construct($id, $username) {
        $this->id = $id;
        $this->username = $username;
    }
}

/**
 * Fetches a user from a database by their id
 *
 * Note how we return a Result that may contain an Option
 * This is because there are three possible outcomes that are semantically different:
 *   1. We successfully find a user
 *   2. The user doesn't exist in the database (this isn't an error - it is expected and must be handled)
 *   3. There is an error querying the database
 */
function findUserById($id) {
    // Assume DB::execute throws a DBError if there is an error while querying
    $result = Result::try_(function() use($id) {
        return DB::execute("SELECT * FROM users WHERE id = ?", $id);
    });
    
    // Use the error propagation to our advantage
    return $result->map(function($data) {
        if( count($data) > 0 ) {
            return Option::just(new User($data[0]["id"], $data[0]["username"]));
        }
        else {
            return Option::none();
        }
    });
}

$id = 1234;  // This would come from request params or something similar
    
// Print an appropriate welcome message for the user
echo "Hello, " . findUserById($id)
                     // In our circumstances, a DB error is like not finding a user
                     ->orElse(function($e) {
                         return Result::success(Option::none());
                     })
                     // Get the username from the optional user
                     ->map(function($o) {
                         return $o->map(function($u) { return $u->username; })
                                  ->getOrDefault("Guest")
                     })
                     // We can safely use get since we know it won't be an error
                     ->get();
```

## License ##

This code is licensed under the terms of the MIT licence.
