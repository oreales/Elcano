<?php
class Mage_Command_BuiltIn_Gitreleases
    extends Mage_Command_CommandAbstract
    implements Mage_Command_RequiresEnvironment
{
    private $_release = null;

    public function run()
    {
        $subcommand = $this->getConfig()->getArgument(1);
        $lockFile = '.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        if (file_exists($lockFile) && ($subcommand == 'rollback')) {
            Mage_Console::output('<red>This environment is locked!</red>', 0, 2);
            return;
        }

        // Run Tasks for Deployment
        $hosts = $this->getConfig()->getHosts();

        if (count($hosts) == 0) {
            Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No hosts defined, unable to get releases.</dark_gray>', 1, 3);

        } else {
            foreach ($hosts as $host) {
                $this->getConfig()->setHost($host);

                //@todo añadir un show para mostrar extendido la informacion de una release.
                switch ($subcommand) {
                    case 'list':
                        $task = Mage_Task_Factory::get('gitreleases/list', $this->getConfig());
                        $task->init();
                        $result = $task->run();
                        break;

                    case 'rollback':
                        $releaseId = $this->getConfig()->getParameter('release', '');
                        $task = Mage_Task_Factory::get('gitreleases/rollback', $this->getConfig());
                        $task->init();
                        $task->setRelease($releaseId);
                        $result = $task->run();
                        break;
                }
            }
        }
    }
}