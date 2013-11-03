<?php
class Mage_Command_BuiltIn_Gitdeploy
    extends Mage_Command_BuiltIn_Deploy
    implements Mage_Command_RequiresEnvironment
{

    /**
     * Deploying using git rebase strategy instead of rsync
     *
     * The next flow will be executed in host:
     * git fetch remote (being remote defined in environment config)
     * git checkout branch (being branch defined in environment config)
     * git stash if git working copy in host is "dirty"
     * git rebase remote/branch
     * git stash pop if git working copy in host was "dirty"
     *
     * @see Mage_Task_BuiltIn_Deployment_Git
     */
    public function run()
    {
        $failedTasks = 0;

        $this->_startTime = time();

        $lockFile = '.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        if (file_exists($lockFile)) {
            Mage_Console::output('<red>This environment is locked!</red>', 1, 2);
            return;
        }

        // Run Pre-Deployment Tasks
        $this->_runNonDeploymentTasks('pre-deploy', $this->getConfig(), 'Pre-Deployment');

        // Run Tasks for Deployment
        $hosts = $this->getConfig()->getHosts();
        $this->_hostsCount = count($hosts);

        if ($this->_hostsCount == 0) {
            Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No hosts defined, skipping deployment tasks.</dark_gray>', 1, 3);

        } else {
            $this->_startTimeHosts = time();
            foreach ($hosts as $_hostKey => $host) {

            	// Check if Host has specific configuration
            	$hostConfig = null;
            	if (is_array($host)) {
            		$hostConfig = $host;
                    $host = $_hostKey;
            	}

            	// Set Host and Host Specific Config
                $this->getConfig()->setHost($host);
                $this->getConfig()->setHostConfig($hostConfig);

                // Prepare Tasks
                $tasks = 0;
                $completedTasks = 0;

                Mage_Console::output('Deploying to <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray>');

                $tasksToRun = $this->getConfig()->getTasks();
                //@oreales: cambiamos el deploy por defecto a deployment/git
                array_unshift($tasksToRun, 'deployment/git');

                if (count($tasksToRun) == 0) {
                    Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No </dark_gray><light_cyan>Deployment</light_cyan> <dark_gray>tasks defined.</dark_gray>', 2);
                    Mage_Console::output('Deployment to <dark_gray>' . $host . '</dark_gray> skipped!', 1, 3);

                } else {
                    foreach ($tasksToRun as $taskData) {
                        $tasks++;
                        $task = Mage_Task_Factory::get($taskData, $this->getConfig(), false, 'deploy');

                        if ($this->_runTask($task)) {
                            $completedTasks++;
                        } else {
                            $failedTasks++;
                        }
                    }

                    if ($completedTasks == $tasks) {
                        $tasksColor = 'green';
                    } else {
                        $tasksColor = 'red';
                    }

                    Mage_Console::output('Deployment to <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray> completed: <' . $tasksColor . '>' . $completedTasks . '/' . $tasks . '</' . $tasksColor . '> tasks done.', 1, 3);
                }

                // Reset Host Config
                $this->getConfig()->setHostConfig(null);
            }
            $this->_endTimeHosts = time();

            if ($failedTasks > 0) {
            	self::$_deployStatus = self::FAILED;
                Mage_Console::output('A total of <dark_gray>' . $failedTasks . '</dark_gray> deployment tasks failed: <red>ABORTING</red>', 1, 2);
            } else {
            	self::$_deployStatus = self::SUCCEDED;
            }
        }

    	// Run Post-Deployment Tasks
    	$this->_runNonDeploymentTasks('post-deploy', $this->getConfig(), 'Post-Deployment');

        // Time Information Hosts
        if ($this->_hostsCount > 0) {
            $timeTextHost = $this->_transcurredTime($this->_endTimeHosts - $this->_startTimeHosts);
            Mage_Console::output('Time for deployment: <dark_gray>' . $timeTextHost . '</dark_gray>.');

            $timeTextPerHost = $this->_transcurredTime(round(($this->_endTimeHosts - $this->_startTimeHosts) / $this->_hostsCount));
            Mage_Console::output('Average time per host: <dark_gray>' . $timeTextPerHost . '</dark_gray>.');
        }

        // Time Information General
        $timeText = $this->_transcurredTime(time() - $this->_startTime);
        Mage_Console::output('Total time: <dark_gray>' . $timeText . '</dark_gray>.', 1, 2);

        // Send Notifications
        $this->_sendNotification();
    }
}
