<?php

namespace Mkjp\Option;


/**
 * Class representing a value that can be present or not
 */
final class Option implements \IteratorAggregate {
    private $value;
    
    /**
     * Private constructor to force instantiation through factory methods
     */
    private function __construct($value) {
        $this->value = $value;
    }
    
    /**
     * Returns the result of applying $f to the value of this option if it is non-empty
     * 
     * $f should take the wrapped value and return another option
     */
    public function andThen(callable $f) {
        return !$this->isEmpty() ? $f($this->get()) : Option::none();
    }
    
    /**
     * Returns this option if it is non-empty and $p returns true
     * 
     * $p should take the wrapped value and return a boolean
     */
    public function filter(callable $p) {
        return !$this->isEmpty() && $p($this->get()) ? $this : Option::none();
    }
    
    /**
     * Returns the option's value
     * 
     * If the option is empty, a \LogicException is thrown
     * 
     * @throws \LogicException
     */
    public function get() {
        if( $this->value === null )
            throw new \LogicException("Cannot get value from empty option");
            
        return $this->value;
    }
    
    /**
     * Returns an iterator for this option
     * 
     * If this option is non-empty, the iterator will produce the single element
     * If this option is empty, it will be an empty iterator
     */
    public function getIterator() {
        if( !$this->isEmpty() ) yield $this->get();
    }
    
    /**
     * Returns the option's value if it is non-empty, $default otherwise
     */
    public function getOrDefault($default) {
        return $this->isEmpty() ? $default : $this->get();
    }
    
    /**
     * Returns the option's value if it is non-empty, otherwise the result of evaluating
     * $f
     * 
     * $f should take no arguments and return a value
     */
    public function getOrElse(callable $f) {
        return $this->isEmpty() ? $f() : $this->get();
    }
    
    /**
     * Returns the option's value if it is non-empty, null otherwise
     */
    public function getOrNull() {
        return $this->isEmpty() ? null : $this->get();
    }
    
    /**
     * Returns true if this option is empty, false otherwise
     */
    public function isEmpty() {
        return $this->value === null;
    }
    
    /**
     * Returns an option containing the result of applying $f to this option's value
     * if it is non-empty
     * 
     * $f should take the wrapped value and return a new value
     */
    public function map(callable $f) {
        return !$this->isEmpty() ? Option::just($f($this->get())) : Option::none();
    }
    
    /**
     * Returns this option if it is non-empty, otherwise the given option is returned
     */
    public function orElse(Option $other) {
        return !$this->isEmpty() ? $this : $other;
    }

    /**
     * Get an option from the given, possibly null, value
     */
    public static function from($x) {
        return new Option($x);
    }
    
    /**
     * Get an option containing the given, non-null value
     */
    public static function just($x) {
        if( $x === null )
            throw new \LogicException("Cannot create just from null value");
        
        return new Option($x);
    }
    
    /**
     * Get an empty option
     */
    public static function none() {
        return new Option(null);
    }
}
