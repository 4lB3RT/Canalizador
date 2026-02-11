<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\Console;

use Canalizador\Shared\Infrastructure\Events\EventHandlerRegistry;
use Illuminate\Console\Command;
use ReflectionClass;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;

final class SetupRabbitMQCommand extends Command
{
    protected $signature = 'rabbitmq:setup';
    protected $description = 'Declare all domain event queues and bind them to the exchange';

    public function handle(EventHandlerRegistry $registry, RabbitMQConnector $connector): void
    {
        $config = $this->laravel['config']->get('queue.connections.rabbitmq');
        $exchange = $config['options']['queue']['exchange'] ?? '';

        if (empty($exchange)) {
            $this->error('No exchange configured in queue.connections.rabbitmq.options.queue.exchange');
            return;
        }

        $queue = $connector->connect($config);
        $queueNames = $this->resolveQueueNames($registry);

        if (empty($queueNames)) {
            $this->warn('No domain events registered.');
            return;
        }

        foreach ($queueNames as $queueName) {
            if (!$queue->isQueueExists($queueName)) {
                $queue->declareQueue($queueName, true, false);
                $this->info("Queue declared: {$queueName}");
            } else {
                $this->line("Queue exists: {$queueName}");
            }

            $queue->bindQueue($queueName, $exchange, $queueName);
            $this->info("  Bound to exchange '{$exchange}' with routing key '{$queueName}'");
        }

        $this->newLine();
        $this->info('RabbitMQ setup completed.');
    }

    /**
     * @return list<string>
     */
    private function resolveQueueNames(EventHandlerRegistry $registry): array
    {
        $queueNames = [];

        foreach ($registry->registeredEventClasses() as $eventClass) {
            $reflection = new ReflectionClass($eventClass);
            $instance = $reflection->newInstanceWithoutConstructor();
            $queueNames[] = $instance->eventName();
        }

        return array_unique($queueNames);
    }
}
