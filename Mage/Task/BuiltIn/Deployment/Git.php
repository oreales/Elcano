<?php
class Mage_Task_BuiltIn_Deployment_Git
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    public function getName()
    {
        return 'Git deploy with rebase [built-in]';
    }

    /**
     * Deployamos con git siguiendo este flujo:
     *
     * git stash (si el repo no esta limpio en remote)
     * git fetch origin --tags (origin definido en configuracion)
     * git rebase origin/master (origin y branch definidos en configuracion)
     * git stash pop (si se hizo stash en el primer paso)
     *
     * @return bool
     */
    public function run()
    {
        //@oreales
        //cogemos configuracion scm del deployment o en su defecto de general
    	$deploymentGitData = $this->getConfig()->deployment('deployment_git', false);
        if (is_array($deploymentGitData) && isset($deploymentGitData['remote']) && isset($deploymentGitData['branch'])) {
            $branch = $this->getParameter('branch', $deploymentGitData['branch']);
            $remote = $this->getParameter('remote', $deploymentGitData['remote']);

            //nos aseguramos de estar en el branch adecuado en production
            $checkoutCommand = $this->_runRemoteCommand("git checkout $branch");
            if(!$checkoutCommand)
            {
                Mage_Console::output('');
                Mage_Console::output("<red>no se ha podido hacer checkout a $branch... </red>",2,0);
                return false;
            }

            //oreales comprobamos el status del repo remote
            $status = '';
            $statusCommand = $this->_runRemoteCommand('git status --porcelain', $status);
            $clean = (empty($status)) ? true : false;
            $stashed = false;
            if(!$clean)
            {
                //hacemos un stash
                $this->_runRemoteCommand("git stash", $output);
                if($output != "No local changes to save"){
                    Mage_Console::output("stashing local modifications... ",0,0);
                    $stashed = true;
                }
            }

            //podemos hacer el rebase
            $commandFetch = $this->_runRemoteCommand("git fetch $remote", $output);
            $commandRebase = $this->_runRemoteCommand("git rebase $remote/$branch", $output);
            $result = $commandFetch && $commandRebase;
            Mage_Console::output("fetching & rebasing $remote/$branch... ",0,0);

            if($stashed)
            {
                //devolvemos el stash a master, si hemos hecho stash previamente
                $this->_runRemoteCommand("git stash pop", $output);
                Mage_Console::output("stash pop... ",0,0);
            }

            return $result;
        }

        Mage_Console::output('');
        Mage_Console::output("<red>defina remote y branch para este environment en la configuracion bajo deployment_git... </red>",2,0);
    	return false;
    	//throw new Exception("\nHa habido un error haciendo rebase en remote.Compruebe que tiene un branch/origin en scm configuracion o pasan como parametros.");
    }
}