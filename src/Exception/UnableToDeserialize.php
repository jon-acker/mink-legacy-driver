<?php

namespace carlosV2\LegacyDriver\Exception;

final class UnableToDeserialize extends \RuntimeException
{
    /**
     * @param string $serializedData
     */
    public function __construct($serializedData)
    {
        parent::__construct(sprintf('Unable to deserialize `%s`.', $serializedData));
    }
}
