<?php
class Mage_Task_BuiltIn_Magento_Cleancache
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Magento - Clean Cache [built-in]';
    }

    public function run()
    {
        $command = 'rm -rf var/cache/*';
        $result = $this->_runLocalCommand($command);

        return $result;
    }
}