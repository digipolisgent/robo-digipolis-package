<?php

namespace DigipolisGent\Tests\Robo\Task\Package;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Common\CommandArguments;
use Robo\Robo;
use Robo\TaskAccessor;
use Symfony\Component\Console\Output\NullOutput;

class ThemeCompileTest extends \PHPUnit_Framework_TestCase implements ContainerAwareInterface, ConfigAwareInterface
{

    use \DigipolisGent\Robo\Task\Package\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;
    use CommandArguments;
    use \Robo\Task\Base\loadTasks;
    use \Robo\Common\ConfigAwareTrait;

    /**
     * Set up the Robo container so that we can create tasks in our tests.
     */
    public function setUp()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);
        $this->setConfig(Robo::config());
    }

    /**
     * Scaffold the collection builder.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   The collection builder.
     */
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks();

        return $this->getContainer()
            ->get('collectionBuilder', [$emptyRobofile]);
    }

    public function testRun()
    {
        $themePath = realpath(__DIR__ . '/../testfiles/testtheme');
        $result = $this->taskThemeCompile($themePath, 'build')
          ->run();

        // Assert response.
        $this->assertEquals(0, $result->getExitCode());
        $this->assertEquals('', $result->getMessage());

        // Assert bower install ran.
        $this->assertFileExists($themePath . '/vendor/bundle');

        // Assert npm install ran.
        $this->assertFileExists($themePath . '/node_modules');

        // Assert bower install ran.
        $this->assertFileExists($themePath . '/libraries');

        // Assert grunt build ran.
        $this->assertFileExists($themePath . '/hello_grunt.txt');

        // Assert gulp build ran.
        $this->assertFileExists($themePath . '/hello_gulp.txt');

        // Manual cleanup.
        $files = [
          '/.bundle',
          '/hello_gulp.txt',
          '/hello_grunt.txt',
          '/libraries',
          '/node_modules',
          '/vendor',
        ];
        foreach ($files as $remove) {
          exec('rm -rf ' . $themePath . $remove);
        }
    }
}