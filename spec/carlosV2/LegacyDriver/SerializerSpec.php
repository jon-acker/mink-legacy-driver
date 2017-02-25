<?php

namespace spec\carlosV2\LegacyDriver;

use PhpSpec\ObjectBehavior;

class SerializerSpec extends ObjectBehavior
{
    function it_serializes_any_given_data()
    {
        $data = array('key' => 'value');

        $this->serialize($data)->shouldReturn(base64_encode(serialize($data)));
    }

    function it_deserializes_any_given_data()
    {
        $data = array('key' => 'value');

        $this->deserialize(base64_encode(serialize($data)))->shouldReturn($data);
    }

    function it_throws_an_exception_if_it_cannot_decode_the_data()
    {
        $this->shouldThrow('carlosV2\LegacyDriver\Exception\UnableToDeserializeException')
            ->duringDeserialize('!@£$');
    }

    function it_throws_an_exception_if_it_cannot_unserialize_the_decoded_data()
    {
        $this->shouldThrow('carlosV2\LegacyDriver\Exception\UnableToDeserializeException')
            ->duringDeserialize(base64_encode('!@£$'));
    }
}
