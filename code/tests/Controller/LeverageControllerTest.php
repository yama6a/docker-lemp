<?php

namespace Tests\Controller;

use App\Http\Router;
use App\Repository\LeverageRepository;
use App\Repository\OptionRepository;
use App\Repository\QuestionRepository;
use Mockery;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Tests\FrameworkTest;

class LeverageControllerTest extends FrameworkTest
{
    /** @var LeverageRepository|Mockery\Mock */
    private LeverageRepository $repo;
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(LeverageRepository::class);
        $this->router = new Router(Mockery::mock(QuestionRepository::class), Mockery::mock(OptionRepository::class), $this->repo);
    }

    public function testStore(): void
    {
        $this->repo->shouldReceive('store')->once();
        $request = Request::create('api.php?path=/leverage', 'POST', ["data" => json_encode(["foo" => "bar"])]);
        $request->files->set("files", [static::makeFile(), static::makeFile()]);

        $response = $this->router->handle($request);
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        $this->assertJsonStringEqualsJsonString('"uploaded"', json_encode(json_decode($response->getContent())->payload));
    }

    static function makeFile(): UploadedFile
    {
        return new UploadedFile(tempnam("/temp", "foo_"), tempnam("/temp", "foo_").".jpg", "image/jpeg", null, true);
    }

}
