<?php

namespace carlosV2\LegacyDriver;

use carlosV2\LegacyDriver\Exception\UnableToDeserialize;

final class Serializer
{
    /**
     * @param mixed $data
     *
     * @return string
     */
    public function serialize($data)
    {
        return base64_encode(serialize($data));
    }

    /**
     * @param string $encodedData
     *
     * @return mixed
     *
     * @throws UnableToDeserialize
     */
    public function deserialize($encodedData)
    {
        $serializedData = base64_decode($encodedData);
        if ($serializedData === false) {
            throw new UnableToDeserialize($encodedData);
        }

        $data = @unserialize($serializedData);
        if ($data === false) {
            throw new UnableToDeserialize($encodedData);
        }

        return $data;
    }
}
