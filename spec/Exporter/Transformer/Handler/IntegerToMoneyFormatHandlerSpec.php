<?php

declare(strict_types=1);

namespace spec\FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;

use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Handler\IntegerToMoneyFormatHandler;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\HandlerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Exporter\Transformer\Pool;
use PhpSpec\ObjectBehavior;
use Prophecy\Prophet;
use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Webmozart\Assert\Assert;

class IntegerToMoneyFormatHandlerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['test']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IntegerToMoneyFormatHandler::class);
    }

    function it_extends()
    {
        $this->shouldHaveType(Handler::class);
    }

    function it_should_implement()
    {
        $this->shouldImplement(HandlerInterface::class);
    }

    function it_should_process_directly()
    {
        $this->handle('test', 10000)->shouldBeString();
        $this->handle('test', 12345)->shouldBe('123.45');
        $this->handle('test', 987654)->shouldBe('9876.54');
    }

    function it_should_process_via_pool()
    {
        $generator = new RewindableGenerator(function () {
            yield $this->getWrappedObject();
        }, $count = 1);

        $pool = new Pool($generator);

        $result = $pool->handle('test', 12345);

        Assert::same('123.45', $result);
    }
}
