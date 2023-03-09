<?php

declare(strict_types=1);

namespace Dojo\Filter;

use Laminas\Filter\FilterInterface;

use const ARRAY_FILTER_USE_BOTH;

use function array_pop;
use function count;

class UploaderFilter implements FilterInterface
{
    /** @param $files should be in the form of ['fieldset_name']['images'] */
    public function filter($files): array
    {
        return array_pop($files);
        return $files;
    }
}
