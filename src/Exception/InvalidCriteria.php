<?php

namespace Uelnur\SymfonyCriteriaRepository\Exception;

use Exception;
use Throwable;

class InvalidCriteria extends Exception {
    public function __construct(mixed $given, string $expected, ?Throwable $previous = null) {
        parent::__construct(sprintf('Invalid criteria. Expected %s, %s given', $expected, get_class($given)), -1, $previous);
    }
}
