<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Gitreleases;

use Mage\Console;
use Mage\Task\Factory;
use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\Releases\RollbackAware;

use Exception;

/**
 * Task for Performing alternative Rollback Operation using git tags / commits
 *
 * @author Oscar Reales <oreales@gmail.com>
 */
class RollbackTask extends AbstractTask implements IsReleaseAware
{
    protected $_release = null;

    public function getName()
    {
        return 'Git Rollback release [built-in]';
    }

    public function setRelease($releaseId)
    {
        $this->_release = $releaseId;
        return $this;
    }

    public function getRelease()
    {
        return $this->_release;
    }

    public function run()
    {
        Console::output('Rollback release on <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray>');

        //nos aseguramos de tener una release a la que cambiar
        if(null === $this->getRelease()){
            Console::output('<red>A release must be specified as parameter (tag or commit SHA1)</red>');
            return false;
        }

        //hacemos checkout a la release indicada (tag o commit)
        $checkoutCommand = $this->runCommandRemote("git checkout " . $this->getRelease());
        if(!$checkoutCommand)
        {
            Console::output("<red>It wasn´t possible rollback to " . $this->getRelease() . "</red>",2);
            return false;
        }

        Console::output("<green>After rollback, current version is " . $this->getRelease() . "</green>",2);
        Console::output('');
        return true;
    }

}