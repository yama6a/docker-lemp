<?php

namespace Tests\Repository;

use App\Repository\LeverageRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\FrameworkTest;

class LeverageRepositoryTest extends FrameworkTest
{
    private LeverageRepository $lRepo;
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = "/tmp/" . rand(0, 999999999);
        mkdir($this->path, 0777, true);
        $this->lRepo = new LeverageRepository($this->path, "/tmp/");
    }

    protected function tearDown(): void
    {
        $this->rrmdir($this->path);
        parent::tearDown();
    }

    // test store
    public function testStore()
    {
        $data = ["a" => 1, "b" => "foo", "c" => true, "d" => ["e" => "bar"]];
        $file1 = $this->makeFile();
        $file2 = $this->makeFile();

        $path = $this->lRepo->store($data, [$file1, $file2]);

        $this->assertFileExists($path . "/data.json");
        $data["_timestamp"] = basename($path);
        $this->assertJsonStringEqualsJsonFile($path . "/data.json", json_encode($data));
        $this->assertFileExists($path . "/0_" . $file1->getClientOriginalName());
        $this->assertFileExists($path . "/1_" . $file2->getClientOriginalName());

    }

    private function rrmdir($dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                        $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            rmdir($dir);
        }
    }

    private function makeFile(): UploadedFile
    {
        $fileName = tempnam($this->path, "srvtest_");
        $fileContent = rand(0, 999999999);
        file_put_contents($fileName, $fileContent);
        return new UploadedFile($fileName, $fileName.".jpg", "image/jpeg", null, true);

    }
}
