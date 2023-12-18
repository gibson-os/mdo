<?php
declare(strict_types=1);

namespace MDO\Test\Functional;

use Codeception\Test\Unit;
use MDO\Client;
use MDO\Loader\FieldLoader;
use MDO\Manager\TableManager;
use MDO\Service\ReplaceService;

class AbstractFunctionalTest extends Unit
{
    protected Client $client;

    protected FieldLoader $fieldLoader;

    protected TableManager $tableManager;

    protected ReplaceService $replaceService;

    protected function setUp(): void
    {
        $this->client = new Client('localhost', 'root', 'ldspFfsd8D0fds');
        $this->fieldLoader = new FieldLoader($this->client);
        $this->tableManager = new TableManager($this->fieldLoader);

        $this->client->execute('DROP DATABASE IF EXISTS `mdo`');
        $this->client->execute('CREATE DATABASE `mdo`');
        $this->client->useDatabase('mdo');

        $this->client->execute(
            'create table `arthur`' .
            '(' .
                '`id` bigint(20) unsigned auto_increment primary key, ' .
                '`name` varchar(32) not null, ' .
                '`description` blob null, ' .
                '`ford_id` bigint(20) unsigned not null, ' .
                '`method` enum ("GET", "POST", "DELETE", "HEAD", "PUT", "CONNECT", "OPTIONS", "TRACE", "PATCH") not null, ' .
                'constraint `uniqueNameMethodTask_idModule_id` unique (`name`, `method`)' .
            ') charset = utf8',
        );
        $this->client->execute(
            'create table `ford`' .
            '(' .
                '`id` bigint(20) unsigned auto_increment primary key, ' .
                '`name` varchar(32) not null, ' .
                'constraint `uniqueNameModule_id` unique (`name`)' .
            ') charset = utf8',
        );

        $this->replaceService = new ReplaceService($this->client);
    }

    protected function tearDown(): void
    {
        $this->client->close();
    }
}
