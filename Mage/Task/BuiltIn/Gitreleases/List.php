<?php
class Mage_Task_BuiltIn_Gitreleases_List
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    public function getName()
    {
        return 'Git Listing releases [built-in]';
    }

    public function run()
    {
        Mage_Console::output('Git Releases available on <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray>',2);

        // Get Releases
        $tags = '';
        $tagCommands = $this->_runRemoteCommand('git tag ', $tags);
        $tags = ($tags == '') ? array() : explode(PHP_EOL, $tags);
        //@todo ordenar tags de manera que la última sea la primera, pero teniendo en cuenta nuestro esquema de tags.
        foreach(array_reverse($tags) as $tag)
        {
            $output = '';
                $showCommand = $this->_runRemoteCommand("git show --decorate --oneline -s $tag^{commit}", $output);

            //marcamos con amarillo el HEAD o current
            if(strpos($output,'HEAD') !== false)
            {
                $output = '<yellow>'.$output.'</yellow>';
            }

            Mage_Console::output($output,3);
        }

        Mage_Console::output('');
    }
}