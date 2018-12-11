<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 24.08.2018
 * Time: 18:42
 */

namespace console\modules\daemons\components;

/**
 * watcher-daemon - check another daemons and run it if need
 */
abstract class WatcherDaemonController extends DaemonController
{
    /**
     * Daemons for check
     * [
     *  ['className' => 'OneDaemonController', 'enabled' => true]
     *  ...
     *  ['className' => 'AnotherDaemonController', 'enabled' => false]
     * ]
     *
     * @var $daemonsList array
     */
    public $daemonsList = [];

    public $daemonFolder = '';

    public function init()
    {
        $pidFile = \Yii::getAlias($this->pidDir) . DIRECTORY_SEPARATOR . $this->shortClassName();
        if (file_exists($pidFile)) {
            $pid = file_get_contents($pidFile);
            exec("ps -p $pid", $output);
            if (count($output) > 1) {
                $this->halt(self::EXIT_CODE_ERROR, 'Another Watcher is already running.');
            }
        }
        parent::init();
    }

    /**
     * Job processing body
     *
     * @param $job array
     *
     * @return boolean
     */
    protected function doJob($job)
    {
        $pidfile = \Yii::getAlias($this->pidDir) . DIRECTORY_SEPARATOR . $job['className'] . $job['consumerClass'] . $job['orgId'] . $job['storeId'];

        \Yii::trace('Check daemon ' . $job['consumerClass']);
        if (file_exists($pidfile)) {
            $pid = file_get_contents($pidfile);
            if (!empty($pid)) {
                if ($this->isProcessRunning($pid)) {
                    if ($job['enabled']) {
                        \Yii::trace('Daemon ' . $job['className'] . ' running and working fine');

                        return true;
                    } else {
                        \Yii::warning('Daemon ' . $job['className'] . ' running, but disabled in config. Send SIGTERM signal.');
                        if (isset($job['hardKill']) && $job['hardKill']) {
                            posix_kill($pid, SIGKILL);
                        } else {
                            posix_kill($pid, SIGTERM);
                        }

                        return true;
                    }
                }
            }
            else{
                $job['enabled'] = true;
            }
        }
        \Yii::trace('Daemon pid not found.');
        if ($job['enabled']) {
            \Yii::trace('Try to run daemon ' . $job['consumerClass'] . '.');
            $command_name = $this->getCommandNameBy($job['className']);
            //flush log before fork
            \Yii::$app->getLog()->getLogger()->flush(true);
            //run daemon
            $pid = pcntl_fork();
            if ($pid == -1) {
                $this->halt(self::EXIT_CODE_ERROR, 'pcntl_fork() returned error');
            } elseif (!$pid) {
                $this->initLogger();
                \Yii::trace('Daemon ' . $job['consumerClass'] . ' is running.');
            } else {
                $this->halt(
                    (0 === \Yii::$app->runAction("$command_name", [
                        'demonize'      => $job['demonize'] ?? 1,
                        'orgId'         => $job['orgId'] ?? null,
                        'consumerClass' => $job['consumerClass'] ?? null,
                        'storeId'       => $job['storeId'] ?? null,
                        'lastExec'      => $job['lastExec'] ?? null,
                    ])
                        ? self::EXIT_CODE_NORMAL
                        : self::EXIT_CODE_ERROR
                    )
                );
            }

        }
        \Yii::trace('Daemon ' . $job['consumerClass'] . ' is checked.');

        return true;
    }

    /**
     * Return array of daemons
     *
     * @return array
     */
    protected function defineJobs()
    {
        sleep($this->sleep);

        return $this->daemonsList;
    }

    protected function getCommandNameBy($className)
    {
        $command = strtolower(
            preg_replace_callback('/(?<!^)(?<![A-Z])[A-Z]{1}/',
                function ($matches) {
                    return '-' . $matches[0];
                },
                str_replace('Controller', '', $className)
            )
        );

        if (!empty($this->daemonFolder)) {
            $command = $this->daemonFolder . DIRECTORY_SEPARATOR . $command;
        }

        return $command . DIRECTORY_SEPARATOR . 'index';
    }

    /**
     * @param $pid
     *
     * @return bool
     */
    public function isProcessRunning($pid)
    {
        try {
            return !!posix_getpgid($pid);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
