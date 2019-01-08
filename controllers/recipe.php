<?php

use Kirby\Http\Header;

return function($page) {

    if (!$page->userHasAccess()) {
        // Render the default error page, whenever a non-authorized
        // user tries to access a private recipe page and quit.
        Header::notfound();
        echo site()->errorPage()->render();
        exit;
    }

    return [];
};
