<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineORMModule\Listener;

use Doctrine\DBAL\Migrations\Tools\Console\Command\AbstractCommand as MigrationCommand;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @license MIT
 * @link    www.doctrine-project.org
 * @author  Thomas Dutrion <thomas@engineor.com>
 */
final class MigrationConfigurationListener
{
    private $container;

    public function __construct(ServiceLocatorInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ConsoleCommandEvent $consoleCommandEvent
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ConsoleCommandEvent $consoleCommandEvent)
    {
        if (!class_exists(MigrationCommand::class)) {
            return;
        }
        if (!$consoleCommandEvent->getCommand() instanceof MigrationCommand) {
            return;
        }
        /* @var $command MigrationCommand */
        $command = $consoleCommandEvent->getCommand();
        $input = $consoleCommandEvent->getInput();
        $objectManagerName = 'doctrine.entitymanager.orm_default';
        if ($input->hasParameterOption(['--object-manager'])) {
            $objectManagerName = $input->getParameterOption(['--object-manager']);
        }
        $migrationConfiguration = $this->container->get(
            str_replace('entitymanager', 'migrations_configuration', $objectManagerName)
        );
        $command->setMigrationConfiguration($migrationConfiguration);
    }
}
