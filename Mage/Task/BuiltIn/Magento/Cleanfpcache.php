<?php
class Mage_Task_BuiltIn_Magento_Cleanfpcache
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Magento - Clean Full Page Cache [built-in]';
    }

    public function run()
    {
        $command = 'rm -rf var/full_page_cache/*';
        $result = $this->_runLocalCommand($command);

        return $result;
    }
}