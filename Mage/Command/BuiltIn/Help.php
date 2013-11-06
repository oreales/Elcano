<?php
class Mage_Command_BuiltIn_Help
    extends Mage_Command_CommandAbstract
{
    /**
     * Saca por pantalla la lista de commandos (commands.txt) que hay en docs
     */
    public function run()
    {
        $output = '';
        exec('cat ' . __DIR__ . '/../../../docs/commands.txt', $output);
        echo implode(PHP_EOL, $output);
    }

}