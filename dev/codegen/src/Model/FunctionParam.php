<?php

declare(strict_types=1);

namespace Divengine\Codegen\Model;

/** A parameter of a database function. Unnamed parameters are named "paramN". */
final readonly class FunctionParam
{
    public function __construct(
        public string $name,
        public string $dataType,
        public string $mode,
    ) {
    }
}
