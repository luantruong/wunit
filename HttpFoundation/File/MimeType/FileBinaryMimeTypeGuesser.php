<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WUnit\HttpFoundation\File\MimeType;

use WUnit\HttpFoundation\File\Exception\FileNotFoundException;
use WUnit\HttpFoundation\File\Exception\AccessDeniedException;

/**
 * Guesses the mime type with the binary "file" (only available on *nix)
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony.com>
 */
class FileBinaryMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * Returns whether this guesser is supported on the current OS
     *
     * @return Boolean
     */
    static public function isSupported()
    {
        return !strstr(PHP_OS, 'WIN');
    }
    /**
     * Guesses the mime type of the file with the given path
     *
     * @see MimeTypeGuesserInterface::guess()
     */
    public function guess($path)
    {
        if (!is_file($path)) {
            throw new FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new AccessDeniedException($path);
        }

        if (!self::isSupported()) {
            return null;
        }

        ob_start();

        // need to use --mime instead of -i. see #6641
        passthru(sprintf('file -b --mime %s 2>/dev/null', escapeshellarg($path)), $return);
        if ($return > 0) {
            ob_end_clean();

            return null;
        }

        $type = trim(ob_get_clean());

        if (!preg_match('#^([a-z0-9\-]+/[a-z0-9\-]+)#i', $type, $match)) {
            // it's not a type, but an error message
            return null;
        }

        return $match[1];
    }
}
