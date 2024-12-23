<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\ConfigParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigParserTest extends TestCase
{
    #[Test]
    public function it_is_able_to_get_a_specified_config_setting(): void
    {
        # given that we have settings and a ConfigParser object
        $settings = [
            'app_name' => 'test',
            'doctrine' => [
                'connection' => [
                    'host' => 'localhost',
                    'driver' => 'pdo_mysql',
                    'dbname' => 'test',
                    'username' => 'root',
                    'password' => ''
                ]
            ]
        ];

        $configParser = new ConfigParser($settings);

        # when we call a get method to get a setting
        $actualSetting1 = $configParser->get('app_name');
        $actualSetting2 = $configParser->get('doctrine.connection');

        $expectedSetting1 = $settings['app_name'];
        $expectedSetting2 = $settings['doctrine']['connection'];

        # then we assert ConfigParser object was giving us a correct setting
        $this->assertEquals($expectedSetting1, $actualSetting1);
        $this->assertEquals($expectedSetting2, $actualSetting2);
    }

    #[Test]
    public function it_gets_the_default_value_when_setting_is_not_found(): void
    {
        $configParser = new ConfigParser(['app_name' => 'Test']);

        $this->assertNull($configParser->get('app_version'));
    }
}