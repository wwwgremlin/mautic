<?php

/*
 * @author      Sergey S. Nezymaev (Pegasus)
 *
 */

namespace Mautic\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Mautic\UserBundle\Entity\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * CLI Command to replace all API credentials Client and tokens to integration one.
 */
class UpdateAdminPasswordCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {

        $this->setName('mautic:user:change_admin_password')
            ->setDescription('Change password for admin (first) user')
            ->setDefinition([
                new InputOption(
                    'password', null, InputOption::VALUE_OPTIONAL,
                    'New Admin Password (default is FooBar(9)?', 'FooBar(9)?'
                )
            ])
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command change admin password (for user with ID = 1) to new one.

<info>php %command.full_name%</info>

You may specify the ne password via the --password parameter:

<info>php %command.full_name% --password=<password>

NOTE: Default Password value is FooBar(9)?
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
        $password     = $options['password'];

        $output->writeln("\n\n<info>Change password for user with ID = 1</info>");

        $em = $this->getContainer()->get('doctrine')->getManager();
        $adminUser = $em->getRepository('MauticUserBundle:User')->find(1);
        $encoder = $this->getContainer()->get('security.password_encoder');
        $adminUser->setPassword($encoder->encodePassword($adminUser, $password));

//         $userRepository = $em->getRepository(User::class);

        $em->persist($adminUser);
        $em->flush();

        $output->writeln("\n\n<info>User with ID = 1 updated</info>");
        return 0;
    }
}
