<?php

declare(strict_types=1);

namespace Alaa\Eventslog\Console\Command;

use Magento\Framework\Event\Config\Reader;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Stdlib\ArrayUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LogCommand
 *
 * @package Alaa\Eventslog\Console\Command
 */
class LogCommand extends Command
{
    /**
     * @var File
     */
    protected $fileAdapter;
    /**
     * @var Reader
     */
    protected $eventConfigReader;
    /**
     * @var ArrayUtils
     */
    protected $arrayUtils;

    /**
     * LogCommand constructor.
     *
     * @param File $fileAdapter
     * @param Reader $eventConfigReader
     * @param ArrayUtils $arrayUtils
     */
    public function __construct(
        File $fileAdapter,
        Reader $eventConfigReader,
        ArrayUtils $arrayUtils
    ) {
        parent::__construct(null);
        $this->fileAdapter = $fileAdapter;
        $this->eventConfigReader = $eventConfigReader;
        $this->arrayUtils = $arrayUtils;
    }

    /**
     * @inheridoc
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('alaa:events:log');
        $this->setDescription('Log Observer Events');

        $this->addArgument('scope', InputArgument::OPTIONAL, 'Scope');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $areas = ['global', 'frontend', 'adminhtml', 'webapi_rest', 'webapi_soap', 'graphql'];
        $scope = $input->getArgument('scope');
        if (in_array($scope, $areas, true)) {
            $areas = [$scope];
        }

        $events = [];
        foreach ($areas as $area) {
            $data = $this->eventConfigReader->read($area);
            $events[] = array_keys($data);
        }

        if (!empty($events)) {
            $this->fileAdapter->write(
                'var/log/events.txt',
                implode(PHP_EOL, $this->arrayUtils->flatten($events))
            );
        }
    }
}
