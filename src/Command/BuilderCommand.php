<?php
namespace AOE\Composer\Satis\Generator\Command;

use AOE\Composer\Satis\Generator\Builder\SatisBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuilderCommand extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Generate satis.json file from composer.json')
            ->addArgument(
                'composer',
                InputArgument::REQUIRED,
                'Path to composer.json file'
            )
            ->addArgument(
                'satis',
                InputArgument::REQUIRED,
                'Path to satis.json file'
            )
            ->addOption(
                'require-dev-dependencies',
                null,
                InputOption::VALUE_REQUIRED,
                'sets "require-dev-dependencies". 0=false 1=true'
            )
            ->addOption(
                'require-dependencies',
                null,
                InputOption::VALUE_REQUIRED,
                'sets "require-dependencies". 0=false 1=true'
            )
            ->addOption(
                'add-requirements',
                null,
                InputOption::VALUE_NONE,
                'sets "add-requirements"'
            )
            ->addOption(
                'add-dev-requirements',
                null,
                InputOption::VALUE_NONE,
                'sets "add-dev-requirements"'
            )
            ->addOption(
                'reset-requirements',
                null,
                InputOption::VALUE_NONE,
                'sets "reset-requirements"'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $satisFile = $input->getArgument('satis');
        if (false === file_exists($satisFile)) {
            throw new \InvalidArgumentException(sprintf('required file does not exists: "%s"', $satisFile), 1446115325);
        }
        $composerFile = $input->getArgument('composer');
        if (false === file_exists($composerFile)) {
            throw new \InvalidArgumentException(sprintf('required file does not exists: "%s"', $composerFile), 1446115336);
        }

        $satis = json_decode(file_get_contents($satisFile));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                sprintf(
                    'An error has occurred while decoding "%s". Error code: %s. Error message: "%s".',
                    $satisFile,
                    json_last_error(),
                    json_last_error_msg()
                ),
                1447257223
            );
        }

        $composer = json_decode(file_get_contents($composerFile));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                sprintf(
                    'An error has occurred while decoding "%s". Error code: %s. Error message: "%s".',
                    $composerFile,
                    json_last_error(),
                    json_last_error_msg()
                ),
                1447257260
            );
        }

        $builder = new SatisBuilder($composer, $satis);

        if ($input->getOption('reset-requirements')) {
            $builder->resetSatisRequires();
        }

        if (null !== $input->getOption('require-dependencies')) {
            $builder->setRequireDependencies($input->getOption('require-dependencies'));
        }

        if (null !== $input->getOption('require-dev-dependencies')) {
            $builder->setRequireDevDependencies($input->getOption('require-dependencies'));
        }

        if ($input->getOption('add-requirements')) {
            $builder->addRequiresFromComposer();
        }

        if ($input->getOption('add-dev-requirements')) {
            $builder->addDevRequiresFromComposer();
        }

        file_put_contents($satisFile, json_encode($builder->build(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
