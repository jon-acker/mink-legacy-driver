<?php

namespace Jacker\LegacyDriver\Runner;

use Jacker\LegacyDriver\Serializer;
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
     * @var LegacyApp
     */
    private $legacyApp;

    /**
     * @param Serializer $serializer
     * @param LegacyApp  $legacyApp
     */
    public function __construct(Serializer $serializer, LegacyApp $legacyApp)
    {
        parent::__construct();

        $this->serializer = $serializer;
        $this->legacyApp = $legacyApp;
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
                'The request serialized and encoded on base64.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $request = $this->serializer->deserialize($input->getArgument('request'));

        $this->legacyApp->handle($request);
    }
}
