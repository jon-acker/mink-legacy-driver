<?php

namespace carlosV2\LegacyDriver\Exception;

use RuntimeException;

final class UnableToDeserializeException extends RuntimeException
{
    /**
     * @param string $serializedData
     */
    public function __construct($serializedData)
    {
        parent::__construct(sprintf('Unable to deserialize `%s`.', $serializedData));
    }
}
