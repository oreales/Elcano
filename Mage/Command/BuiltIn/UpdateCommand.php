<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Command\BuiltIn;

use Mage\Command\AbstractCommand;
use Mage\Task\Factory;
use Mage\Console;

use Exception;

/**
 * Updates the SCM Base Code
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class UpdateCommand extends AbstractCommand
{
	/**
	 * Updates the SCM Base Code
	 * @see \Mage\Command\AbstractCommand::run()
	 */
    public function run()
    {
        $task = Factory::get('scm/update', $this->getConfig());
        $task->init();

        Console::output('Updating application via ' . $task->getName() . ' ... ', 1, 0);
        $result = $task->run();

        if ($result == true) {
            Console::output('<green>OK</green>' . PHP_EOL, 0);
        } else {
            Console::output('<red>FAIL</red>' . PHP_EOL, 0);
        }
    }

}