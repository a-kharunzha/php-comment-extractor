<?php

declare(strict_types=1);

namespace Tests\App\Console\Command;

use App\Console\Application;
use App\Console\Command\ExtractCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ExtractCommandTest extends TestCase
{
    public function testRunCommand(): void
    {
        $application = new Application();

        $application->add(new ExtractCommand());
        //
        $command = $application->find('extract');
        $commandTester = new CommandTester($command);
        // $commandTester->execute([
        //     'command' => $command->getName(),
        // ]);
        //
        // $output = $commandTester->getDisplay();
        // $this->assertContains('Hello!', $output);
    }
}
