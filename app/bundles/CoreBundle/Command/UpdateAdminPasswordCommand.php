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
            ->setDescription('Change password for user')
            ->setDefinition([
                new InputOption(
                    'password', null, InputOption::VALUE_OPTIONAL,
                    'New Admin Password', 'FooBar(9)?'
                ),
                new InputOption(
                    'user_id', null, InputOption::VALUE_OPTIONAL,
                    'ID of user which should be changed', '1'
                ),
                new InputOption(
                    'user_login', null, InputOption::VALUE_OPTIONAL,
                    'Login of user which should be changed', 'admin'
                ),
            ])
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command change admin password (for user specified user) to new one.

<info>php %command.full_name%</info>

You may specify the new password via the --password parameter:

<info>php %command.full_name% --password=<password> --user_id=1|--user_login=admin

NOTE: Default Password value is 'FooBar(9)?'
--user_id - ID of user to change password. Default is 1
--user_login - Login of user to change password. Default is 'admin'

NOTE: ID parameter will be used first to find user to change password
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
        $password      = $options['password'];
        $user_id       = $options['user_id'] ?: 1;
        $user_login    = $options['user_login'] ?: 'admin';

        $output->writeln("\n\n<info>Change password for user with ID = {$user_id} and login {$user_login}</info>");

        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository('MauticUserBundle:User');
        $adminUser = $repo->find($user_id);

        if (is_null($adminUser)) {
            $output->writeln("\n\n<info>Unable to find user by ID. Try to find by login ({$user_login})...</info>");
            $adminUser =  $repo->findOneBy(array('username' => $user_login));
            if (is_null($adminUser)) {
                $output->writeln("\n\n<error>Unable to find user</error>");
                return false;
            }
            $output->writeln("\n\n<info>User founded by login ({$user_login})</info>");
        }

        $encoder = $this->getContainer()->get('security.password_encoder');
        $adminUser->setPassword($encoder->encodePassword($adminUser, $password));

        $em->persist($adminUser);
        $em->flush();

        $output->writeln("\n\n<info>User with ID = {$adminUser->getId()} and/or login = {$adminUser->getUsername()} updated</info>");
        return true;
    }
}
