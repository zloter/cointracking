<?php

namespace Zloter\Cointracking\Services;

class FileManager
{

    const VALID_EXTENSIONS = ['csv'];

    public function save($stream, string $content)
    {
        fwrite($stream, $content);
    }

    /**
     * @return false|resource
     * @throws \Exception
     */
    public function newTmp()
    {
        return tmpfile() ?? throw new \Exception("CannotOpenTmpFile");
    }

    /**
     * @param $file
     * @return void
     */
    public function printToStdOutput($file): void
    {
        fseek($file, 0);
        while($line = fgets($file)) {
            if ($line === "}{\n") {
                echo "  },  \n  {\n";
            } elseif (str_contains($line, '[') || str_contains($line, ']')) {
                echo $line;
            } else {
                echo "  $line";
            }
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getValidFileName(): string
    {
        $fileName = $_SERVER['argv'][1] ?? null;
        if (! $fileName) {
            throw new \Exception('NoInputFile');
        }
        $file_extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (! in_array($file_extension, self::VALID_EXTENSIONS)) {
            throw new \Exception('InvalidFileType');
        }
        if (! is_readable($fileName)) {
            throw new \Exception('FileNotExistsOrUnreadable');
        }
        return $fileName;
    }

    public function close($tmp)
    {
        fclose($tmp);
    }
}