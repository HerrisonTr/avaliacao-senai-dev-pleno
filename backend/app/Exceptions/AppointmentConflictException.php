<?php

namespace App\Exceptions;

use RuntimeException;

class AppointmentConflictException extends RuntimeException
{
    /**
     * @param  list<array{id: int, name: string}>  $alternativeAttendants
     */
    public function __construct(
        string $message,
        public readonly array $alternativeAttendants = [],
    ) {
        parent::__construct($message);
    }
}
