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
use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

use Exception;

/**
 * Task for Listing Available Releases on an Environment
 *
 * @author Oscar Reales <oreales@gmail.com>
 */
class ListTask extends AbstractTask implements IsReleaseAware
{
    public function getName()
    {
        return 'Git Listing releases [built-in]';
    }

    public function run()
    {
        Console::output('Git Releases available on <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray>',2);

        // Get Releases
        $tags = '';
        $tagCommands = $this->runCommandRemote('git tag ', $tags);
        $tags = ($tags == '') ? array() : explode(PHP_EOL, $tags);

        $current = '';
        $_currentAlreadyFounded = false;
        $currentCommand = $this->runCommandRemote('git show -s --format=%h ', $current);

        $maxReleases = $this->getConfig()->release('max', 10);
        $maxReleasesCountdown = $maxReleases;

        //@todo ordenar tags de manera que la última sea la primera, pero teniendo en cuenta nuestro esquema de tags.
        foreach(array_reverse($tags) as $tag)
        {
            $output = '';
            $showCommand = $this->runCommandRemote("git show --decorate --oneline -s $tag^{commit}", $output);

            //marcamos con amarillo el HEAD o current
            if(!$_currentAlreadyFounded && strpos($output,$current) === 0)
            {
                $output = '<yellow>'.$output.'</yellow>';
                $_currentAlreadyFounded = true;
            }

            Console::output($output,3);

            $maxReleasesCountdown--;
            if($maxReleasesCountdown == 0){
                $moreReleases = count($tags) - $maxReleases;
                Console::output(sprintf("... and %d releases more (showing last %d)", $moreReleases,$maxReleases),3);
                break;
            }
        }

        Console::output('');
    }
}