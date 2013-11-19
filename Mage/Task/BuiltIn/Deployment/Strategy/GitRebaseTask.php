<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Console;

use Exception;

/**
 * Task for using Git Working Copy as the Deployed Code
 *
 * @author Oscar Reales <oreales@gmail.com>
 */
class GitRebaseTask extends AbstractTask implements IsReleaseAware
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Deploy via Git Rebase [built-in]';
    }

    /**
     *
     * Deploying using git rebase strategy instead of rsync
     *
     * The next flow will be executed in host:
     * git fetch remote (being remote defined in environment config)
     * git checkout branch (being branch defined in environment config)
     * git stash if git working copy in host is "dirty"
     * git rebase remote/branch
     * git stash pop if git working copy in host was "dirty"
     *
     * @return bool
     */
    public function run()
    {
        //config from params, and if not params from environment config: git-rebase-defaults
        $deploymentGitData = $this->getConfig()->environmentConfig('git-rebase-defaults', array());
        $remote = $this->getParameter('remote', (array_key_exists('remote',$deploymentGitData)) ? $deploymentGitData['remote'] : 'origin');
        $branch = $this->getParameter('branch', (array_key_exists('branch',$deploymentGitData)) ? $deploymentGitData['branch'] : 'master');
        
        $result = true;

        //fetching
        $result = $this->runCommandRemote("git fetch $remote", $output) && $result;
        if(!$result)
        {
            Console::output("<red>fails fetch $remote ... </red>",0,0);
            return false;
        }

        //ensuring right branch is checked out
        $result = $this->runCommandRemote("git checkout $branch") && $result;
        if(!$result)
        {
            Console::output("<red>fails $branch checkout ... </red>",0,0);
            return false;
        }

        //testing for local modifications just to be sure
        $status = '';
        $result = $this->runCommandRemote('git status --porcelain', $status) && $result;
        $clean = (empty($status)) ? true : false;
        $stashed = false;
        if(!$clean)
        {
            //hacemos un stash
            $result = $this->runCommandRemote("git stash", $output) && $result;
            if($output != "No local changes to save"){
                Console::output("stash ... ",0,0);
                $stashed = true;
            }
        }

        //rebasing
        $result = $this->runCommandRemote("git rebase $remote/$branch", $output) && $result;
        if(!$result)
        {
            Console::output("<red>fails rebase $remote/$branch ... </red>",0,0);
        }

        if($stashed)
        {
            //local modifications before rebase being popped from stash
            $result = $this->runCommandRemote("git stash pop", $output) && $result;
            Console::output("stash pop ... ",0,0);
        }

        return $result;
    }
}