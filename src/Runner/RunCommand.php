<?php

namespace carlosV2\LegacyDriver\Runner;

use carlosV2\LegacyDriver\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RunCommand extends Command
{
    const NAME = 'run';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        parent::__construct();

        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Executes the legacy application.')
            ->addArgument(
                'request',
                InputArgument::REQUIRED,
                'The request serialized and encoded.'
            )
            ->addArgument(
                'legacy_app_builder',
                InputArgument::REQUIRED,
                'The legacy app builder serialized and encoded.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $request = $this->serializer->deserialize($input->getArgument('request'));

        $legacyAppBuilder = $this->serializer->deserialize($input->getArgument('legacy_app_builder'));
        $legacyAppBuilder->build()->handle($request);
    }
}
