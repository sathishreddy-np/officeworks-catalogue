<?php

// This file is PHP. The tag above starts the PHP program.

// A namespace is the folder address of this class.
// It stops this name clashing with a class of the same name in another folder.
namespace App\Catalogue;

// RuntimeException is PHP's built-in "something went wrong while the program was running" error.
use RuntimeException;

// This is our own error type for "the product list could not be loaded".
// Controllers catch this name and show a retry message instead of a crash page.
class CatalogueUnavailable extends RuntimeException
{
    // The class body is empty on purpose.
    // It inherits the message and the previous error from RuntimeException.
}
