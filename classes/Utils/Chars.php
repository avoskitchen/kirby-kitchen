<?php

namespace AvosKitchen\Kitchen\Utils;

/**
 * A collection of shared character collections and snippets
 * for regular expressions.
 */
class Chars {
    const SPACES = "\u{0020}\u{00a0}"; // regular and non-break space
    const REGEX_SPACES = '\x{0020}\x{00a0}'; // regular and non-breaking space
    const REGEX_PREFIXES = 'ca\.|~';
}
