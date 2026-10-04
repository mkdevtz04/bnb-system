<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a stay cannot be held: the dates are taken, blocked, or the party
 * is too large. Carries a message safe to show the guest.
 */
class DatesUnavailableException extends Exception {}
