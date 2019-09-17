<?php

namespace DigipolisGent\Tests\Robo\Task\Package;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use PHPUnit\Framework\Constraint\IsTrue;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\InvalidArgumentHelper;
use Robo\Common\CommandArguments;
use Robo\Contract\ConfigAwareInterface;
use Robo\Robo;
use Robo\TaskAccessor;
use Symfony\Component\Console\Output\NullOutput;

class ThemeCleanTest extends TestCase implements ContainerAwareInterface, ConfigAwareInterface
{

    use \DigipolisGent\Robo\Task\Package\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;
    use CommandArguments;
    use \Robo\Task\Base\loadTasks;
    use \Robo\Common\ConfigAwareTrait;
    use \DigipolisGent\Robo\Task\Package\Utility\NpmFindExecutable;

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
        $compileResult = $this->taskThemeCompile($themePath, 'build')->run();
        $this->assertEquals('', $compileResult->getMessage());
        $this->assertEquals(0, $compileResult->getExitCode());
        $result = $this->taskThemeClean($themePath)
          ->run();

        // Assert response.
        $this->assertEquals('', $result->getMessage());
        $this->assertEquals(0, $result->getExitCode());

        // Assert cleanup of bundler files.
        $bundlerFiles = [
            '/vendor/bundle',
            '/.bundle',
        ];
        foreach ($bundlerFiles as $bundlerFile) {
          $this->assertFileNotExists($themePath . $bundlerFile);
        }

        // Assert cleanup of npm files.
        $this->assertFileNotExists($themePath . '/node_modules');

        // Assert cleanup of bower files.
        $this->assertDirectoryIsEmpty(getenv('HOME') . '/.cache/bower/packages');

        // Assert cleanup of Grunt/Gulp files.
        $this->assertFileNotExists($themePath . '/.sass-cache');

    }

    /**
     * Asserts that a directory is empty.
     *
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryIsEmpty($directory, $message = '')
    {
        if (!is_string($directory)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new IsTrue();
        if (!is_readable($directory)) {
            static::assertThat(false, $constraint, $message);
            return;
        }
        $handle = opendir($directory);
        while (false !== ($entry = readdir($handle))) {
          if ($entry != "." && $entry != "..") {
            static::assertThat(false, $constraint, $message);
            return;
          }
        }
        static::assertThat(true, $constraint, $message);
    }
}
