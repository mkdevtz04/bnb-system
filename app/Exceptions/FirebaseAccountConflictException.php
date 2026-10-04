<?php

namespace App\Exceptions;

use Exception;

/**
 * The token verified, but it cannot be attached to a local account — typically
 * because an account already holds that email address and the provider did not
 * verify the address.
 */
class FirebaseAccountConflictException extends Exception {}
