<?php
class Mage_Task_BuiltIn_Gitreleases_Rollback
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    private $_release = null;

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
        Mage_Console::output('Rollback release on <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray>');

        //nos aseguramos de tener una release a la que cambiar
        if(null === $this->getRelease()){
            Mage_Console::output('<red>A release must be specified as parameter (tag or commit SHA1)</red>');
            return false;
        }

        //hacemos checkout a la release indicada (tag o commit)
        $checkoutCommand = $this->_runRemoteCommand("git checkout " . $this->getRelease());
        if(!$checkoutCommand)
        {
            Mage_Console::output("<red>It wasn´t possible rollback to " . $this->getRelease() . "</red>",2);
            return false;
        }

        Mage_Console::output("<green>After rollback, current version is " . $this->getRelease() . "</green>",2);
        Mage_Console::output('');
        return true;
    }

}