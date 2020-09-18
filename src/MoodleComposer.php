<?php

namespace MoodleComposer;

use Composer\Script\Event;
use Composer\Util\Filesystem;

class MoodleComposer
{
    public static function postInstall(Event $event)
    {
        $appDir = getcwd();
        $extra = $event->getComposer()->getPackage()->getExtra();
        $installerdir = $extra['installerdir'];
        $filesystem = new Filesystem();
        $filesystem->copy($appDir . "/vendor/moodle/moodle", $appDir . DIRECTORY_SEPARATOR . $installerdir);
    }
}
