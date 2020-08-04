<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\System\SystemConfig\Exception\XmlParsingException;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyManifestCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareSaasStyle($input, $output);

        /** @var array<string> $manifestPaths */
        $manifestPaths = $input->getArgument('manifests');

        $invalidCount = 0;

        foreach ($manifestPaths as $manifestPath) {
            try {
                Manifest::createFromXmlFile($manifestPath);
            } catch (XmlParsingException $e) {
                $io->error($e->getMessage());
                ++$invalidCount;
            }
        }
        if ($invalidCount > 0) {
            return 1;
        }
        $io->success('all files valid');

        return 0;
    }

    protected function configure(): void
    {
        $this->setName('app:verify')
            ->setDescription('checks manifests for errors')
            ->addArgument('manifests', InputArgument::IS_ARRAY, 'The paths of the manifest file');
    }
}
