<?php

namespace Probuild\Command;

use Probuild\Config;
use Probuild\Shell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LinkCommand extends Command
{

    /** @var  Shell\Directory */
    protected $directoryShell;
    /** @var Shell\Link */
    protected $linkShell;

    /**
     * @author Cristian Quiroz <cris@qcas.co>
     */
    protected function configure()
    {
        $this->setName('link')
            ->setDescription('Links target paths and post composer paths to target dir.')
            ->addArgument('config', InputArgument::OPTIONAL, 'Yaml config file with build settings. If not defined, config.yaml will be tried.')
            ->addOption('test', 't', InputOption::VALUE_NONE, 'If set, no commands will be executed.')
            ->addOption('cp-command', 'c', InputOption::VALUE_REQUIRED, 'If set, overrides `cp` command.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     * @author Cristian Quiroz <cris@qcas.co>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getArgument('config');
        if (!$configFile) {
            $configFile = './config.yaml';
        }
        $config = new Config($configFile);

        //Prepare shell
        if ($input->getOption('test')) {
            $this->enableTestMode();
        }

        //Use cp and composer if set
        if ($cpCommand = $input->getOption('cp-command')) {
            $this->getLinkShell()->setCpCommand($cpCommand);
            $this->getDirectoryShell()->setCpCommand($cpCommand);
        }

        //Prepare Shells
        $this->setShellOutput($output);

        //Create main links
        $output->writeln("\n<comment>## Creating links to target directory ##</comment>");
        $this->getLinkShell()->createLinks($config->getDirectoryPaths(), $config->getTargetDirectory());

        //Create post composer links
        if (count($config->getPostComposerDirectoryPaths()) > 0) {
            $output->writeln("\n<comment>## Creating post composer links to target directory ##</comment>");
            $this->getLinkShell()->createLinks(
                $config->getPostComposerDirectoryPaths(),
                $config->getTargetDirectory()
            );
        }

        //Clean up target directory
        $output->writeln("\n<comment>## Cleaning up target directory ##</comment>");
        $this->getDirectoryShell()->cleanup($config->getTargetDirectory());
    }

    /**
     * @return MakeCommand
     * @author Cristian Quiroz <cris@qcas.co>
     */
    public function enableTestMode()
    {
        $this->getDirectoryShell()->enableTestMode();
        $this->getLinkShell()->enableTestMode();

        return $this;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return MakeCommand
     * @author Cristian Quiroz <cris@qcas.co>
     */
    public function setShellOutput(OutputInterface $output)
    {
        $this->getDirectoryShell()->setOutput($output);
        $this->getLinkShell()->setOutput($output);

        return $this;
    }

    /**
     * @return Shell\Directory
     * @author Cristian Quiroz <cris@qcas.co>
     */
    public function getDirectoryShell()
    {
        return $this->directoryShell;
    }

    /**
     * @param Shell\Directory $directoryShell
     * @author Cristian Quiroz <cris@qcas.co>
     * @return MakeCommand
     */
    public function setDirectoryShell($directoryShell)
    {
        $this->directoryShell = $directoryShell;

        return $this;
    }

    /**
     * @return Shell\Link
     * @author Cristian Quiroz <cris@qcas.co>
     */
    public function getLinkShell()
    {
        return $this->linkShell;
    }

    /**
     * @param Shell\Link $linkShell
     * @author Cristian Quiroz <cris@qcas.co>
     * @return MakeCommand
     */
    public function setLinkShell($linkShell)
    {
        $this->linkShell = $linkShell;

        return $this;
    }
}
