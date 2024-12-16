<?php

namespace zennit\ABAC\Contracts;

use zennit\ABAC\DTO\AttributeCollection;

interface AttributeLoaderInterface
{
    public function loadAttributes(object $subject, string $resource): AttributeCollection;
}
