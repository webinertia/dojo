<?php

declare(strict_types=1);

namespace Dojo\View;

interface DojoInterface
{
    /**
     * Base path to AOL CDN
     */
    public const CDN_BASE_AOL = 'http://o.aolcdn.com/dojo/';

    /**
     * Path to dojo on AOL CDN (following version string)
     */
    public const CDN_DOJO_PATH_AOL = '/dojo/dojo.xd.js';

    /**
     * Base path to Google CDN
     */
    public const CDN_BASE_GOOGLE = 'http://ajax.googleapis.com/ajax/libs/dojo/';

    /**
     * Path to dojo on Google CDN (following version string)
     */
    public const CDN_DOJO_PATH_GOOGLE = '/dojo/dojo.xd.js';
}
