<?php

namespace App\Tests\Unit\Command;

use App\Command\InitConfigCommand;
use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitConfigCommandTest extends TestCase
{
    public function testBehavior(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $ii = $this->createMock(InputInterface::class);
        $oi = $this->createMock(OutputInterface::class);

        $config = (new Configuration())
            ->setBlogDescription('A dummy description')
            ->setBlogTitle('A dummy title')
            ->setCopyrightMessage('A dummy copyright message')
            ->setCopyrightExtraMessage('A dummy copyright extra message')
            ->setGithubUsername('A dummy github username')
            ->setGoogleAnalyticsId('A dummy Google Analytics identifier')
            ->setLinkedinUsername('A dummy Linkedin username')
        ;

        $em->expects(static::once())->method('persist')->with($config);
        $em->expects(static::once())->method('flush');

        $cmd = new InitConfigCommand($em);
        static::assertEquals(0, $cmd->run($ii, $oi));
    }
}
