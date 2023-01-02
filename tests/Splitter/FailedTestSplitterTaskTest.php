<?php

declare(strict_types=1);

namespace Tests\Codeception\Task\Splitter;

use Codeception\Task\Splitter\FailedTestSplitterTask;
use Consolidation\Log\Logger;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Finder\Finder;
use const Tests\Codeception\Task\TEST_PATH;

final class FailedTestSplitterTaskTest extends TestCase
{
    public function testRunWillFailIfReportFileDoesNotExists(): void
    {
        $task = new FailedTestSplitterTask(5);
        $task->setLogger(new Logger(new NullOutput()));
        $task->setReportPath('tests/_output/failedTests.txt')
            ->groupsTo(TEST_PATH . '/result/group_');

        $this->expectException(RuntimeException::class);
        $task->run();
    }

    /**
     * @covers ::run
     */
    public function testRun(): void
    {
        $expected = 4;
        $task = new FailedTestSplitterTask($expected);
        $task->setLogger(new Logger(new NullOutput()));

        $groupTo = TEST_PATH . '/result/group_';
        $task
            ->setReportPath(TEST_PATH . '/fixtures/failedTests.txt')
            ->groupsTo($groupTo)
            ->run();

        $countedFiles = 0;
        for ($i = 1; $i <= $expected; ++$i) {
            $this->assertFileExists($groupTo . $i);
            $countedFiles += count(
                explode(
                    PHP_EOL,
                    file_get_contents($groupTo . $i)
                )
            );
        }

        $this->assertSame(
            8,
            $countedFiles,
            "Expected 8 tests but got {$countedFiles} tests."
        );
        $this->assertFileDoesNotExist($groupTo . ($expected + 1));
    }

    /**
     * @covers ::setReportPath
     */
    public function testSetReportPathWillThrowExceptionWithEmptyPath(): void
    {
        $task = new FailedTestSplitterTask(5);
        $task->setLogger(new Logger(new NullOutput()));
        $this->expectException(InvalidArgumentException::class);
        $task->setReportPath('');
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        $finder = Finder::create()
            ->files()
            ->name('group_*');

        foreach ($finder->in(TEST_PATH . '/result') as $file) {
            unlink($file->getPathname());
        }
    }
}
