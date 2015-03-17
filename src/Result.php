<?php

namespace Mkjp\Option;


/**
 * Class representing a result that can be a success or an error
 */
class Result implements \IteratorAggregate {
    private $value = null;
    private $error = null;
    
    /**
     * Private constructor to force instantiation through factory methods
     */
    private function __construct($value, \Exception $error) {
        $this->value = $value;
        $this->error = $error;
    }
    
    /**
     * Returns the result of applying $f to the value of this result if it is successful
     * 
     * $f should take the wrapped value and return a result
     */
    public function andThen(callable $f) {
        if( $this->value !== null ) return $f($this->get());
        if( $this->error !== null ) return $this;
        throw new \LogicException("Value and error cannot both be null");
    }
    
    /**
     * Returns the value of the result if it is a success
     * 
     * If it is an error, the exception it was created with is thrown
     */
    public function get() {
        if( $this->value !== null ) return $this->value;
        if( $this->error !== null ) throw $this->error;
        throw new \LogicException("Value and error cannot both be null");
    }
    
    /**
     * Returns the error if the result is an error
     * 
     * If the result is not an error, a \LogicException is thrown
     */
    public function getError() {
        if( $this->error === null )
            throw new \LogicException("Tried to retrieve error from a success");
        return $this->error;
    }
    
    /**
     * Returns an iterator for this result
     * 
     * If this result is a success, the iterator will produce the single value
     * If this result is an error, it will be an empty iterator
     */
    public function getIterator() {
        if( $this->value !== null ) yield $this->value;
    }
    
    /**
     * Returns the result's value if it is a success, $default otherwise
     */
    public function getOrDefault($default) {
        return $this->value !== null ? $this->value : $default;
    }
    
    /**
     * Returns the result's value if it is a success, otherwise the result of evaluating
     * $f with the error
     * 
     * $f should take an exception and return a value
     */
    public function getOrElse(callable $f) {
        if( $this->value !== null ) return $this->value;
        if( $this->error !== null ) return $f($this->error);
        throw new \LogicException("Value and error cannot both be null");
    }
    
    /**
     * Returns the result's value if it is a success, null otherwise
     */
    public function getOrNull() {
        return $this->value;
    }
    
    /**
     * Returns true if this result represents an error, false otherwise
     */
    public function isError() {
        return $this->error !== null;
    }
    
    /**
     * Returns true if this result represents a success, false otherwise
     */
    public function isSuccess() {
        return $this->value !== null;
    }
    
    /**
     * Returns a result containing the result of applying $f to this result's value
     * if it is a success
     * 
     * $f should take a value and return a new value
     */
    public function map(callable $f) {
        if( $this->value !== null ) return Result::success($f($this->value));
        if( $this->error !== null ) return $this;
        throw new \LogicException("Value and error cannot both be null");
    }
    
    /**
     * Returns this result if it is a success, else the result of evaluating $f with
     * the error
     * 
     * $f should take an exception and return a new result
     */
    public function orElse(callable $f) {
        if( $this->value !== null ) return $this;
        if( $this->error !== null ) return $f($this->error);
        throw new \LogicException("Value and error cannot both be null");
    }
    
    /**
     * Converts this result to an option
     * 
     * If this is a success, it returns an Option::just
     * If this is an error, it returns Option::none
     */
    public function toOption() {
        return Option::from($this->value);
    }
    
    /**
     * Get an error result containing the given, non-null exception
     */
    public static function error(\Exception $error) {
        return new Result(null, $error);
    }
    
    /**
     * Get a successful result containing the given, non-null value
     */
    public static function success($x) {
        if( $x === null )
            throw new \LogicException("Cannot create a success with null value");
        
        return new Result($x, null);
    }
    
    /**
     * Tries to execute the given function and returns a result representing the
     * outcome, i.e. if the function returns normally, a successful result is
     * returned containing the return value and if the function throws an exception,
     * an error result is returned containing the error
     * 
     * $f should take no arguments and return a value that will be wrapped in a
     * success if the computation completes successfully
     * 
     * The _ is required if we want to use the word try since it is keyword
     */
    public static function _try(callable $f) {
        try {
            return Result::success($f());
        }
        catch( \Exception $error ) {
            return Result::error($error);
        }
    }
}
