<?php
namespace MonitoCli\Controller;

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Command;
use GetOpt\ArgumentException;

class Create
{
    public function run()
    {
        define('NAME', 'AwesomeApp');
        define('VERSION', '1.0-alpha');

        $getOpt = new GetOpt();

        // define common options
        $getOpt->addOptions([
           
            Option::create(null, 'version', GetOpt::NO_ARGUMENT)
                ->setDescription('Show version information and quit'),
                
            // Option::create('?', 'help', GetOpt::NO_ARGUMENT)
            //     ->setDescription('Show this help and quit'),
            
        ]);

        // add simple commands
        $getOpt->addCommand(Command::create('create', function () { 
            echo 'When you see this message the setup works.' . PHP_EOL;
        })->setDescription('Create objects'));

        // add commands
        $getOpt->addCommand(new \MonitoCli\Command\Create());
        // $getOpt->addCommand(new MoveCommand());
        // $getOpt->addCommand(new DeleteCommand());

        // process arguments and catch user errors
        try {
            $getOpt->process();
        } catch (ArgumentException $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
            echo PHP_EOL . $getOpt->getHelpText();
            exit;
        } catch (\Exception $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
            echo PHP_EOL . $getOpt->getHelpText();
            exit;
        }

        // show version and quit
        if ($getOpt->getOption('version')) {
            echo sprintf('%s: %s' . PHP_EOL, NAME, VERSION);
            exit;
        }

        // show help and quit
        $command = $getOpt->getCommand();
        if (!$command || $getOpt->getOption('help')) {
            echo $getOpt->getHelpText();
            exit;
        }
        try {
            // call the requested command
            call_user_func($command->handler(), $getOpt);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}