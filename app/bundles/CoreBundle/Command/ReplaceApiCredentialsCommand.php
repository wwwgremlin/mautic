<?php

/*
 * @author      Sergey S. Nezymaev (Pegasus)
 *
 */

namespace Mautic\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Mautic\ApiBundle\Entity\oAuth2\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command to replace all API credentials Client and tokens to integration one.
 */
class ReplaceApiCredentialsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mautic:api:replace_credentials')
            ->setDescription('Replace API credentials with integration ones')
            ->setDefinition([
                new InputOption(
                    'client_id', null, InputOption::VALUE_REQUIRED,
                    'Integration Client ID'
                ),
                new InputOption(
                    'client_secret', null, InputOption::VALUE_REQUIRED,
                    'Integration Secret'
                )
            ])
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command change all API credentials to integration ones.

<info>php %command.full_name%</info>

You must specify the client_id of the credentials via the --client_id parameter and client secret via --client_secret parameter:

<info>php %command.full_name% --client_id=<client_id> --client_secret=<client_secret>

NOTE: Client ID of Mautic format is <ID>_CLIENT_ID, where ID is sequential number from Credentials record. So use as --client_id
parameter with no this prefix (<ID>_)
</info>

EOT
);
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options       = $input->getOptions();
        $client_id     = $options['client_id'];
        $secret        = $options['client_secret'];

        $output->writeln("\n\n<info>Change all credentials to $client_id and  $secret.</info>");

        $em = $this->getContainer()->get('doctrine')->getManager();
        $apiCredentialsRepo = $em->getRepository(Client::class);
        $allClients = $apiCredentialsRepo->findAll();

        $cnt = count($allClients);
        $output->writeln("\n<info>Count is $cnt</info>");
        foreach ($allClients as $client) {
            $output->writeln("\n<info>Updating for " . $client->getName() . ' (' . $client->getId() . ") ...</info>");
            $client->setRandomId($client_id);
            $client->setSecret($secret);
            $em->persist($client);
        }

        $em->flush();

        return 0;
    }
}
