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

use Exception;

/**
 * Initializes a Magallanes Configuration into a Proyect
 *
 * @author Oscar Reales <oreales@gmail.com>
 */
class HelpCommand extends AbstractCommand
{
    /**
     * echo commands.txt (from docs)
     */
    public function run()
    {
        $output = '';
        exec('cat ' . __DIR__ . '/../../../docs/commands.txt', $output);
        echo implode(PHP_EOL, $output);
    }

}